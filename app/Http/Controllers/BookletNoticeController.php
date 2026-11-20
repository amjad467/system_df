<?php

namespace App\Http\Controllers;

use App\Models\BookletNotice;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookletNoticeController extends Controller
{
    /**
     * Check authorization for expired booklet notices.
     */
    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if (!$user->isAdmin() && $user->role !== 'archiver' && $user->role !== 'booklet' && !$user->hasPermission('reports.expired_booklets')) {
            abort(403, 'دەسەڵاتی بینینی ئەم بەشە تەنها بۆ ئەدمین، ئەرشیف و بەشی دەفتەرە!');
        }
    }

    /**
     * Display batches and attendance analytics dashboard.
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        // 1. Run a lightweight attendance sync check to ensure 100% up-to-date data
        $this->performAttendanceSync();

        // 2. Global KPIs
        $totalNotices = BookletNotice::count();
        $totalAttended = BookletNotice::attended()->count();
        $totalUnattended = BookletNotice::unattended()->count();
        $attendanceRate = $totalNotices > 0 ? round(($totalAttended / $totalNotices) * 100, 1) : 0;

        // 3. Batches Summary List
        $batchesQuery = BookletNotice::select(
            'batch_code',
            'batch_name',
            DB::raw('MIN(sent_at) as sent_at'),
            DB::raw('MIN(sent_by) as sent_by'),
            DB::raw('MIN(channel) as channel'),
            DB::raw('COUNT(*) as total_count'),
            DB::raw("COUNT(CASE WHEN status = 'attended' OR attended_at IS NOT NULL THEN 1 END) as attended_count"),
            DB::raw("COUNT(CASE WHEN status = 'sent' AND attended_at IS NULL THEN 1 END) as unattended_count")
        )
        ->groupBy('batch_code', 'batch_name')
        ->orderByDesc(DB::raw('MIN(sent_at)'));

        $batches = $batchesQuery->paginate(10)->withQueryString();

        // 4. If viewing details of a specific batch
        $selectedBatchCode = $request->get('batch');
        $batchNotices = null;
        $selectedBatchSummary = null;

        if ($selectedBatchCode) {
            $batchSummaryQuery = BookletNotice::where('batch_code', $selectedBatchCode);
            $selectedBatchSummary = [
                'batch_code' => $selectedBatchCode,
                'batch_name' => (clone $batchSummaryQuery)->value('batch_name'),
                'sent_at' => (clone $batchSummaryQuery)->min('sent_at'),
                'channel' => (clone $batchSummaryQuery)->value('channel'),
                'total_count' => (clone $batchSummaryQuery)->count(),
                'attended_count' => (clone $batchSummaryQuery)->attended()->count(),
                'unattended_count' => (clone $batchSummaryQuery)->unattended()->count(),
            ];
            $selectedBatchSummary['attendance_rate'] = $selectedBatchSummary['total_count'] > 0 
                ? round(($selectedBatchSummary['attended_count'] / $selectedBatchSummary['total_count']) * 100, 1) 
                : 0;

            $noticesQuery = BookletNotice::with(['transaction.plateType', 'transaction.carMake', 'attendedTransaction'])
                ->where('batch_code', $selectedBatchCode);

            // Filter by attendance status inside batch
            if ($request->get('filter') === 'attended') {
                $noticesQuery->attended();
            } elseif ($request->get('filter') === 'unattended') {
                $noticesQuery->unattended();
            }

            if ($request->filled('search')) {
                $s = $request->get('search');
                $noticesQuery->where(function($q) use ($s) {
                    $q->where('visitor_name', 'like', "%{$s}%")
                      ->orWhere('plate_number', 'like', "%{$s}%")
                      ->orWhere('phone_number', 'like', "%{$s}%")
                      ->orWhere('booklet_number', 'like', "%{$s}%");
                });
            }

            $batchNotices = $noticesQuery->orderByDesc('id')->paginate(20)->withQueryString();
        }

        return view('transactions.expired_notices_analytics', compact(
            'totalNotices',
            'totalAttended',
            'totalUnattended',
            'attendanceRate',
            'batches',
            'selectedBatchCode',
            'selectedBatchSummary',
            'batchNotices'
        ));
    }

    /**
     * Create a new notice batch for selected expired booklets.
     */
    public function storeBatch(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'integer|exists:transactions,id',
            'channel' => 'required|in:whatsapp,viber,sms',
            'batch_name' => 'nullable|string|max:150',
            'message_template' => 'nullable|string|max:1000',
        ]);

        $transactions = Transaction::with(['plateType', 'carMake'])
            ->whereIn('id', $validated['transaction_ids'])
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'هیچ مامەڵەیەک نەدۆزرایەوە!',
            ], 422);
        }

        $batchCode = 'BATCH-' . date('Ymd-His') . '-' . rand(100, 999);
        $batchName = $validated['batch_name'] ?: ('وەجبەی ' . now()->format('Y-m-d H:i') . ' (' . $transactions->count() . ' هاووڵاتی)');
        $now = now();
        $userId = auth()->id();
        $channel = $validated['channel'];

        $createdNotices = [];

        DB::transaction(function () use ($transactions, $batchCode, $batchName, $validated, $now, $userId, $channel, &$createdNotices) {
            foreach ($transactions as $tx) {
                // Compose text
                $defaultText = BookletNotice::generateDefaultMessage(
                    $tx->visitor_name ?? 'هاووڵاتی',
                    $tx->plate_number ?? '',
                    $tx->booklet_number,
                    $tx->end_date?->format('Y-m-d')
                );

                $messageText = $validated['message_template'] ?: $defaultText;
                // Replace template placeholders
                $messageText = str_replace(
                    ['{ناو}', '{تابلۆ}', '{دەفتەر}', '{بەروار}'],
                    [$tx->visitor_name ?? '', $tx->plate_number ?? '', $tx->booklet_number ?? '', $tx->end_date?->format('Y-m-d') ?? ''],
                    $messageText
                );

                // Check if citizen has already renewed/cancelled (e.g. today or recently)
                $existingNewTx = Transaction::where(function($q) use ($tx) {
                        if ($tx->chassis_number) {
                            $q->where('chassis_number', $tx->chassis_number);
                        }
                        if ($tx->plate_number) {
                            $q->orWhere('plate_number', $tx->plate_number);
                        }
                    })
                    ->where('id', '!=', $tx->id)
                    ->where('created_at', '>=', $tx->created_at)
                    ->latest()
                    ->first();

                $isAttended = (bool)$existingNewTx;

                $notice = BookletNotice::create([
                    'transaction_id' => $tx->id,
                    'batch_code' => $batchCode,
                    'batch_name' => $batchName,
                    'phone_number' => $tx->phone_number,
                    'visitor_name' => $tx->visitor_name,
                    'plate_number' => $tx->plate_number,
                    'chassis_number' => $tx->chassis_number,
                    'booklet_number' => $tx->booklet_number,
                    'end_date' => $tx->end_date,
                    'channel' => $channel,
                    'message_text' => $messageText,
                    'sent_by' => $userId,
                    'sent_at' => $now,
                    'status' => $isAttended ? 'attended' : 'sent',
                    'attended_at' => $isAttended ? ($existingNewTx->created_at ?: $now) : null,
                    'attended_transaction_id' => $isAttended ? $existingNewTx->id : null,
                    'attended_type' => $isAttended ? ($existingNewTx->transactionType?->name_kurdish ?? 'مامەڵەی نوێ') : null,
                ]);

                $normalizedPhone = BookletNotice::normalizePhoneNumber($tx->phone_number);
                $whatsappUrl = $normalizedPhone ? ('https://wa.me/' . $normalizedPhone . '?text=' . urlencode($messageText)) : null;
                $viberUrl = $normalizedPhone ? ('viber://chat?number=%2B' . $normalizedPhone) : null;

                $createdNotices[] = [
                    'id' => $notice->id,
                    'visitor_name' => $tx->visitor_name,
                    'phone_number' => $tx->phone_number,
                    'normalized_phone' => $normalizedPhone,
                    'plate_number' => $tx->plate_number,
                    'booklet_number' => $tx->booklet_number,
                    'whatsapp_url' => $whatsappUrl,
                    'viber_url' => $viberUrl,
                    'status' => $notice->status,
                ];
            }
        });

        AuditLogger::log(
            'ناردنی وەجبەی دەفتەرە بەسەرچووەکان',
            BookletNotice::class,
            null,
            "کۆدی وەجبە: {$batchCode} - ناوی وەجبە: {$batchName} - ژمارەی هاووڵاتی: " . count($createdNotices)
        );

        return response()->json([
            'success' => true,
            'message' => "وەجبەی نوێ بە سەرکەوتوویی دروستکرا بۆ (" . count($createdNotices) . ") هاووڵاتی!",
            'batch_code' => $batchCode,
            'batch_name' => $batchName,
            'channel' => $channel,
            'notices' => $createdNotices,
        ]);
    }

    /**
     * AJAX endpoint to automatically select the next X (e.g. 100) unnotified expired booklets.
     */
    public function selectUnnotified(Request $request)
    {
        $this->authorizeAccess();

        $limit = min((int)$request->get('limit', 100), 200);

        // Fetch oldest expired booklet transaction IDs that have NO records in booklet_notices
        $now = now()->startOfDay();
        $bookletTypeIds = [1, 2, 3, 4, 7];

        $ids = Transaction::whereIn('transaction_type_id', $bookletTypeIds)
            ->whereNotNull('end_date')
            ->where('end_date', '<', $now)
            ->whereNull('cancelled_at')
            ->where(function($q) {
                $q->whereNull('transactions.is_cancelled')->orWhere('transactions.is_cancelled', 0);
            })
            ->whereNotExists(function($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('transactions as t_newer')
                  ->whereColumn('t_newer.plate_number', 'transactions.plate_number')
                  ->where('t_newer.id', '>', \Illuminate\Support\Facades\DB::raw('transactions.id'))
                  ->whereNull('t_newer.deleted_at');
            })
            ->whereDoesntHave('bookletNotices')
            ->orderBy('end_date', 'asc')
            ->limit($limit)
            ->pluck('id');

        return response()->json([
            'success' => true,
            'count' => $ids->count(),
            'ids' => $ids,
        ]);
    }

    /**
     * Print official report of citizens who received a notice but have NOT attended.
     */
    public function printNoShows(Request $request, string $batchCode)
    {
        $this->authorizeAccess();

        $batchSummaryQuery = BookletNotice::where('batch_code', $batchCode);
        $batchName = (clone $batchSummaryQuery)->value('batch_name') ?: $batchCode;
        $sentAt = (clone $batchSummaryQuery)->min('sent_at');

        $noShows = BookletNotice::with(['transaction.plateType', 'transaction.carMake'])
            ->where('batch_code', $batchCode)
            ->unattended()
            ->orderBy('end_date', 'asc')
            ->get();

        return view('transactions.print_expired_no_shows', compact(
            'batchCode',
            'batchName',
            'sentAt',
            'noShows'
        ));
    }

    /**
     * Synchronize unattended notices with newer transactions in database.
     */
    public function syncAttendance()
    {
        $this->authorizeAccess();
        $syncedCount = $this->performAttendanceSync();

        return response()->json([
            'success' => true,
            'synced_count' => $syncedCount,
            'message' => "بە سەرکەوتوویی پشکنین کرا، ({$syncedCount}) هاووڵاتی وەک هاتووە نوێکرایەوە.",
        ]);
    }

    /**
     * Helper method to scan unattended notices and auto-mark attended if matching transactions exist.
     */
    protected function performAttendanceSync(): int
    {
        $unattended = BookletNotice::where('status', 'sent')
            ->whereNull('attended_at')
            ->get();

        $count = 0;

        foreach ($unattended as $notice) {
            $newTx = Transaction::where(function($q) use ($notice) {
                    if ($notice->chassis_number) {
                        $q->where('chassis_number', $notice->chassis_number);
                    }
                    if ($notice->plate_number) {
                        $q->orWhere('plate_number', $notice->plate_number);
                    }
                })
                ->where('id', '!=', $notice->transaction_id)
                ->where(function($q) use ($notice) {
                    if ($notice->sent_at) {
                        $q->where('created_at', '>=', $notice->sent_at->copy()->subMinutes(5));
                    }
                })
                ->latest()
                ->first();

            if ($newTx) {
                $notice->update([
                    'status' => 'attended',
                    'attended_at' => $newTx->created_at ?: now(),
                    'attended_transaction_id' => $newTx->id,
                    'attended_type' => $newTx->transactionType?->name_kurdish ?? 'مامەڵەی نوێ',
                ]);
                $count++;
            }
        }

        return $count;
    }
}
