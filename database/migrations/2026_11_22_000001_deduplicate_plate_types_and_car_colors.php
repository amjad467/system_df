<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Deduplicate Plate Types
        $pts = DB::table('plate_types')->orderBy('id', 'asc')->get();
        $seen = [];
        $deleteIds = [];
        foreach ($pts as $pt) {
            $cleanName = trim(preg_replace('/\s*\(.*?\)/', '', $pt->name_kurdish));
            if (isset($seen[$cleanName])) {
                $canonicalId = $seen[$cleanName];
                DB::table('transactions')->where('plate_type_id', $pt->id)->update(['plate_type_id' => $canonicalId]);
                $deleteIds[] = $pt->id;
            } else {
                $seen[$cleanName] = $pt->id;
                DB::table('plate_types')->where('id', $pt->id)->update(['name_kurdish' => $cleanName]);
            }
        }
        if (!empty($deleteIds)) {
            DB::table('plate_types')->whereIn('id', $deleteIds)->delete();
        }

        // 2. Deduplicate Car Colors
        $colorMerges = [
            8 => 1,    // سپى -> سپی
            133 => 2,  // ره‌ش -> ڕەش
            13 => 3,   // نیلی -> شین / نیلی
            18 => 3,   // نيلى -> شین / نیلی
            103 => 3,  // شين -> شین / نیلی
            172 => 3,  // شین -> شین / نیلی
            29 => 4,   // زەرد -> زەرد / زێڕی
            10 => 7,   // سه‌وز -> سەوز
            84 => 23,  // بيجى -> بێجی
            145 => 26, // جوزى -> جۆزى
            162 => 28, // قاوه‌ى -> قاوه‌یی
            161 => 36, // مور -> مۆر
            176 => 20, // پرته‌قالى -> پرته‌قاڵی
            151 => 70, // نه‌وتی + سقف ره‌ش -> نه‌وتى + سقف ره‌ش
            175 => 164,// ره‌صاصى دوكه‌لى -> ره‌صاصی دوكه‌لى
            168 => 54, // شین+سقف ره‌ش -> شين * سقف ره‌ش
            100 => 85, // رمادى سقف ره‌ش -> رمادى+سقف ره‌ش
            147 => 144,// سپى + سقف متحرك -> سپى+سقف متحرك
            152 => 167,// سلڤر -> سلفر
        ];

        $standardNames = [
            1 => ['name_kurdish' => 'سپی', 'name_english' => 'White'],
            2 => ['name_kurdish' => 'ڕەش', 'name_english' => 'Black'],
            3 => ['name_kurdish' => 'شین / نیلی', 'name_english' => 'Blue'],
            4 => ['name_kurdish' => 'زەرد / زێڕی', 'name_english' => 'Yellow / Gold'],
            5 => ['name_kurdish' => 'زیوی / ڕەساسی', 'name_english' => 'Silver / Grey'],
            6 => ['name_kurdish' => 'سوور', 'name_english' => 'Red'],
            7 => ['name_kurdish' => 'سەوز', 'name_english' => 'Green'],
            20 => ['name_kurdish' => 'پرتەقاڵی', 'name_english' => 'Orange'],
            23 => ['name_kurdish' => 'بێجی', 'name_english' => 'Beige'],
            25 => ['name_kurdish' => 'مارۆنی', 'name_english' => 'Maroon'],
            26 => ['name_kurdish' => 'جۆزی', 'name_english' => 'Nut Brown'],
            28 => ['name_kurdish' => 'قاوەیی', 'name_english' => 'Brown'],
            36 => ['name_kurdish' => 'مۆر', 'name_english' => 'Purple'],
            167 => ['name_kurdish' => 'سلڤەر / زیوی', 'name_english' => 'Silver'],
        ];

        foreach ($colorMerges as $fromId => $toId) {
            DB::table('transactions')->where('car_color_id', $fromId)->update(['car_color_id' => $toId]);
            DB::table('car_colors')->where('id', $fromId)->delete();
        }

        foreach ($standardNames as $id => $data) {
            DB::table('car_colors')->where('id', $id)->update($data);
        }

        Cache::forget('all_plate_types');
        Cache::forget('all_car_colors');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
