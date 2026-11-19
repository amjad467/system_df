<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ڕاپۆرتی سەرپێچیکارانی دەفتەری گومرگی - {{ $batchName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            margin: 0;
            padding: 20px;
            color: #0f172a;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: #f1f5f9;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
        }

        .btn {
            padding: 8px 18px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-print { background: #0284c7; color: white; }
        .btn-back { background: #64748b; color: white; }

        .header-box {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-title {
            font-size: 17px;
            font-weight: 900;
            margin: 3px 0;
        }
        .header-sub {
            font-size: 12px;
            color: #475569;
            margin: 2px 0;
            font-weight: 600;
        }

        .report-badge {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
            display: inline-block;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }

        th {
            background: #0f172a;
            color: #fff;
            padding: 8px 6px;
            border: 1px solid #0f172a;
            font-weight: 800;
            text-align: center;
        }

        td {
            padding: 7px 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .footer-signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
            font-size: 12px;
            font-weight: 800;
        }
        .sig-block {
            text-align: center;
            width: 200px;
        }
        .sig-line {
            margin-top: 45px;
            border-top: 1px dashed #0f172a;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page {
                size: A4 landscape;
                margin: 12mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <div>
            <strong>ڕاپۆرتی سەرپێچیکاران و بەدواداچوونی دەفتەرە بەسەرچووەکان</strong>
            <span style="color:#64748b; font-size:12px; margin-right:10px;">کۆدی وەجبە: {{ $batchCode }}</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-print">
                <i class="fa-solid fa-print"></i> چاپکردنی فەرمی
            </button>
            <a href="{{ route('booklet_notices.index', ['batch' => $batchCode]) }}" class="btn btn-back">
                <i class="fa-solid fa-arrow-right"></i> گەڕانەوە
            </a>
        </div>
    </div>

    <!-- Official Header -->
    <div class="header-box">
        <div style="text-align: right; width: 250px;">
            <div style="font-weight: 800; font-size: 13px;">حکومەتی هەرێمی کوردستان</div>
            <div style="font-size: 11px; color: #475569;">وەزارەتی دارایی و ئابووری</div>
            <div style="font-size: 11px; color: #475569;">بەڕێوەبەرایەتی گشتی گومرگ</div>
            <div style="font-size: 12px; font-weight: 800; color: #0284c7;">بەڕێوەبەرایەتی گومرگی سلێمانی</div>
        </div>

        <div>
            <div class="header-title">ڕاپۆرتی سەرپێچیکارانی دەفتەری گومرگی (ئەوانەی ئاگادارکراونەتەوە و سەردانیان نەکردووە)</div>
            <div class="header-sub">دەفتەری ئۆتۆمبێلی گەشتیاری - بەشی دەفتەر و ئەرشیف</div>
            <div class="report-badge">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $batchName }} - (کۆی سەرپێچیکاران: {{ $noShows->count() }} هاووڵاتی)
            </div>
        </div>

        <div style="text-align: left; width: 250px; font-size: 11px; font-weight: 700;">
            <div>بەرواری دەرهێنان: {{ now()->format('Y-m-d') }}</div>
            <div>بەرواری ناردنی نامە: {{ $sentAt ? \Carbon\Carbon::parse($sentAt)->format('Y-m-d') : '-' }}</div>
            <div style="font-family: monospace;">کۆد: {{ $batchCode }}</div>
        </div>
    </div>

    <!-- No-Shows Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="text-align: right; padding-right: 8px;">ناوی هاووڵاتی</th>
                <th style="width: 110px;">ژمارەی مۆبایل</th>
                <th style="width: 100px;">ژمارەی تابلۆ</th>
                <th style="width: 80px;">ژمارەی دەفتەر</th>
                <th>جۆری ئۆتۆمبێل / مۆدێل</th>
                <th style="width: 90px;">بەسەرچوون</th>
                <th style="width: 80px;">ماوەی تێپەڕبوو</th>
                <th style="width: 80px;">دۆخی ئێستا</th>
                <th style="width: 120px;">ئیجرائاتی یاسایی / تێبینی</th>
            </tr>
        </thead>
        <tbody>
            @forelse($noShows as $index => $notice)
                @php
                    $isPast = $notice->end_date && $notice->end_date->isPast();
                    $daysDiff = $notice->end_date ? abs(now()->startOfDay()->diffInDays($notice->end_date->startOfDay(), false)) : 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: right; padding-right: 8px; font-weight: 800;">
                        {{ $notice->visitor_name }}
                    </td>
                    <td style="direction: ltr; font-family: monospace; font-weight: 700;">
                        {{ $notice->phone_number ?: '-' }}
                    </td>
                    <td style="font-weight: 800; font-family: monospace;">
                        {{ $notice->plate_number }}
                    </td>
                    <td style="font-family: monospace; font-weight: 800; color: #b45309;">
                        {{ $notice->booklet_number ?: '-' }}
                    </td>
                    <td>
                        {{ $notice->transaction?->carMake?->name_kurdish ?? '-' }}
                        @if($notice->transaction?->model_year)
                            ({{ $notice->transaction->model_year }})
                        @endif
                    </td>
                    <td style="font-family: monospace; font-weight: 700; color: #dc2626;">
                        {{ $notice->end_date ? $notice->end_date->format('Y-m-d') : '-' }}
                    </td>
                    <td style="font-weight: 800; color: #dc2626;">
                        {{ $daysDiff }} ڕۆژ
                    </td>
                    <td style="color: #b91c1c; font-weight: 800;">
                        نەهاتووە
                    </td>
                    <td style="color: #64748b; font-size: 10px;">
                        ئاگادارکراوەتەوە
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="padding: 25px; color: #15803d; font-weight: 800; font-size: 13px;">
                        <i class="fa-solid fa-circle-check"></i> پیرۆزە! سەرجەم هاووڵاتیانی ئەم وەجبەیە سەردانیان کردووە و هیچ سەرپێچیکارێک نەماوەتەوە.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Footer -->
    <div class="footer-signatures">
        <div class="sig-block">
            ئامادەکاری ڕاپۆرت
            <div class="sig-line">{{ auth()->user()?->name ?? 'کارمەند' }}</div>
        </div>
        <div class="sig-block">
            بەرپرسی بەشی دەفتەر و ئەرشیف
            <div class="sig-line">واژوو و بەروار</div>
        </div>
        <div class="sig-block">
            بەڕێوەبەری گومرگی سلێمانی
            <div class="sig-line">پەسەندکردن و مۆر</div>
        </div>
    </div>

</body>
</html>
