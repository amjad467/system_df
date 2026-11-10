<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'transaction_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'date_nusraw_puchal' => 'date',
        'passport_issue_date' => 'date',
        'user_edit_date' => 'datetime',
        'inspected_at' => 'datetime',
        'audited_at' => 'datetime',
        'paid_at' => 'datetime',
        'booklet_completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_inspected' => 'boolean',
        'is_audited' => 'boolean',
        'is_paid' => 'boolean',
        'is_booklet_completed' => 'boolean',
        'is_pledge_completed' => 'boolean',
        'is_printed' => 'boolean',
        'is_cancelled' => 'boolean',
        'is_returned' => 'boolean',
    ];

    public function getIsCancelledAttribute(): bool
    {
        return !is_null($this->cancelled_at);
    }

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    public function relatedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by_user_id');
    }



    public function trafficDirectorate(): BelongsTo
    {
        return $this->belongsTo(TrafficDirectorate::class, 'traffic_directorate_id');
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'director_id');
    }

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function plateType(): BelongsTo
    {
        return $this->belongsTo(PlateType::class, 'plate_type_id');
    }

    public function carMake(): BelongsTo
    {
        return $this->belongsTo(CarMake::class, 'car_make_id');
    }

    public function carColor(): BelongsTo
    {
        return $this->belongsTo(CarColor::class, 'car_color_id');
    }

    public function canBeEditedByDataEntry(): bool
    {
        if ($this->is_returned) {
            return true;
        }
        return !$this->is_inspected && !$this->is_audited && !$this->is_paid && !$this->is_booklet_completed && !$this->is_cancelled && !$this->is_submitted;
    }

    /**
     * Check if transaction type requires Booklet Printing (چاپی دەفتەر).
     * Types requiring Booklet Print:
     * 1: دەرهێنانی دەفتەر (New Booklet Issue)
     * 2: تازەکردنەوەی دەفتەر (Booklet Renewal)
     * 3: ناوگۆڕینی دەفتەر (Name Change)
     * 4: دەفتەر گۆڕین (Booklet Replacement)
     * 7: ناوگۆڕین و دەفتەرگۆڕین (Name & Booklet Change)
     */
    public function requiresBooklet(): bool
    {
        if (in_array((int)$this->transaction_type_id, [1, 2, 3, 4, 7])) {
            return true;
        }

        if ($this->transactionType) {
            $name = $this->transactionType->name_kurdish;
            if (str_contains($name, 'دەرهێنان') || 
                str_contains($name, 'تازە') || str_contains($name, 'تازه') ||
                str_contains($name, 'ناوگۆڕین') || 
                str_contains($name, 'دەفتەر گۆڕین') || str_contains($name, 'دەفتەرگۆڕین')) {
                return true;
            }
        }

        return false;
    }

    public function isFullBookletForm(): bool
    {
        return $this->requiresBooklet();
    }

    public function isCancellationOrInspection(): bool
    {
        if (in_array((int)$this->transaction_type_id, [5, 6, 8])) {
            return true;
        }
        if ($this->transactionType) {
            $name = $this->transactionType->name_kurdish;
            return str_contains($name, 'پووچەڵ') || str_contains($name, 'پشکنین') || str_contains($name, 'بەڵێننامە');
        }
        return false;
    }

    public function isCancellation(): bool
    {
        if ((int)$this->transaction_type_id === 5) {
            return true;
        }
        return $this->transactionType && str_contains($this->transactionType->name_kurdish, 'پووچەڵ');
    }

    public function isInspection(): bool
    {
        if ((int)$this->transaction_type_id === 8) {
            return true;
        }
        return $this->transactionType && str_contains($this->transactionType->name_kurdish, 'پشکنین');
    }

    /**
     * Get human readable Kurdish description of current stage and status.
     */
    public function getCurrentStageName(): string
    {
        if ($this->is_cancelled) {
            return 'پووچەڵکراوە';
        }
        if ($this->is_submitted) {
            return 'سەبمیت و ئەرشیفکراو (کۆتایی)';
        }
        if ($this->is_booklet_completed) {
            return 'دەفتەری بۆ تەواوکراوە (چاوەڕێی سەبمیتە)';
        }
        if ($this->is_paid) {
            if ($this->requiresBooklet()) {
                return 'پارەدانی بۆ ئەنجامدراوە (چاوەڕێی چاپی دەفتەرە)';
            } else {
                return 'پارەدانی بۆ ئەنجامدراوە (چاپی دەفتەر ناکەن - چاوەڕێی سەبمیتە)';
            }
        }
        if ($this->is_audited) {
            return 'وردبینی بۆ ئەنجامدراوە (چاوەڕێی پارەدان و وەسڵە)';
        }
        if ($this->is_inspected) {
            return 'کەشف و تەخمینی بۆ ئەنجامدراوە (چاوەڕێی وردبینییە)';
        }
        return 'داتا ئەنتەری بۆ کراوە (چاوەڕێی کەشف و تەخمینە)';
    }

    /**
     * Get detailed status of exactly WHERE the transaction is right now.
     */
    public function getCurrentLocationDescription(): array
    {
        if ($this->is_cancelled) {
            return [
                'title' => 'پووچەڵکراوەتەوە',
                'description' => 'ئەم مامەڵەیە بە تەواوی ڕاگیراوە و پووچەڵکراوەتەوە' . ($this->cancellation_reason ? " (هۆکار: {$this->cancellation_reason})" : ''),
                'badge_color' => 'rose',
                'icon' => 'fa-ban',
                'step' => 0,
            ];
        }

        if ($this->is_returned) {
            return [
                'title' => 'گەڕێنراوەتەوە (پێویستی بە ڕاستکردنەوەیە)',
                'description' => 'گەڕێنراوەتەوە بۆ داتائەنتەری. هۆکار: ' . ($this->return_reason ?? 'هۆکار دیارینەکراوە'),
                'badge_color' => 'rose',
                'icon' => 'fa-triangle-exclamation',
                'step' => 1,
            ];
        }

        if ($this->is_submitted) {
            return [
                'title' => 'سەبمیت و ئەرشیفکراو',
                'description' => 'مامەڵەکە بە تەواوی تەواوبووە و لە ئەرشیف پارێزراوە لەلایەن ' . ($this->submitted_by ?? 'کارمەند'),
                'badge_color' => 'emerald',
                'icon' => 'fa-box-archive',
                'step' => 6,
            ];
        }

        if ($this->is_booklet_completed) {
            return [
                'title' => 'دەفتەری گەشتیاری دەرکراوە',
                'description' => 'ژمارەی دەفتەر: ' . $this->booklet_number . ' - ئامادەیە بۆ ڕادەستکردن و سەبمیت',
                'badge_color' => 'indigo',
                'icon' => 'fa-passport',
                'step' => 5,
            ];
        }

        if ($this->is_paid) {
            if ($this->requiresBooklet()) {
                return [
                    'title' => 'لە بەشی دەفتەرە (چاوەڕوانی دەفتەر)',
                    'description' => 'پارە دراوە (وەسڵی ۳۷-ئـ: ' . ($this->receipt_37a_number ?? '-') . ') - ئێستا لە بەشی دەفتەرە بۆ لێدانی ژمارەی دەفتەری گەشتیاری',
                    'badge_color' => 'sky',
                    'icon' => 'fa-passport',
                    'step' => 5,
                ];
            } else {
                return [
                    'title' => 'پارە دراوە (چاوەڕوانی سەبمیت)',
                    'description' => 'وەسڵی ۳۷-ئـ بڕاوە (ژمارە: ' . ($this->receipt_37a_number ?? '-') . ') - پێویستی بە دەفتەر نییە، چاوەڕێی کۆتاییهێنانە',
                    'badge_color' => 'emerald',
                    'icon' => 'fa-check-circle',
                    'step' => 5,
                ];
            }
        }

        if ($this->is_audited) {
            return [
                'title' => 'لە قاسەیە (چاوەڕوانی پارەدان و وەسڵ)',
                'description' => 'وردبینی لەلایەن ' . ($this->audited_by ?? 'وردبین') . ' کراوە - ئێستا لە بەردەم قاسەیە بۆ بڕینی وەسڵی ۳۷-ئـ و وەرگرتنی پارە',
                'badge_color' => 'amber',
                'icon' => 'fa-cash-register',
                'step' => 4,
            ];
        }

        if ($this->is_inspected) {
            return [
                'title' => 'لە ژووری وردبینییە (چاوەڕوانی وردبین)',
                'description' => 'کەشف و تەخمین لەلایەن ' . ($this->inspected_by ?? 'ئەندازیار') . ' تەواوکراوە - ئێستا لەلای وردبینی ژمێریارییە',
                'badge_color' => 'purple',
                'icon' => 'fa-clipboard-check',
                'step' => 3,
            ];
        }

        return [
            'title' => 'لە بەشی کەشف و تەخمینە (لای ئەندازیار)',
            'description' => 'داتاکان داخڵکراون لەلایەن ' . ($this->user_input ?? 'داتائەنتەری') . ' - ئێستا چاوەڕێی پشکنینی ئۆتۆمبێلە لەلایەن ئەندازیاری تەخمین',
            'badge_color' => 'orange',
            'icon' => 'fa-magnifying-glass',
            'step' => 2,
        ];
    }

    /**
     * Check if given user can pass the CURRENT active stage.
     */
    public function canUserPassStage(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($this->is_cancelled || $this->is_submitted) {
            return false;
        }

        // Active Stage 2: Inspection
        if (!$this->is_inspected) {
            return $user->role === 'inspector';
        }

        // Active Stage 3: Audit
        if (!$this->is_audited) {
            return $user->role === 'auditor';
        }

        // Active Stage 4: Payment
        if (!$this->is_paid) {
            return $user->role === 'cashier';
        }

        // Active Stage 5: Booklet (if applicable)
        if ($this->requiresBooklet() && !$this->is_booklet_completed) {
            return $user->role === 'booklet';
        }

        // Final Submit
        return in_array($user->role, ['data_entry', 'booklet', 'cashier']);
    }

    public function scopeExpiredBooklets($query)
    {
        // Booklet types: 1: New Issue, 2: Renewal, 3: Name Change, 4: Booklet Replacement, 7: Both
        $bookletTypeIds = [1, 2, 3, 4, 7];
        return $query->whereIn('transaction_type_id', $bookletTypeIds)
            ->whereNotNull('end_date')
            ->whereNull('cancelled_at')
            ->where('end_date', '<', now()->startOfDay());
    }

    public function scopeExpiringSoonBooklets($query, $days = 30)
    {
        $bookletTypeIds = [1, 2, 3, 4, 7];
        $now = now()->startOfDay();
        $soon = now()->addDays($days)->endOfDay();
        return $query->whereIn('transaction_type_id', $bookletTypeIds)
            ->whereNotNull('end_date')
            ->whereNull('cancelled_at')
            ->where('end_date', '>=', $now)
            ->where('end_date', '<=', $soon);
    }
}

