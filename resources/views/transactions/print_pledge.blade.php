<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بەڵێننامە - {{ $transaction->barcode }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f8fafc;
            color: #000000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 6mm;
            }
            .print-card {
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 18px !important;
                margin: 0 auto !important;
                min-height: 285mm;
            }
        }

        .print-card {
            min-height: 285mm;
        }
    </style>
</head>
<body class="p-4">

    @php
        $tx = $transaction;
        $parent = $tx->parentTransaction;
        $plateNumber = $tx->plate_number ?: $parent?->plate_number;
        $carMakeName = $tx->carMake?->name_kurdish ?: ($parent?->carMake?->name_kurdish ?: ($tx->carMake?->name_english ?: $parent?->carMake?->name_english));
        $visitorName = $tx->visitor_name ?: $parent?->visitor_name;
        $passportNo = $tx->passport_number ?: ($parent?->passport_number ?: '');
        $passportIssueDate = $tx->passport_issue_date ?: ($parent?->passport_issue_date ?: null);
        $passportIssuePlace = $tx->passport_issue_place ?: ($parent?->passport_issue_place ?: '');
        $address = $tx->address ?: ($parent?->address ?: '');
        $phoneNumber = $tx->phone_number ?: ($parent?->phone_number ?: '');
        $directorName = $tx->director?->name ?: ($parent?->director?->name ?: 'قادر پیرۆت قادر');
        $txDate = $tx->transaction_date ?: ($parent?->transaction_date ?: now());
    @endphp

    <!-- Top Studio Toolbar (Hidden during printing) -->
    <x-print-layout-studio 
        docId="letter_pledge" 
        targetId="pledgePrintArea" 
        title="بەڵێننامە • {{ $transaction->barcode }}" 
        initialTop="0" 
        initialRight="0" 
        initialZoom="100" />

    <!-- MAIN REPORT PRINT CARD (Exact match to official template) -->
    <div id="pledgePrintArea" class="print-card bg-white border-2 border-black p-8 rounded-none shadow-xl max-w-4xl mx-auto space-y-4 text-black mt-3 relative flex flex-col justify-between">

        <!-- Subtle Background Watermark of Kurdistan Emblem -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.07] z-0">
            <img src="{{ asset('images/krg_logo.png') }}" class="w-96 h-96 object-contain" alt="KRG Watermark">
        </div>

        <div class="relative z-10 space-y-4">
            <!-- Emblem & Header Grid -->
            <div class="border-2 border-black p-3.5 rounded-none">
                <div class="grid grid-cols-3 items-center text-center">
                    
                    <!-- Right Title: Kurdish -->
                    <div class="text-right text-[13px] font-bold leading-tight">
                        <p class="font-black text-sm">حکومەتی هەرێمی کوردستان \عێراق</p>
                        <p>سەرۆکایەتی ئەنجومەنی وەزیران</p>
                        <p>وەزارەتی دارایی و ئابوری</p>
                        <p>بەڕێوەبەرایەتی گشتی گومرگ</p>
                        <p class="font-black">بەڕێوەبەرایەتی گومرگی سلێمانی</p>
                        <p>هۆبەی ئۆتۆمبێل</p>
                    </div>

                    <!-- Center Emblem & English Title -->
                    <div class="flex flex-col items-center justify-center space-y-1">
                        <div class="w-16 h-16 flex items-center justify-center">
                            <img src="{{ asset('images/krg_logo.png') }}" 
                                 alt="Kurdish Region Emblem" 
                                 class="max-h-16 object-contain">
                        </div>
                        <div class="text-[10px] font-bold text-center leading-tight">
                            <p class="font-extrabold text-[11px]">Kurdish Region</p>
                            <p>Presidency council of ministries</p>
                            <p>ministry of finance economy</p>
                            <p>General directorate customs</p>
                            <p class="font-bold">Department of (Automobiles)</p>
                        </div>
                    </div>

                    <!-- Left Title: Arabic -->
                    <div class="text-left text-[13px] font-bold leading-tight" dir="rtl">
                        <p class="font-black text-sm">حکومة اقلیم کوردستان \العراق</p>
                        <p>رئاسة مجلس الوزراء</p>
                        <p>وزارة المالية والاقتصاد</p>
                        <p>المدیریة العامة للجمارك</p>
                        <p class="font-black">مدیریة كمرك السلیمانیة</p>
                        <p>قسم السیارات</p>
                    </div>

                </div>
            </div>

            <!-- Date & Number Bar (Dashed Border) -->
            <div class="border-y-2 border-dashed border-black py-2 px-2 flex items-center justify-between text-sm font-bold">
                <!-- Right: Number -->
                <div>
                    <span>ژمارە: </span>
                    <span class="font-mono text-base tracking-widest text-gray-500">..............................</span>
                </div>
                <!-- Left: Date (Year & Month only) -->
                <div>
                    <span>ڕێکەوتی: </span>
                    <span dir="ltr" class="inline-block font-mono text-base font-bold">{{ $txDate ? \Carbon\Carbon::parse($txDate)->format('Y/m') : date('Y/m') }}/ &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span>
                </div>
            </div>

            <!-- Title & Subject (Centered) -->
            <div class="text-center space-y-2 pt-2">
                <h2 class="text-2xl font-black tracking-wide">
                    بەڵێننامە
                </h2>
                <h3 class="text-lg font-black underline underline-offset-4">
                    بابەت /دەفتەری ڕێپێدانی بردنه ده‌ره‌وه‌ی ئۆتۆمبێل
                </h3>
            </div>

            <!-- Official Pledge Body Text -->
            <div class="text-base font-bold leading-loose px-2 pt-2 text-justify">
                <p>
                    من کە لەخوارەوە ناوم هاتووە و واژۆم کردووە بەڵێن دەدەم کە ئۆتۆمبێلی ژمارە ( <span class="font-black text-lg">{{ $plateNumber }}</span> ) جۆری ( <span class="font-black text-lg">{{ $carMakeName }}</span> ) بگەڕێنمەوە لەماوەی دیاری کراویدا وەخۆم بەرپرسم لە هەرکێشەیەک کە رووبدات وەخۆم خاوەنیم مافی هیچ کەسێکی بەسەرەوە نییە بەپێچەوانەوە خۆم بەرپرس دەبم بەڕامبەر بەیاسا و ڕێنماییەکان .
                </p>
            </div>

            <!-- Citizen Identification Block -->
            <div class="pt-6 px-2 space-y-3 font-bold text-sm leading-relaxed">
                <p class="font-black text-base">واژۆی بەڵێندەر:</p>
                
                <p class="text-base">
                    ناوی سیانی ( <span class="font-black text-lg">{{ $visitorName }}</span> )
                </p>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                    <div>
                        ژمارەی ناسنامە ( <span class="font-black font-mono text-base">{{ $passportNo ?: '....................' }}</span> )
                    </div>
                    <div>
                        ڕێکەوتی دەرچوونی: ( <span class="font-black font-mono text-base">{{ $passportIssueDate ? \Carbon\Carbon::parse($passportIssueDate)->format('Y / m / d') : '.... / .. / ..' }}</span> )
                    </div>
                    <div>
                        دەرچووە لە ( <span class="font-black text-base">{{ $passportIssuePlace ?: '....................' }}</span> )
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-x-8 gap-y-2 text-sm pt-1">
                    <div>
                        ناونیشان: ( <span class="font-black text-base">{{ $address ?: '........................................................' }}</span> )
                    </div>
                    <div>
                        مۆبایل ( <span class="font-black font-mono text-base">{{ $phoneNumber ?: '....................' }}</span> )
                    </div>
                </div>
            </div>

            <!-- Legal Officer Attestation Block -->
            <div class="pt-6 px-2 text-justify text-sm font-bold leading-loose border-t border-gray-300">
                <p>
                    ئامادەبوو لەپێشم بەڵێندەر ( <span class="font-black text-base">{{ $visitorName }}</span> ) دوای دڵنیابوون لەناسنامەکەی و دەقی بەڵێننامەکەم بۆ خوێندەوە و دانی بەناوەڕۆکەکەی نا دوای ئەوە لەبەردەمم واژۆی کرد منیش پەسەندم کرد لە ڕێکەوتی ( <span dir="ltr" class="inline-block font-black font-mono text-base">{{ $txDate ? \Carbon\Carbon::parse($txDate)->format('Y/m') : date('Y/m') }}/ &nbsp; &nbsp; &nbsp; &nbsp;</span> )
                </p>
            </div>

            <!-- Director Signature & Barcode Grid -->
            <div class="pt-8 pb-2 px-2 flex items-end justify-between">
                
                <!-- Clean Official Barcode (Right / Center) -->
                <div class="text-center space-y-1">
                    <svg id="pledgeBarcodeSvg" class="max-h-12"></svg>
                    <div class="font-mono text-xs font-bold text-center">
                        * {{ $transaction->barcode }} *
                    </div>
                </div>

                <!-- Director Block (Left of the page) -->
                <div class="text-center font-black space-y-1">
                    <p class="text-lg">{{ $directorName }}</p>
                    <p class="text-sm">بەڕێوەبەری گومرگی سلێمانی</p>
                    <p class="pt-1">
                        <span dir="ltr" class="inline-block font-mono text-sm font-bold">{{ $txDate ? \Carbon\Carbon::parse($txDate)->format('Y/m') : date('Y/m') }}/ &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span>
                    </p>
                </div>

            </div>
        </div>

        <!-- Footer Address & Date -->
        <div class="pt-3 border-t-2 border-black flex items-center justify-between text-xs font-bold text-gray-800 relative z-10">
            <div class="border border-gray-400 px-3 py-0.5 text-center font-mono text-[11px]">
                {{ date('Y-m-d') }}
            </div>
            <div>
                حکومەتی هەرێمی کوردستان\ ناونیشان سلێمانی تەنیشت باخی بەختیاری
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            JsBarcode("#pledgeBarcodeSvg", "*{{ $transaction->barcode }}*", {
                format: "CODE128",
                lineColor: "#000",
                width: 1.8,
                height: 38,
                displayValue: false
            });
        });
    </script>
</body>
</html>
