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

        $securityLevels = ['کارمەند (Data Entry)', 'لێپرسراوی تەخمین (Inspector)', 'وردبینیکار (Auditor)', 'ژمێریار / وەسڵبڕ (Cashier)', 'بەشی دەفتەر (Booklet Section)', 'بەڕێوەبەر (Admin)'];
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
            ['name' => 'كارمەند ئازاد (داتا ئەنتەری)', 'user_login' => 'dataentry', 'email' => 'dataentry@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'data_entry'],
            ['name' => 'ئەندازیار کەمال (تەخمین)', 'user_login' => 'inspector', 'email' => 'inspector@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'inspector'],
            ['name' => 'وردبین هۆشیار (وردبینی)', 'user_login' => 'auditor', 'email' => 'auditor@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'auditor'],
            ['name' => 'ژمێریار دیار (وەسڵبڕ)', 'user_login' => 'cashier', 'email' => 'cashier@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'cashier'],
            ['name' => 'کارمەند سەربەست (بەشی دەفتەر)', 'user_login' => 'booklet', 'email' => 'booklet@customs.gov.krd', 'password' => Hash::make('123456'), 'role' => 'booklet'],
            ['name' => 'کارگێڕی گشتی (Admin)', 'user_login' => 'admin', 'email' => 'admin@customs.gov.krd', 'password' => Hash::make('admin123'), 'role' => 'admin'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }

        // 9. Sample Transactions
        $fees1 = FeeCalculatorService::calculate(1, 2, false);
        Transaction::firstOrCreate(
            ['barcode' => '2610040001'],
            [
                'traffic_directorate_id' => 1,
                'director_id' => 1,
                'transaction_type_id' => 1,
                'plate_type_id' => 1,
                'car_make_id' => 1,
                'car_color_id' => 1,
                'visitor_name' => 'ڕێبین ئازاد عومەر',
                'visitor_name_eng' => 'REBIN AZAD OMER',
                'second_driver_name' => 'کاوان ئازاد عومەر',
                'second_driver_name_eng' => 'KAWAN AZAD OMER',
                'plate_number' => '22 A 88412',
                'model_year' => '2023',
                'chassis_number' => 'JTEBU5JR8K5019823',
                'piston_count' => 6,
                'salana_number' => 'SAL-998822',
                'transaction_date' => now(),
                'notes' => 'مامەڵەی تاقیکردنەوەی سەرەتایی',
                'num_years' => 2,
                'receipt_37a_number' => '37A-445511',
                'pay_amount_years' => $fees1['pay_amount_years'],
                'pay_stamp' => $fees1['pay_stamp'],
                'pay_form' => $fees1['pay_form'],
                'pay_fine' => $fees1['pay_fine'],
                'pay_inspection' => $fees1['pay_inspection'],
                'total_pay' => $fees1['total_pay'],
                'start_date' => $fees1['start_date'],
                'end_date' => $fees1['end_date'],
                'zedabar' => 'باشە',
                'zedabar_eng' => 'good',
                'kamukurty' => 'باشە',
                'kamukurty_eng' => 'good',
                'bary_gshty' => 'باشە',
                'bary_gshty_eng' => 'good',
                'user_input' => 'كارمەند ئازاد (داتا ئەنتەری)',
                'is_inspected' => true,
                'inspected_by' => 'ئەندازیار کەمال (تەخمین)',
                'inspected_at' => now(),
                'is_audited' => true,
                'audited_by' => 'وردبین هۆشیار (وردبینی)',
                'audited_at' => now(),
                'is_paid' => true,
                'paid_by' => 'ژمێریار دیار (وەسڵبڕ)',
                'paid_at' => now(),
                'is_booklet_completed' => true,
                'booklet_number' => 'DF-887711',
                'booklet_by' => 'کارمەند سەربەست (بەشی دەفتەر)',
                'booklet_completed_at' => now(),
                'is_pledge_completed' => true,
                'is_printed' => true,
                'is_submitted' => true,
                'submitted_by' => 'کارگێڕی گشتی (Admin)',
                'submitted_at' => now(),
            ]
        );

        $fees2 = FeeCalculatorService::calculate(2, 1, false);
        Transaction::firstOrCreate(
            ['barcode' => '2610040002'],
            [
                'traffic_directorate_id' => 2,
                'director_id' => 2,
                'transaction_type_id' => 2,
                'plate_type_id' => 3,
                'car_make_id' => 3,
                'car_color_id' => 2,
                'visitor_name' => 'هێمن قادر سلێمان',
                'visitor_name_eng' => 'HEMN QADIR SULAIMAN',
                'plate_number' => '21 B 55190',
                'model_year' => '2021',
                'chassis_number' => 'KMHD841ED5FU01928',
                'piston_count' => 4,
                'salana_number' => 'SAL-112233',
                'transaction_date' => now(),
                'num_years' => 1,
                'pay_amount_years' => $fees2['pay_amount_years'],
                'pay_stamp' => $fees2['pay_stamp'],
                'pay_form' => $fees2['pay_form'],
                'pay_fine' => $fees2['pay_fine'],
                'pay_inspection' => $fees2['pay_inspection'],
                'total_pay' => $fees2['total_pay'],
                'start_date' => $fees2['start_date'],
                'end_date' => $fees2['end_date'],
                'zedabar' => 'باشە',
                'zedabar_eng' => 'good',
                'kamukurty' => 'باشە',
                'kamukurty_eng' => 'good',
                'bary_gshty' => 'باشە',
                'bary_gshty_eng' => 'good',
                'user_input' => 'كارمەند ئازاد (داتا ئەنتەری)',
                'is_inspected' => true,
                'inspected_by' => 'ئەندازیار کەمال (تەخمین)',
                'inspected_at' => now(),
                'is_audited' => false,
            ]
        );
    }

}
