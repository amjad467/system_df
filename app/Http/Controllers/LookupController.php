<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\TrafficDirectorate;
use App\Models\Director;
use App\Models\PlateType;
use App\Models\CarMake;
use App\Models\CarColor;
use App\Models\TransactionType;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Cache;

class LookupController extends Controller
{
    // 1. Directorates
    public function storeDirectorate(Request $request)
    {
        $validated = $request->validate([
            'name_kurdish' => 'required|string|max:255|unique:traffic_directorates,name_kurdish',
            'name_english' => 'nullable|string|max:255',
        ], [
            'name_kurdish.unique' => 'ئەم بەڕێوەبەرایەتییە پێشتر تۆمارکراوە!'
        ]);

        $item = TrafficDirectorate::create($validated);
        Cache::forget('all_traffic_directorates');
        AuditLogger::log('زیادکردنی بەڕێوەبەرایەتی نوێ', TrafficDirectorate::class, $item->id, $item->name_kurdish);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function updateDirectorate(Request $request, TrafficDirectorate $directorate)
    {
        $validated = $request->validate([
            'name_kurdish' => ['required', 'string', 'max:255', Rule::unique('traffic_directorates', 'name_kurdish')->ignore($directorate->id)],
        ], [
            'name_kurdish.unique' => 'ئەم ناوی بەڕێوەبەرایەتییە پێشتر هەبووە!'
        ]);

        $directorate->update($validated);
        Cache::forget('all_traffic_directorates');
        AuditLogger::log('دەستکاریکردنی ناوی بەڕێوەبەرایەتی', TrafficDirectorate::class, $directorate->id, $directorate->name_kurdish);

        return response()->json(['success' => true, 'item' => $directorate]);
    }

    // 2. Directors
    private function ensureDirectorActiveColumn(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('directors') && !\Illuminate\Support\Facades\Schema::hasColumn('directors', 'is_active')) {
                try {
                    \Illuminate\Support\Facades\DB::statement('ALTER TABLE directors ADD COLUMN is_active INTEGER NOT NULL DEFAULT 0');
                } catch (\Throwable $e2) {
                    \Illuminate\Support\Facades\Schema::table('directors', function ($table) {
                        $table->boolean('is_active')->default(false);
                    });
                }
                $target = Director::where('name', 'like', '%قادر%')->first() ?? Director::first();
                if ($target) {
                    $target->update(['is_active' => true]);
                }
            }
        } catch (\Throwable $e) {}
    }

    public function storeDirector(Request $request)
    {
        $this->ensureDirectorActiveColumn();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:directors,name',
        ], [
            'name.unique' => 'ئەم ناوەی بەڕێوەبەر پێشتر تۆمارکراوە!'
        ]);

        $hasCol = \Illuminate\Support\Facades\Schema::hasColumn('directors', 'is_active');
        $makeActive = $request->boolean('is_active');

        if ($hasCol) {
            $hasActive = Director::where('is_active', true)->exists();
            if ($makeActive || !$hasActive) {
                Director::query()->update(['is_active' => false]);
                $validated['is_active'] = true;
            } else {
                $validated['is_active'] = false;
            }
        }

        $item = Director::create($validated);
        Cache::forget('all_directors');
        Cache::forget('active_director');
        AuditLogger::log('زیادکردنی بەڕێوەبەری نوێ', Director::class, $item->id, $item->name);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()->back()->with('success', "بەڕێوەبەری نوێ [{$item->name}] بە سەرکەوتوویی تۆمارکرا.");
    }

    public function setActiveDirector(Request $request, Director $director)
    {
        $this->ensureDirectorActiveColumn();

        if (\Illuminate\Support\Facades\Schema::hasColumn('directors', 'is_active')) {
            Director::query()->update(['is_active' => false]);
            $director->update(['is_active' => true]);
        }

        Cache::forget('all_directors');
        Cache::forget('active_director');
        AuditLogger::log('دیاریکردنی بەڕێوەبەری سەرەکی بۆ نووسراوەکان', Director::class, $director->id, $director->name);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'item' => $director]);
        }

        return redirect()->back()->with('success', "بەڕێوەبەر [{$director->name}] دیاریکرا وەک بەڕێوەبەری سەرەکی لەسەر نووسراوە فەرمییەکان.");
    }

    public function setActiveDirectorDropdown(Request $request)
    {
        $this->ensureDirectorActiveColumn();

        $validated = $request->validate([
            'director_id' => 'required|exists:directors,id',
        ]);

        $director = Director::findOrFail($validated['director_id']);
        if (\Illuminate\Support\Facades\Schema::hasColumn('directors', 'is_active')) {
            Director::query()->update(['is_active' => false]);
            $director->update(['is_active' => true]);
        }

        Cache::forget('all_directors');
        Cache::forget('active_director');
        AuditLogger::log('دیاریکردنی بەڕێوەبەری سەرەکی بۆ نووسراوەکان', Director::class, $director->id, $director->name);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'item' => $director]);
        }

        return redirect()->back()->with('success', "بەڕێوەبەر [{$director->name}] دیاریکرا وەک بەڕێوەبەری سەرەکی لەسەر نووسراوە فەرمییەکان.");
    }

    public function destroyDirector(Request $request, Director $director)
    {
        $this->ensureDirectorActiveColumn();

        $name = $director->name;
        $id = $director->id;
        $wasActive = $director->is_active ?? false;

        // Nullify references in transactions first to prevent foreign key errors
        try {
            \App\Models\Transaction::where('director_id', $id)->update(['director_id' => null]);
        } catch (\Throwable $e) {}

        $director->delete();

        // Ensure at least one is active if available
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('directors', 'is_active')) {
                if ($wasActive || !Director::where('is_active', true)->exists()) {
                    Director::first()?->update(['is_active' => true]);
                }
            }
        } catch (\Throwable $e) {}

        Cache::forget('all_directors');
        Cache::forget('active_director');
        AuditLogger::log('سڕینەوەی بەڕێوەبەر', Director::class, $id, $name);

        return redirect()->back()->with('success', "بەڕێوەبەر [{$name}] بە سەرکەوتوویی سڕایەوە.");
    }

    public function updateDirector(Request $request, Director $director)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('directors', 'name')->ignore($director->id)],
        ], [
            'name.unique' => 'ئەم ناوەی بەڕێوەبەر پێشتر هەبووە!'
        ]);

        $director->update($validated);
        Cache::forget('all_directors');
        Cache::forget('active_director');
        AuditLogger::log('دەستکاریکردنی ناوی بەڕێوەبەر', Director::class, $director->id, $director->name);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'item' => $director]);
        }

        return redirect()->back()->with('success', "ناوی بەڕێوەبەر نوێکرایەوە.");
    }

    // 3. Plate Types
    public function storePlateType(Request $request)
    {
        $validated = $request->validate([
            'name_kurdish' => 'required|string|max:255|unique:plate_types,name_kurdish',
            'name_english' => 'nullable|string|max:255',
        ], [
            'name_kurdish.unique' => 'ئەم جۆرە تابلۆیە پێشتر تۆمارکراوە!'
        ]);

        $item = PlateType::create($validated);
        Cache::forget('all_plate_types');
        AuditLogger::log('زیادکردنی جۆری تابلۆی نوێ', PlateType::class, $item->id, $item->name_kurdish);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function updatePlateType(Request $request, PlateType $plateType)
    {
        $validated = $request->validate([
            'name_kurdish' => ['required', 'string', 'max:255', Rule::unique('plate_types', 'name_kurdish')->ignore($plateType->id)],
        ], [
            'name_kurdish.unique' => 'ئەم جۆرە تابلۆیە پێشتر هەبووە!'
        ]);

        $plateType->update($validated);
        Cache::forget('all_plate_types');
        AuditLogger::log('دەستکاریکردنی ناوی جۆری تابلۆ', PlateType::class, $plateType->id, $plateType->name_kurdish);

        return response()->json(['success' => true, 'item' => $plateType]);
    }

    // 4. Car Makes
    public function storeCarMake(Request $request)
    {
        $validated = $request->validate([
            'name_kurdish' => 'required|string|max:255|unique:car_makes,name_kurdish',
            'name_english' => 'required|string|max:255|unique:car_makes,name_english',
        ], [
            'name_kurdish.unique' => 'ئەم مارکە کوردییە پێشتر تۆمارکراوە!',
            'name_english.unique' => 'ئەم مارکە ئینگلیزییە پێشتر تۆمارکراوە!'
        ]);

        $item = CarMake::create($validated);
        Cache::forget('all_car_makes');
        AuditLogger::log('زیادکردنی مارکەی ئۆتۆمبێلی نوێ', CarMake::class, $item->id, "{$item->name_kurdish} - {$item->name_english}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'item' => $item, 'make' => $item]);
        }

        return redirect()->back()->with('success', "مارکەی نوێ [{$item->name_kurdish}] زیادکرا.");
    }

    public function updateCarMake(Request $request, CarMake $carMake)
    {
        $validated = $request->validate([
            'name_kurdish' => ['required', 'string', 'max:255', Rule::unique('car_makes', 'name_kurdish')->ignore($carMake->id)],
            'name_english' => ['required', 'string', 'max:255', Rule::unique('car_makes', 'name_english')->ignore($carMake->id)],
        ], [
            'name_kurdish.unique' => 'ئەم مارکە کوردییە پێشتر هەبووە!',
            'name_english.unique' => 'ئەم مارکە ئینگلیزییە پێشتر هەبووە!'
        ]);

        $carMake->update($validated);
        Cache::forget('all_car_makes');
        AuditLogger::log('دەستکاریکردنی ناوی مارکەی ئۆتۆمبێل', CarMake::class, $carMake->id, "{$carMake->name_kurdish} - {$carMake->name_english}");

        return response()->json(['success' => true, 'item' => $carMake]);
    }

    // 5. Car Colors
    public function storeCarColor(Request $request)
    {
        $validated = $request->validate([
            'name_kurdish' => 'required|string|max:255|unique:car_colors,name_kurdish',
            'name_english' => 'nullable|string|max:255',
        ], [
            'name_kurdish.unique' => 'ئەم ڕەنگە کوردییە پێشتر تۆمارکراوە!',
        ]);

        if (empty($validated['name_english'])) {
            $validated['name_english'] = $validated['name_kurdish'];
        }

        $item = CarColor::create($validated);
        Cache::forget('all_car_colors');
        AuditLogger::log('زیادکردنی ڕەنگی نوێ', CarColor::class, $item->id, "{$item->name_kurdish} - {$item->name_english}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'item' => $item, 'color' => $item]);
        }

        return redirect()->back()->with('success', "ڕەنگی نوێ [{$item->name_kurdish}] زیادکرا.");
    }

    public function updateCarColor(Request $request, CarColor $carColor)
    {
        $validated = $request->validate([
            'name_kurdish' => ['required', 'string', 'max:255', Rule::unique('car_colors', 'name_kurdish')->ignore($carColor->id)],
            'name_english' => ['required', 'string', 'max:255', Rule::unique('car_colors', 'name_english')->ignore($carColor->id)],
        ], [
            'name_kurdish.unique' => 'ئەم ڕەنگە کوردییە پێشتر هەبووە!',
            'name_english.unique' => 'ئەم ڕەنگە ئینگلیزییە پێشتر هەبووە!'
        ]);

        $carColor->update($validated);
        Cache::forget('all_car_colors');
        AuditLogger::log('دەستکاریکردنی ناوی ڕەنگی ئۆتۆمبێل', CarColor::class, $carColor->id, "{$carColor->name_kurdish} - {$carColor->name_english}");

        return response()->json(['success' => true, 'item' => $carColor]);
    }

    // 6. Transaction Types
    public function storeTransactionType(Request $request)
    {
        $validated = $request->validate([
            'name_kurdish' => 'required|string|max:255|unique:transaction_types,name_kurdish',
            'name_english' => 'nullable|string|max:255',
        ], [
            'name_kurdish.unique' => 'ئەم جۆرە مامەڵەیە پێشتر تۆمارکراوە!'
        ]);

        $item = TransactionType::create($validated);
        Cache::forget('all_transaction_types');
        AuditLogger::log('زیادکردنی جۆری مامەڵەی نوێ', TransactionType::class, $item->id, $item->name_kurdish);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function updateTransactionType(Request $request, TransactionType $transactionType)
    {
        $validated = $request->validate([
            'name_kurdish' => ['required', 'string', 'max:255', Rule::unique('transaction_types', 'name_kurdish')->ignore($transactionType->id)],
        ], [
            'name_kurdish.unique' => 'ئەم ناوی مامەڵەیە پێشتر هەبووە!'
        ]);

        $transactionType->update($validated);
        Cache::forget('all_transaction_types');
        AuditLogger::log('دەستکاریکردنی ناوی جۆری مامەڵە', TransactionType::class, $transactionType->id, $transactionType->name_kurdish);

        return response()->json(['success' => true, 'item' => $transactionType]);
    }

    // 7. Passport Issue Places
    public function storePassportIssuePlace(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:passport_issue_places,name',
        ], [
            'name.unique' => 'ئەم شوێنە پێشتر تۆمارکراوە!'
        ]);

        $item = \App\Models\PassportIssuePlace::create($validated);
        Cache::forget('all_passport_issue_places');
        AuditLogger::log('زیادکردنی شوێنی دەرچوونی پاسپۆرت', \App\Models\PassportIssuePlace::class, $item->id, $item->name);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()->back()->with('success', "شوێنی دەرچوونی نوێ [{$item->name}] بە سەرکەوتوویی زیادکرا.");
    }

    public function updatePassportIssuePlace(Request $request, \App\Models\PassportIssuePlace $passportIssuePlace)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('passport_issue_places', 'name')->ignore($passportIssuePlace->id)],
        ], [
            'name.unique' => 'ئەم شوێنە پێشتر هەبووە!'
        ]);

        $passportIssuePlace->update($validated);
        Cache::forget('all_passport_issue_places');
        AuditLogger::log('دەستکاریکردنی شوێنی دەرچوونی پاسپۆرت', \App\Models\PassportIssuePlace::class, $passportIssuePlace->id, $passportIssuePlace->name);

        return response()->json(['success' => true, 'item' => $passportIssuePlace]);
    }

    public function destroyPassportIssuePlace(\App\Models\PassportIssuePlace $passportIssuePlace)
    {
        $name = $passportIssuePlace->name;
        $passportIssuePlace->delete();
        Cache::forget('all_passport_issue_places');
        AuditLogger::log('سڕینەوەی شوێنی دەرچوونی پاسپۆرت', \App\Models\PassportIssuePlace::class, $passportIssuePlace->id, $name);

        return response()->json(['success' => true]);
    }

    public function destroyCarMake(Request $request, CarMake $carMake)
    {
        $name = $carMake->name_kurdish;
        $id = $carMake->id;

        try {
            \App\Models\Transaction::where('car_make_id', $id)->update(['car_make_id' => null]);
        } catch (\Throwable $e) {}

        $carMake->delete();
        Cache::forget('all_car_makes');
        AuditLogger::log('سڕینەوەی مارکەی ئۆتۆمبێل', CarMake::class, $id, $name);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => "مارکەی [{$name}] بە سەرکەوتوویی سڕایەوە."]);
        }

        return redirect()->back()->with('success', "مارکەی [{$name}] بە سەرکەوتوویی سڕایەوە.");
    }

    public function destroyCarColor(Request $request, CarColor $carColor)
    {
        $name = $carColor->name_kurdish;
        $id = $carColor->id;

        try {
            \App\Models\Transaction::where('car_color_id', $id)->update(['car_color_id' => null]);
        } catch (\Throwable $e) {}

        $carColor->delete();
        Cache::forget('all_car_colors');
        AuditLogger::log('سڕینەوەی ڕەنگی ئۆتۆمبێل', CarColor::class, $id, $name);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => "ڕەنگی [{$name}] بە سەرکەوتوویی سڕایەوە."]);
        }

        return redirect()->back()->with('success', "ڕەنگی [{$name}] بە سەرکەوتوویی سڕایەوە.");
    }
}

