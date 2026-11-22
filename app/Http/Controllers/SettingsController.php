<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionType;
use App\Models\Director;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function pricing()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('settings.pricing')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی دەستکاریکردنی نرخەکانت نییە بەپێی پێرمشنەکان!');
        }

        $transactionTypes = TransactionType::all();
        $directors = Director::orderBy('id', 'asc')->get();
        $activeDirector = Director::getActiveDirector();

        return view('settings.pricing', compact('transactionTypes', 'directors', 'activeDirector'));
    }

    public function updatePricing(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('settings.pricing')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی دەستکاریکردنی نرخەکانت نییە بەپێی پێرمشنەکان!');
        }
        // 1. Update Active Director if selected
        if ($request->filled('active_director_id')) {
            Director::query()->update(['is_active' => false]);
            $activeDir = Director::find($request->active_director_id);
            if ($activeDir) {
                $activeDir->update(['is_active' => true]);
                Cache::forget('all_directors');
                Cache::forget('active_director');
                AuditLogger::log('دیاریکردنی بەڕێوەبەری سەرەکی بۆ نووسراوەکان', Director::class, $activeDir->id, $activeDir->name);
            }
        }

        // 2. Add New Director if name entered
        if ($request->filled('new_director_name')) {
            $newName = trim($request->new_director_name);
            if (!empty($newName)) {
                $makeActive = $request->boolean('make_new_director_active') || !Director::where('is_active', true)->exists();
                if ($makeActive) {
                    Director::query()->update(['is_active' => false]);
                }
                $newDir = Director::create([
                    'name' => $newName,
                    'is_active' => $makeActive,
                ]);
                Cache::forget('all_directors');
                Cache::forget('active_director');
                AuditLogger::log('زیادکردنی بەڕێوەبەری نوێ لە فۆڕمی ڕێکخستن', Director::class, $newDir->id, $newDir->name);
            }
        }

        $prices = $request->input('prices', []);

        foreach ($prices as $id => $data) {
            $txType = TransactionType::find($id);
            if ($txType) {
                $txType->update([
                    'pay_1_year' => $data['pay_1_year'] ?? 0,
                    'pay_2_year' => $data['pay_2_year'] ?? 0,
                    'pay_3_year' => $data['pay_3_year'] ?? 0,
                    'stamp_pay' => $data['stamp_pay'] ?? 0,
                    'form_pay' => $data['form_pay'] ?? 0,
                    'fine_pay' => $data['fine_pay'] ?? 0,
                    'inspection_pay' => $data['inspection_pay'] ?? 0,
                    'bus_inspection_pay' => $data['bus_inspection_pay'] ?? 0,
                ]);
            }
        }

        AuditLogger::log('دەستکاریکردنی نرخی مامەڵەکان لایەن سۆپەر ئەدمین', null, null, 'گۆڕانکاری لە سزاکان، ڕسوومات و نرخەکان کرا');

        return redirect()->back()->with('success', 'ڕێکخستنەکان (بەڕێوەبەری سەرەکی نووسراوەکان و نرخەکان) بە سەرکەوتوویی پاشەکەوتکران.');
    }

    public function lookups()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('settings.lookups')) {
            return redirect()->route('dashboard')->with('error', 'دەسەڵاتی بەڕێوەبردنی پێناسە و لیستەکانت نییە بەپێی پێرمشنەکان!');
        }

        $transactionTypes = TransactionType::all();
        $directorates = \App\Models\TrafficDirectorate::all();
        $directors = \App\Models\Director::all();
        $passportIssuePlaces = \App\Models\PassportIssuePlace::orderBy('name')->get();
        $carMakes = \App\Models\CarMake::orderBy('name_kurdish')->get();
        $carColors = \App\Models\CarColor::orderBy('id')->get();

        return view('settings.lookups', compact('transactionTypes', 'directorates', 'directors', 'passportIssuePlaces', 'carMakes', 'carColors'));
    }

    /**
     * Download database backup (Admin ONLY).
     */
    public function backupDownload()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'تەنها سەرپەرشتیاری گشتی (Admin) دەسەڵاتی داگرتنی کۆپی یەدەگ و باک ئەپی داتابەیسی هەیە!');
        }

        $dbPath = database_path('database.sqlite');
        if (!file_exists($dbPath)) {
            return redirect()->back()->with('error', 'فایلی داتابەیس نەدۆزرایەوە!');
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "customs_database_backup_{$timestamp}.sqlite";

        AuditLogger::log('داگرتنی باک ئەپی داتابەیس', null, null, "ئەدمین کۆپییەکی یەدەگی داتابەیسی داگرت: {$fileName}");

        return response()->download($dbPath, $fileName, [
            'Content-Type' => 'application/x-sqlite3',
        ]);
    }
}
