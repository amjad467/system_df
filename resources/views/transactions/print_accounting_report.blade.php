<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محاسبة ٦٦ - بەڕێوەبەرایەتی گومرگی سلێمانی</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #525659;
            margin: 0;
            padding: 20px;
            color: #000000;
        }

        /* Top Action Bar (hidden in print) */
        .toolbar {
            max-width: 297mm;
            margin: 0 auto 15px auto;
            background: #1e293b;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .toolbar-btn {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .toolbar-btn:hover {
            background: #0369a1;
        }
        .toolbar-btn.secondary {
            background: #475569;
        }
        .toolbar-btn.secondary:hover {
            background: #334155;
        }

        /* A4 Landscape Page */
        .page {
            width: 297mm;
            min-height: 210mm;
            padding: 12mm 15mm;
            margin: 0 auto 25px auto;
            background: #ffffff;
            box-shadow: 0 5px 25px rgba(0,0,0,0.25);
            position: relative;
            page-break-after: always;
        }

        /* Header of محاسبة ٦٦ */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: bold;
        }

        .header-left {
            text-align: right;
            width: 30%;
        }
        .header-title-code {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .header-datetime {
            font-size: 12px;
            font-family: monospace;
            font-weight: normal;
        }

        .header-center {
            text-align: center;
            width: 40%;
        }
        .header-dept-title {
            font-size: 17px;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .header-right {
            text-align: left;
            width: 30%;
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            gap: 15px;
        }

        /* Range boxes (لە 1 بۆ 50) */
        .range-box-container {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        .range-box {
            border: 1px solid #4b5563;
            padding: 2px 16px;
            font-family: monospace;
            font-size: 14px;
            font-weight: bold;
            min-width: 90px;
            text-align: center;
            background: #fafafa;
        }

        .page-counter {
            font-size: 13px;
            font-weight: bold;
            font-family: monospace;
            margin-left: 10px;
        }

        /* Table Design matching scan */
        .accounting-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            text-align: center;
            margin-top: 5px;
        }

        .accounting-table th, 
        .accounting-table td {
            border: 1px solid #000000;
            padding: 4px 3px;
            line-height: 1.2;
        }

        .accounting-table th {
            background-color: #f1f5f9;
            font-weight: 800;
            font-size: 11px;
            color: #000000;
        }

        .accounting-table td.col-name {
            text-align: right;
            padding-right: 6px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }

        .accounting-table td.font-num {
            font-family: monospace;
            font-size: 11px;
            font-weight: 600;
        }

        .accounting-table tr.cancelled-row {
            color: #374151;
        }

        .cancelled-flag {
            font-size: 10px;
            color: #000000;
            font-weight: bold;
            margin-left: 5px;
        }

        /* Summary Section (matching Image 3) */
        .summary-container {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            font-size: 12px;
            font-weight: bold;
        }

        .summary-header-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .summary-receipt-counts {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .count-box {
            border: 1px solid #000000;
            padding: 2px 14px;
            min-width: 60px;
            text-align: center;
            font-family: monospace;
            font-weight: bold;
        }

        .summary-grid {
            width: 380px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 8px;
            font-size: 12px;
        }
        .summary-item.highlight-gray {
            background-color: #e2e8f0;
            font-weight: 800;
            font-size: 13px;
        }

        .summary-item .amount {
            font-family: monospace;
            font-size: 13px;
        }

        .tafqeet-line {
            margin-top: 15px;
            font-size: 13px;
            font-weight: 800;
            text-align: right;
            width: 100%;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .toolbar {
                display: none !important;
            }

            .page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 8mm 10mm !important;
                width: 100% !important;
                min-height: auto !important;
                page-break-after: always;
            }

            @page {
                size: A4 landscape;
                margin: 5mm;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Toolbar (No Print) -->
    <div class="toolbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button onclick="window.print()" class="toolbar-btn">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                چاپکردن (Print)
            </button>
            <a href="{{ route('reports.accounting_66', request()->query()) }}" class="toolbar-btn secondary">
                گەڕانەوە بۆ ڕاپۆرت
            </a>
            <a href="{{ route('reports.accounting_66.export', request()->query()) }}" class="toolbar-btn secondary">
                داگرتنی Excel / CSV
            </a>
        </div>
        <div style="font-size: 13px;">
            کۆی پسولەکان: <strong>{{ $totalReceiptsCount }}</strong> | کۆی گشتی: <strong>{{ number_format($grandTotal) }} د.ع</strong>
        </div>
    </div>

    @php
        $rowsPerPage = 25;
        $chunks = $transactions->chunk($rowsPerPage);
        $totalChunks = $chunks->count();
        // Total pages includes the summary page if multi-page or if separate
        $totalPages = $totalChunks > 0 ? $totalChunks + 1 : 1;
        $globalIndex = 1;
    @endphp

    @if($chunks->isEmpty())
        <!-- Empty State Page -->
        <div class="page">
            <div class="report-header">
                <div class="header-left">
                    <div class="header-title-code">محاسبة ٦٦</div>
                    <div class="header-datetime">{{ now()->format('Y-m-d H:i:s') }}</div>
                </div>
                <div class="header-center">
                    <div class="header-dept-title">بەڕێوەبەرایەتی گومرگی سلێمانی</div>
                </div>
                <div class="header-right">
                    <div class="page-counter">1 - 1</div>
                </div>
            </div>
            <div style="text-align: center; padding: 100px 0; font-size: 16px; color: #64748b;">
                هیچ مامەڵە و پسوولەیەک بەم مەرجانە بوونی نییە.
            </div>
        </div>
    @else
        <!-- Data Table Pages (Chunked into 25 rows per page) -->
        @foreach($chunks as $pageIndex => $chunk)
            <div class="page">
                <!-- Header -->
                <div class="report-header">
                    <div class="header-left">
                        <div class="header-title-code">محاسبة ٦٦</div>
                        <div class="header-datetime">{{ now()->format('Y-m-d H:i:s') }}</div>
                    </div>

                    <div class="header-center">
                        <div class="header-dept-title">بەڕێوەبەرایەتی گومرگی سلێمانی</div>
                    </div>

                    <div class="header-right">
                        <div class="range-box-container">
                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span>لە</span>
                                    <div class="range-box">{{ $receiptFrom ?? 1 }}</div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span>بۆ</span>
                                    <div class="range-box">{{ $receiptTo ?? $totalReceiptsCount }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="page-counter">{{ ($pageIndex + 1) }} - {{ $totalPages }}</div>
                    </div>
                </div>

                <!-- Table -->
                <table class="accounting-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">ز</th>
                            <th style="width: 200px;">ناوی لێوەوەرگیراو</th>
                            <th style="width: 65px;">ژ. پسولە</th>
                            <th style="width: 100px;">جۆری مامەڵە</th>
                            <th style="width: 50px;">بڕی ساڵ</th>
                            <th style="width: 85px;">بەروار</th>
                            <th style="width: 75px;">رەسم</th>
                            <th style="width: 75px;">اجور</th>
                            <th style="width: 60px;">پول</th>
                            <th style="width: 60px;">فۆرم</th>
                            <th style="width: 85px;">کۆی گشتی</th>
                            <th style="width: 90px;">تێبینی</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chunk as $tx)
                            @php
                                $isCanc = $tx->is_cancelled || !empty($tx->cancelled_at);
                            @endphp
                            <tr class="{{ $isCanc ? 'cancelled-row' : '' }}">
                                <td class="font-num">{{ $globalIndex++ }}</td>
                                <td class="col-name">{{ $tx->visitor_name }}</td>
                                <td class="font-num">{{ $tx->receipt_37a_number ?? '-' }}</td>
                                <td>{{ $tx->transactionType->name_kurdish ?? '-' }}</td>
                                <td class="font-num">{{ $isCanc ? '0' : ($tx->num_years ?? 1) }}</td>
                                <td class="font-num">{{ $tx->display_date }}</td>
                                <td class="font-num">{{ $isCanc ? '0' : number_format($tx->pay_amount_years) }}</td>
                                <td class="font-num">{{ $isCanc ? '0' : number_format($tx->pay_inspection) }}</td>
                                <td class="font-num">{{ $isCanc ? '0' : number_format($tx->pay_stamp) }}</td>
                                <td class="font-num">{{ $isCanc ? '0' : number_format($tx->pay_form) }}</td>
                                <td class="font-num" style="font-weight: 800;">{{ $isCanc ? '0' : number_format($tx->total_pay) }}</td>
                                <td style="font-size: 10px; font-weight: bold;">
                                    @if($isCanc)
                                        پووچەڵکراوەتەوە
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <!-- Final Page: Official Accounting 66 Summary Page (Matching Image 3) -->
        <div class="page">
            <div class="report-header">
                <div class="header-left">
                    <div class="header-title-code">محاسبة ٦٦</div>
                    <div class="header-datetime">{{ now()->format('Y-m-d H:i:s') }}</div>
                </div>

                <div class="header-center">
                    <div class="header-dept-title">بەڕێوەبەرایەتی گومرگی سلێمانی</div>
                </div>

                <div class="header-right">
                    <div class="range-box-container">
                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span>لە</span>
                                <div class="range-box">{{ $receiptFrom ?? 1 }}</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span>بۆ</span>
                                <div class="range-box">{{ $receiptTo ?? $totalReceiptsCount }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="page-counter">{{ $totalPages }} - {{ $totalPages }}</div>
                </div>
            </div>

            <!-- Empty table header template line for visual alignment -->
            <table class="accounting-table" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th style="width: 30px;">ز</th>
                        <th style="width: 200px;">ناوی لێوەوەرگیراو</th>
                        <th style="width: 65px;">ژ. پسولە</th>
                        <th style="width: 100px;">جۆری مامەڵە</th>
                        <th style="width: 50px;">بڕی ساڵ</th>
                        <th style="width: 85px;">بەروار</th>
                        <th style="width: 75px;">رەسم</th>
                        <th style="width: 75px;">اجور</th>
                        <th style="width: 60px;">پول</th>
                        <th style="width: 60px;">فۆرم</th>
                        <th style="width: 85px;">کۆی گشتی</th>
                        <th style="width: 90px;">تێبینی</th>
                    </tr>
                </thead>
            </table>

            <!-- Summary block matching customer image 3 -->
            <div class="summary-container">
                <div class="summary-header-row">
                    <div>کۆی پسولەکان: <span style="font-family: monospace; font-size: 14px;">{{ $totalReceiptsCount }}</span></div>
                </div>

                <div class="summary-receipt-counts">
                    <span>پسولەی پوچەڵکراو</span>
                    <div class="count-box">{{ $cancelledCount }}</div>
                    <span>وە</span>
                    <div class="count-box">{{ $paidCount }}</div>
                    <span>پسولە پارەی وەرگیراوە</span>
                </div>

                <div class="summary-grid">
                    <div class="summary-item">
                        <span>کۆی رەسم :</span>
                        <span class="amount">{{ number_format($totalRasm) }}</span>
                    </div>
                    <div class="summary-item">
                        <span>کۆی پوول:</span>
                        <span class="amount">{{ number_format($totalPul) }}</span>
                    </div>
                    <div class="summary-item">
                        <span>کۆی فۆرم:</span>
                        <span class="amount">{{ number_format($totalForm) }}</span>
                    </div>
                    <div class="summary-item highlight-gray">
                        <span>کۆی داهات:</span>
                        <span class="amount">{{ number_format($totalDahat) }}</span>
                    </div>
                    <div class="summary-item">
                        <span>اجورکشف :</span>
                        <span class="amount">{{ number_format($totalInspection) }}</span>
                    </div>
                    <div class="summary-item highlight-gray">
                        <span>کۆی امانات:</span>
                        <span class="amount">{{ number_format($totalAmanat) }}</span>
                    </div>
                    <div class="summary-item highlight-gray" style="font-size: 14px;">
                        <span>کۆی گشتی بە ژمارە:</span>
                        <span class="amount">{{ number_format($grandTotal) }}</span>
                    </div>
                </div>

                <div class="tafqeet-line">
                    کۆی گشتی بە نووسین: 
                    <span style="font-weight: 900; text-decoration: underline;">{{ $grandTotalWords }}</span>
                </div>
            </div>
        </div>
    @endif

</body>
</html>
