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
        AuditLogger::log('دەستکاریکردنی ناوی بەڕێوەبەرایەتی', TrafficDirectorate::class, $directorate->id, $directorate->name_kurdish);

        return response()->json(['success' => true, 'item' => $directorate]);
    }

    // 2. Directors
    public function storeDirector(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:directors,name',
        ], [
            'name.unique' => 'ئەم ناوەی بەڕێوەبەر پێشتر تۆمارکراوە!'
        ]);

        $item = Director::create($validated);
        AuditLogger::log('زیادکردنی بەڕێوەبەری نوێ', Director::class, $item->id, $item->name);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function updateDirector(Request $request, Director $director)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('directors', 'name')->ignore($director->id)],
        ], [
            'name.unique' => 'ئەم ناوەی بەڕێوەبەر پێشتر هەبووە!'
        ]);

        $director->update($validated);
        AuditLogger::log('دەستکاریکردنی ناوی بەڕێوەبەر', Director::class, $director->id, $director->name);

        return response()->json(['success' => true, 'item' => $director]);
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
        AuditLogger::log('زیادکردنی مارکەی ئۆتۆمبێلی نوێ', CarMake::class, $item->id, "{$item->name_kurdish} - {$item->name_english}");

        return response()->json(['success' => true, 'item' => $item]);
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
        AuditLogger::log('دەستکاریکردنی ناوی مارکەی ئۆتۆمبێل', CarMake::class, $carMake->id, "{$carMake->name_kurdish} - {$carMake->name_english}");

        return response()->json(['success' => true, 'item' => $carMake]);
    }

    // 5. Car Colors
    public function storeCarColor(Request $request)
    {
        $validated = $request->validate([
            'name_kurdish' => 'required|string|max:255|unique:car_colors,name_kurdish',
            'name_english' => 'required|string|max:255|unique:car_colors,name_english',
        ], [
            'name_kurdish.unique' => 'ئەم ڕەنگە کوردییە پێشتر تۆمارکراوە!',
            'name_english.unique' => 'ئەم ڕەنگە ئینگلیزییە پێشتر تۆمارکراوە!'
        ]);

        $item = CarColor::create($validated);
        AuditLogger::log('زیادکردنی ڕەنگی نوێ', CarColor::class, $item->id, "{$item->name_kurdish} - {$item->name_english}");

        return response()->json(['success' => true, 'item' => $item]);
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
        AuditLogger::log('دەستکاریکردنی ناوی جۆری مامەڵە', TransactionType::class, $transactionType->id, $transactionType->name_kurdish);

        return response()->json(['success' => true, 'item' => $transactionType]);
    }
}

