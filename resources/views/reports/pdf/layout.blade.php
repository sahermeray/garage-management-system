<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'تقرير' }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path("fonts/DejaVuSans.ttf") }}') format('truetype');
            font-weight: normal;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 11px;
            direction: rtl;
            unicode-bidi: embed;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .page {
            padding: 20px 25px;
        }

        /* Header */
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .institution-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
        }

        .report-meta {
            text-align: left;
            font-size: 10px;
            color: #6b7280;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
            color: #111827;
        }

        .filter-info {
            font-size: 10px;
            color: #374151;
            margin-top: 4px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 10px;
        }

        thead tr {
            background-color: #1e40af;
            color: #fff;
        }

        thead th {
            padding: 7px 8px;
            text-align: right;
            font-weight: bold;
            border: 1px solid #1e3a8a;
        }

        tbody tr:nth-child(even) {
            background-color: #f1f5f9;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            text-align: right;
        }

        /* Summary row */
        .total-row td {
            font-weight: bold;
            background-color: #dbeafe;
            border-top: 2px solid #2563eb;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #9ca3af;
            font-size: 12px;
        }

        /* Footer */
        .footer {
            margin-top: 15px;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            font-size: 9px;
            color: #9ca3af;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
<div class="page">
    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            <div>
                <div class="institution-name">{{ $institutionName ?? 'المرآب' }}</div>
                <div class="report-title">{{ $title ?? 'تقرير' }}</div>
                @if (!empty($vehicleLabel))
                    <div class="filter-info">المركبة: {{ $vehicleLabel }}</div>
                @endif
                @if (!empty($dateFrom) && !empty($dateTo))
                    <div class="filter-info">الفترة: من {{ $dateFrom }} إلى {{ $dateTo }}</div>
                @endif
            </div>
            <div class="report-meta">
                <div>تاريخ الطباعة: {{ now()->format('Y-m-d') }}</div>
                <div>الوقت: {{ now()->format('H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    @if (empty($data))
        <div class="empty-state">لا توجد بيانات للعرض</div>
    @else
        @php
            $columns = array_keys(reset($data));
            $columnLabels = [
                'institutional_number' => 'الرقم المؤسسي',
                'category' => 'الفئة',
                'manufacturer' => 'الصانع',
                'model' => 'الموديل',
                'year' => 'سنة الصنع',
                'plate_number' => 'رقم اللوحة',
                'color' => 'اللون',
                'current_odometer' => 'العداد الحالي',
                'status' => 'الحالة',
                'trip_number' => 'رقم الرحلة',
                'trip_date' => 'تاريخ الرحلة',
                'driver' => 'السائق',
                'destination' => 'الوجهة',
                'purpose' => 'الغرض',
                'odometer_start' => 'العداد عند المغادرة',
                'odometer_end' => 'العداد عند العودة',
                'distance' => 'المسافة (كم)',
                'vehicle' => 'المركبة',
                'repair_date' => 'تاريخ الإصلاح',
                'odometer' => 'قراءة العداد',
                'problem' => 'المشكلة',
                'description' => 'الوصف',
                'location' => 'موقع الإصلاح',
                'workshop_name' => 'اسم الورشة',
                'invoice_number' => 'رقم الفاتورة',
                'total_cost' => 'التكلفة الإجمالية',
                'type' => 'النوع',
                'quantity' => 'الكمية',
                'unit_price' => 'سعر الوحدة',
                'total' => 'المجموع',
                'date' => 'التاريخ',
                'liters' => 'الكمية (لتر)',
                'price_per_liter' => 'سعر اللتر',
                'notes' => 'ملاحظات',
                'repair_total' => 'مصاريف الإصلاح',
                'fuel_total' => 'مصاريف الوقود',
                'grand_total' => 'الإجمالي الكلي',
                'period' => 'الفترة',
            ];
        @endphp
        <table>
            <thead>
                <tr>
                    @foreach ($columns as $col)
                        <th>{{ $columnLabels[$col] ?? $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        @foreach ($columns as $col)
                            <td>{{ $row[$col] ?? '—' }}</td>
                        @endforeach
                    </tr>
                @endforeach

                {{-- Totals row for cost columns --}}
                @php
                    $sumColumns = array_intersect($columns, ['total_cost', 'total', 'repair_total', 'fuel_total', 'grand_total']);
                @endphp
                @if (count($sumColumns) > 0)
                    <tr class="total-row">
                        @foreach ($columns as $col)
                            @if ($col === reset($columns))
                                <td>الإجمالي</td>
                            @elseif (in_array($col, $sumColumns))
                                <td>
                                    {{ number_format(collect($data)->sum(fn($r) => (float) str_replace(',', '', $r[$col] ?? 0)), 2) }}
                                </td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <span>{{ $institutionName ?? 'المرآب' }} — نظام إدارة المرآب</span>
        <span>صفحة 1</span>
    </div>
</div>
</body>
</html>
