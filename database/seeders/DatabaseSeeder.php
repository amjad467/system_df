<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Transaction;
use App\Services\FeeCalculatorService;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed Enterprise Permissions
        \App\Services\PermissionService::seedDefaults();

        // 1. Security Levels

        $securityLevels = ['کارمەند (Data Entry)', 'لێپرسراوی تەخمین (Inspector)', 'وردبینیکار (Auditor)', 'ژمێریار / وەسڵبڕ (Cashier)', 'بەشی دەفتەر (Booklet Section)', 'ئەرشیف و سەپمیت (Archiver)', 'بەڕێوەبەر (Admin)'];
        foreach ($securityLevels as $level) {
            DB::table('security_levels')->updateOrInsert(['name' => $level]);
        }

        // 2. Traffic Directorates
        $directorates = [
            ['name_kurdish' => 'بەڕێوەبەرایەتی هاتوچۆی پارێزگای هەولێر', 'name_english' => 'Erbil Traffic Directorate'],
            ['name_kurdish' => 'بەڕێوەبەرایەتی هاتوچۆی پارێزگای سلێمانی', 'name_english' => 'Sulaymaniyah Traffic Directorate'],
            ['name_kurdish' => 'بەڕێوەبەرایەتی هاتوچۆی پارێزگای دهۆک', 'name_english' => 'Duhok Traffic Directorate'],
            ['name_kurdish' => 'بەڕێوەبەرایەتی هاتوچۆی پارێزگای هەڵەبجە', 'name_english' => 'Halabja Traffic Directorate'],
        ];
        foreach ($directorates as $d) {
            DB::table('traffic_directorates')->updateOrInsert(['name_kurdish' => $d['name_kurdish']], $d);
        }

        // 3. Directors
        $directors = ['عەمید هێمن مامەند', 'عەقید ئارام عەلی', 'سەرکەوت قادر'];
        foreach ($directors as $dir) {
            DB::table('directors')->updateOrInsert(['name' => $dir]);
        }

        // 4. Plate Types
        $plateTypes = [
            ['name_kurdish' => 'تایبەت (خصوصي)', 'name_english' => 'Private'],
            ['name_kurdish' => 'بار (حمل)', 'name_english' => 'Cargo/Truck'],
            ['name_kurdish' => 'کرێ (أجرة)', 'name_english' => 'Taxi/Commercial'],
            ['name_kurdish' => 'پاسی نەفەر (حافلة)', 'name_english' => 'Passenger Bus'],
        ];
        foreach ($plateTypes as $pt) {
            DB::table('plate_types')->updateOrInsert(['name_kurdish' => $pt['name_kurdish']], $pt);
        }

        // 5. Car Makes
        $carMakes = [
            ['name_kurdish' => 'تۆیۆتا', 'name_english' => 'Toyota'],
            ['name_kurdish' => 'نیسان', 'name_english' => 'Nissan'],
            ['name_kurdish' => 'هیۆندای', 'name_english' => 'Hyundai'],
            ['name_kurdish' => 'کیا', 'name_english' => 'Kia'],
            ['name_kurdish' => 'میڕسیدس', 'name_english' => 'Mercedes-Benz'],
            ['name_kurdish' => 'بی ئێم دەبلیو', 'name_english' => 'BMW'],
            ['name_kurdish' => 'شۆفرلێت', 'name_english' => 'Chevrolet'],
            ['name_kurdish' => 'فۆرد', 'name_english' => 'Ford'],
            ['name_kurdish' => 'لێکسس', 'name_english' => 'Lexus'],
        ];
        foreach ($carMakes as $cm) {
            DB::table('car_makes')->updateOrInsert(['name_kurdish' => $cm['name_kurdish']], $cm);
        }

        // 6. Car Colors
        $carColors = [
            ['name_kurdish' => 'سپی', 'name_english' => 'White'],
            ['name_kurdish' => 'ڕەش', 'name_english' => 'Black'],
            ['name_kurdish' => 'نیلی / شین', 'name_english' => 'Blue'],
            ['name_kurdish' => 'زێڕی / زەرد', 'name_english' => 'Yellow / Gold'],
            ['name_kurdish' => 'ڕەساسی / زیوی', 'name_english' => 'Silver / Grey'],
            ['name_kurdish' => 'سوور', 'name_english' => 'Red'],
            ['name_kurdish' => 'سەوز', 'name_english' => 'Green'],
        ];
        foreach ($carColors as $cc) {
            DB::table('car_colors')->updateOrInsert(['name_kurdish' => $cc['name_kurdish']], $cc);
        }

        // 7. Transaction Types
        $txTypes = [
            ['id' => 1, 'name_kurdish' => 'دەرهێنانی دەفتەر (نوێ)', 'name_english' => 'New Booklet Issue'],
            ['id' => 2, 'name_kurdish' => 'تازەکردنەوەی دەفتەر', 'name_english' => 'Booklet Renewal'],
            ['id' => 3, 'name_kurdish' => 'ناوگۆڕینی دەفتەر', 'name_english' => 'Name Change'],
            ['id' => 4, 'name_kurdish' => 'دەفتەر گۆڕین', 'name_english' => 'Booklet Replacement'],
            ['id' => 5, 'name_kurdish' => 'پووچەڵکردنەوەی دەفتەر', 'name_english' => 'Booklet Cancellation'],
            ['id' => 6, 'name_kurdish' => 'بەڵێننامە', 'name_english' => 'Pledge Form'],
            ['id' => 7, 'name_kurdish' => 'ناوگۆڕین و دەفتەرگۆڕین', 'name_english' => 'Name & Booklet Change'],
            ['id' => 8, 'name_kurdish' => 'پشکنین', 'name_english' => 'Inspection'],
        ];
        foreach ($txTypes as $tt) {
            DB::table('transaction_types')->updateOrInsert(['id' => $tt['id']], $tt);
        }

        // 8. Employee Demo Users per Role
        $users = [
            ['name' => 'كارمەند ئازاد (داتا ئەنتەری)', 'user_login' => 'dataentry', 'email' => 'dataentry@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'data_entry', 'is_active' => true],
            ['name' => 'ئەندازیار کەمال (تەخمین)', 'user_login' => 'inspector', 'email' => 'inspector@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'inspector', 'is_active' => true],
            ['name' => 'وردبین هۆشیار (وردبینی)', 'user_login' => 'auditor', 'email' => 'auditor@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'auditor', 'is_active' => true],
            ['name' => 'ژمێریار دیار (وەسڵبڕ)', 'user_login' => 'cashier', 'email' => 'cashier@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'cashier', 'is_active' => true],
            ['name' => 'کارمەند سەربەست (بەشی دەفتەر)', 'user_login' => 'booklet', 'email' => 'booklet@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'booklet', 'is_active' => true],
            ['name' => 'کارمەند ئاراس (ئەرشیف و سەپمیت)', 'user_login' => 'archiver', 'email' => 'archiver@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'archiver', 'is_active' => true],
            ['name' => 'کارگێڕی گشتی (Admin)', 'user_login' => 'admin', 'email' => 'admin@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'admin', 'is_active' => true],
            ['name' => 'امجد صالح (Admin)', 'user_login' => 'amjad', 'email' => 'amjad@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'admin', 'is_active' => true],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['user_login' => $u['user_login']], $u);
        }

        // 9. Restore Full Baseline Dataset (All 84 Transactions, Car Makes, Sequences)
        \App\Services\DataRestoreService::restoreAll();
    }
}
