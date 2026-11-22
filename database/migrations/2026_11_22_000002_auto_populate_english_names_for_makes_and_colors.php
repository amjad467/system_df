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
        // 1. Common Car Makes Auto-Enrichment
        $makePatterns = [
            'لاندكروز' => 'TOYOTA LAND CRUISER',
            'راف' => 'TOYOTA RAV4',
            'كرولا' => 'TOYOTA COROLLA',
            'كامرى' => 'TOYOTA CAMRY',
            'برادو' => 'TOYOTA PRADO',
            'اظلون' => 'TOYOTA AVALON',
            'افالون' => 'TOYOTA AVALON',
            'يارس' => 'TOYOTA YARIS',
            'هايلوكس' => 'TOYOTA HILUX',
            'بيك اب دبل' => 'TOYOTA HILUX PICKUP',
            'تويوتا' => 'TOYOTA',
            'يوكون' => 'GMC YUKON',
            'اكسبلور' => 'FORD EXPLORER',
            'اكسبيديشن' => 'FORD EXPEDITION',
            'تاهؤ' => 'CHEVROLET TAHOE',
            'تاهو' => 'CHEVROLET TAHOE',
            'سوبربان' => 'CHEVROLET SUBURBAN',
            'امبالا' => 'CHEVROLET IMPALA',
            'ماليبو' => 'CHEVROLET MALIBU',
            'كروز' => 'CHEVROLET CRUZE',
            'اوبترا' => 'CHEVROLET OPTRA',
            'سلفرادو' => 'CHEVROLET SILVERADO',
            'كابتيفا' => 'CHEVROLET CAPTIVA',
            'ترافيرس' => 'CHEVROLET TRAVERSE',
            'شوفرليت' => 'CHEVROLET',
            'التيما' => 'NISSAN ALTIMA',
            'پاترول' => 'NISSAN PATROL',
            'باترول' => 'NISSAN PATROL',
            'سنترا' => 'NISSAN SENTRA',
            'روك' => 'NISSAN ROGUE',
            'روگ' => 'NISSAN ROGUE',
            'روج' => 'NISSAN ROGUE',
            'صنى' => 'NISSAN SUNNY',
            'سني' => 'NISSAN SUNNY',
            'تيدا' => 'NISSAN TIIDA',
            'مكسيما' => 'NISSAN MAXIMA',
            'ماكسيما' => 'NISSAN MAXIMA',
            'قشقاى' => 'NISSAN QASHQAI',
            'كيكس' => 'NISSAN KICKS',
            'باثفايندر' => 'NISSAN PATHFINDER',
            'ارمادا' => 'NISSAN ARMADA',
            'نافارا' => 'NISSAN NAVARA',
            'نيسان' => 'NISSAN',
            'جارجر' => 'DODGE CHARGER',
            'جالنجر' => 'DODGE CHALLENGER',
            'دورانجو' => 'DODGE DURANGO',
            'دوج' => 'DODGE',
            'لكزز' => 'LEXUS',
            'لكزس' => 'LEXUS',
            'شيروكى' => 'JEEP CHEROKEE',
            'شيروكي' => 'JEEP CHEROKEE',
            'رانكلر' => 'JEEP WRANGLER',
            'رانجلر' => 'JEEP WRANGLER',
            'كومباس' => 'JEEP COMPASS',
            'جيب' => 'JEEP',
            'باسات' => 'VOLKSWAGEN PASSAT',
            'كولف' => 'VOLKSWAGEN GOLF',
            'تيجوان' => 'VOLKSWAGEN TIGUAN',
            'جيتا' => 'VOLKSWAGEN JETTA',
            'طوارق' => 'VOLKSWAGEN TOUAREG',
            'فولكس' => 'VOLKSWAGEN',
            'bmw' => 'BMW',
            'مرسيدس' => 'MERCEDES-BENZ',
            'توكسون' => 'HYUNDAI TUCSON',
            'توسان' => 'HYUNDAI TUCSON',
            'سنتافى' => 'HYUNDAI SANTA FE',
            'سانتافي' => 'HYUNDAI SANTA FE',
            'النترا' => 'HYUNDAI ELANTRA',
            'سوناتا' => 'HYUNDAI SONATA',
            'اكسنت' => 'HYUNDAI ACCENT',
            'كونا' => 'HYUNDAI KONA',
            'كريتا' => 'HYUNDAI CRETA',
            'باليسيد' => 'HYUNDAI PALISADE',
            'هيونداى' => 'HYUNDAI',
            'هيونداي' => 'HYUNDAI',
            'موستانج' => 'FORD MUSTANG',
            'فيوجن' => 'FORD FUSION',
            'تورس' => 'FORD TAURUS',
            'فورد' => 'FORD',
            'اسكلاد' => 'CADILLAC ESCALADE',
            'اسكاليد' => 'CADILLAC ESCALADE',
            'كاديلاك' => 'CADILLAC',
            'رانج رور' => 'RANGE ROVER',
            'رێنج ڕۆڤەر' => 'RANGE ROVER',
            'لاند روفير' => 'LAND ROVER',
            'ديفندر' => 'LAND ROVER DEFENDER',
            'سورينتو' => 'KIA SORENTO',
            'سبورتاج' => 'KIA SPORTAGE',
            'سپورتاج' => 'KIA SPORTAGE',
            'سيراتو' => 'KIA CERATO',
            'فورتي' => 'KIA FORTE',
            'اوبتيما' => 'KIA OPTIMA',
            'كارنفال' => 'KIA CARNIVAL',
            'بيكانتو' => 'KIA PICANTO',
            'سيلتوس' => 'KIA SELTOS',
            'كادينزا' => 'KIA CADENZA',
            'تيلورايد' => 'KIA TELLURIDE',
            'كيا' => 'KIA',
            'شيري' => 'CHERY',
            'چێری' => 'CHERY',
            'جيتور' => 'JETOUR',
            'جێتۆر' => 'JETOUR',
            'MG' => 'MG',
            'ام جي' => 'MG',
            'جيلي' => 'GEELY',
            'هافال' => 'HAVAL',
            'شانجان' => 'CHANGAN',
            'سيفيك' => 'HONDA CIVIC',
            'اكورد' => 'HONDA ACCORD',
            'هوندا' => 'HONDA',
            'مازدا' => 'MAZDA',
            'باجيرو' => 'MITSUBISHI PAJERO',
            'اوتلاندر' => 'MITSUBISHI OUTLANDER',
            'لانسر' => 'MITSUBISHI LANCER',
            'ميتسوبيشي' => 'MITSUBISHI',
            'سوزوكي' => 'SUZUKI',
            'داستر' => 'RENAULT DUSTER',
            'رينو' => 'RENAULT',
            'بيجو' => 'PEUGEOT',
            'اودي' => 'AUDI',
            'بورش' => 'PORSCHE',
            'كرايسلر' => 'CHRYSLER',
            'كيا' => 'KIA',
        ];

        // Enrich makes where name_english is empty
        $makes = DB::table('car_makes')
            ->where(function($q) {
                $q->whereNull('name_english')->orWhere('name_english', '');
            })
            ->get(['id', 'name_kurdish']);

        foreach ($makes as $m) {
            $ku = $m->name_kurdish;
            foreach ($makePatterns as $pattern => $enName) {
                if (stripos($ku, $pattern) !== false) {
                    DB::table('car_makes')->where('id', $m->id)->update(['name_english' => $enName]);
                    break;
                }
            }
        }

        // 2. Common Car Colors Auto-Enrichment
        $colorPatterns = [
            'سپی' => 'WHITE',
            'سپى' => 'WHITE',
            'لؤلؤ' => 'PEARL WHITE',
            'سه‌ده‌ف' => 'PEARL WHITE',
            'ڕەش' => 'BLACK',
            'ره‌ش' => 'BLACK',
            'شین' => 'BLUE',
            'شين' => 'BLUE',
            'نیلی' => 'NAVY BLUE',
            'نيلى' => 'NAVY BLUE',
            'سماوی' => 'SKY BLUE',
            'سماوى' => 'SKY BLUE',
            'سمائى' => 'SKY BLUE',
            'ئاسمانی' => 'SKY BLUE',
            'ئاسمانى' => 'SKY BLUE',
            'پیرۆزە' => 'TURQUOISE',
            'شذر' => 'TURQUOISE',
            'سوور' => 'RED',
            'سور' => 'RED',
            'سەوز' => 'GREEN',
            'سه‌وز' => 'GREEN',
            'زەیتون' => 'OLIVE GREEN',
            'زه‌يتون' => 'OLIVE GREEN',
            'زەرد' => 'YELLOW',
            'زه‌رد' => 'YELLOW',
            'ئاڵتون' => 'GOLD',
            'زێڕ' => 'GOLD',
            'زير' => 'GOLD',
            'ذهب' => 'GOLD',
            'زیو' => 'SILVER',
            'زيو' => 'SILVER',
            'فض' => 'SILVER',
            'سلڤەر' => 'SILVER',
            'سلڤر' => 'SILVER',
            'سلفر' => 'SILVER',
            'ڕەساس' => 'GREY',
            'ره‌ساس' => 'GREY',
            'رصاص' => 'GREY',
            'رماد' => 'GREY',
            'سمنت' => 'CEMENT GREY',
            'خاك' => 'KHAKI',
            'خاک' => 'KHAKI',
            'بێج' => 'BEIGE',
            'بيج' => 'BEIGE',
            'تبن' => 'BEIGE',
            'قاوە' => 'BROWN',
            'قاوه‌' => 'BROWN',
            'جۆز' => 'BROWN',
            'جوز' => 'BROWN',
            'بن' => 'BROWN',
            'كاكاو' => 'BROWN',
            'مارۆن' => 'MAROON',
            'مارون' => 'MAROON',
            'عناب' => 'MAROON',
            'خه‌نه‌' => 'MAROON',
            'پرتەقاڵ' => 'ORANGE',
            'پرته‌قال' => 'ORANGE',
            'برتقال' => 'ORANGE',
            'مۆر' => 'PURPLE',
            'مور' => 'PURPLE',
            'بنفسج' => 'PURPLE',
            'باذنجان' => 'PURPLE',
            'پەمە' => 'PINK',
            'وه‌رد' => 'PINK',
            'نەوت' => 'PETROLEUM BLUE',
            'نه‌وت' => 'PETROLEUM BLUE',
            'نفط' => 'PETROLEUM BLUE',
            'برۆنز' => 'BRONZE',
            'برونز' => 'BRONZE',
            'نحاس' => 'COPPER',
            'قەیس' => 'PEACH',
            'قه‌يس' => 'PEACH',
            'حلیب' => 'CREAM',
            'حليب' => 'CREAM',
            'فیل' => 'ELEPHANT GREY',
            'فيل' => 'ELEPHANT GREY',
        ];

        $colors = DB::table('car_colors')
            ->where(function($q) {
                $q->whereNull('name_english')->orWhere('name_english', '');
            })
            ->get(['id', 'name_kurdish']);

        foreach ($colors as $c) {
            $ku = $c->name_kurdish;
            foreach ($colorPatterns as $pattern => $enName) {
                if (stripos($ku, $pattern) !== false) {
                    DB::table('car_colors')->where('id', $c->id)->update(['name_english' => $enName]);
                    break;
                }
            }
        }

        Cache::forget('all_car_makes');
        Cache::forget('all_car_colors');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
