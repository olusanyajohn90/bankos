<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a202c; margin: 0; padding: 0; }
    .header { background-color: #1e3a5f; color: #fff; padding: 14px 20px; }
    .header h1 { margin: 0; font-size: 14px; font-weight: bold; letter-spacing: 1px; }
    .header p { margin: 3px 0 0; font-size: 9px; opacity: 0.8; }
    .gold-line { height: 3px; background-color: #c9a84c; }
    .content { padding: 14px 20px; }
    .section-title { font-size: 10px; font-weight: bold; color: #1e3a5f; text-transform: uppercase;
                     letter-spacing: 0.5px; border-bottom: 1px solid #c9a84c; padding-bottom: 4px; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    th { background-color: #1e3a5f; color: #fff; text-align: left; padding: 4px 6px; font-size: 8px; }
    td { border-bottom: 1px solid #e2e8f0; padding: 3px 6px; font-size: 8px; }
    tr:nth-child(even) td { background-color: #f7fafc; }
    .footer { position: fixed; bottom: 0; width: 100%; padding: 4px 20px; font-size: 8px;
              color: #718096; border-top: 1px solid #e2e8f0; text-align: right; }
    .generated { font-size: 8px; color: #718096; margin-bottom: 8px; }
    .meta-row { display: table; width: 100%; margin-bottom: 8px; }
    .meta-cell { display: table-cell; width: 50%; vertical-align: top; }
    .meta-label { color: #718096; }
    .meta-val { font-weight: bold; }
</style>
</head>
<body>

<div class="header">
    <h1>{{ strtoupper($report->name) }}</h1>
    <p>Custom Report &mdash; Generated {{ now()->format('d M Y, H:i') }}</p>
</div>
<div class="gold-line"></div>

<div class="content">
    <p class="generated">
        Data Source: <strong>{{ ucfirst($report->data_source) }}</strong> &nbsp;&nbsp;
        Total Rows: <strong>{{ number_format($rows->count()) }}</strong> &nbsp;&nbsp;
        Generated: {{ now()->format('d M Y H:i') }}
    </p>

    @if($rows->isEmpty())
        <p style="color:#718096; font-style:italic;">No data to display.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach($columns as $col)
                        <th>{{ strtoupper($col) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows->take(500) as $row)
                    <tr>
                        @foreach($columns as $col)
                            <td>{{ data_get((array)$row, $col) ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($rows->count() > 500)
            <p style="color:#718096;font-style:italic;">Showing first 500 rows. Export CSV for full data.</p>
        @endif
    @endif
</div>

<div class="footer">
    CONFIDENTIAL &mdash; {{ now()->format('d M Y') }}
</div>

</body>
</html>
