<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Certificate - {{ $share->certificate_number }}</title>
    <style>
        @page {
            size: landscape;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: #f5f5f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .certificate {
            width: 900px;
            min-height: 600px;
            background: #fffef5;
            border: 3px solid #1a3a5c;
            padding: 8px;
            position: relative;
        }
        .certificate-inner {
            border: 2px solid #c4a35a;
            padding: 40px 50px;
            height: 100%;
            position: relative;
        }
        .certificate-inner::before {
            content: '';
            position: absolute;
            top: 4px;
            left: 4px;
            right: 4px;
            bottom: 4px;
            border: 1px solid #e0d5b0;
            pointer-events: none;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .institution-name {
            font-size: 28px;
            font-weight: bold;
            color: #1a3a5c;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 5px;
        }
        .certificate-title {
            font-size: 20px;
            color: #c4a35a;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-top: 10px;
            border-bottom: 2px solid #c4a35a;
            display: inline-block;
            padding-bottom: 5px;
        }
        .certificate-number {
            font-size: 12px;
            color: #888;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
        }
        .body-text {
            text-align: center;
            font-size: 14px;
            color: #333;
            line-height: 2;
            margin: 30px 0;
        }
        .member-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a3a5c;
            border-bottom: 1px solid #1a3a5c;
            display: inline-block;
            padding: 0 20px 3px;
        }
        .shares-detail {
            font-size: 18px;
            font-weight: bold;
            color: #1a3a5c;
        }
        .details-grid {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            text-align: center;
        }
        .detail-item {
            flex: 1;
        }
        .detail-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 5px;
        }
        .detail-value {
            font-size: 16px;
            font-weight: bold;
            color: #1a3a5c;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 40px;
            padding-top: 20px;
        }
        .signature-line {
            text-align: center;
            width: 200px;
        }
        .signature-line .line {
            border-top: 1px solid #333;
            margin-bottom: 5px;
        }
        .signature-line .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
        }
        .date-issued {
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .certificate {
                width: 100%;
                min-height: auto;
                border-width: 2px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 20px;" class="no-print">
        <button onclick="window.print()" style="padding: 10px 30px; background: #1a3a5c; color: white; border: none; border-radius: 6px; font-size: 14px; cursor: pointer;">
            Print Certificate
        </button>
    </div>

    <div class="certificate">
        <div class="certificate-inner">
            <div class="header">
                <div class="institution-name">{{ $share->institution_name }}</div>
                <div class="certificate-title">Share Certificate</div>
                <div class="certificate-number">No. {{ $share->certificate_number }}</div>
            </div>

            <div class="body-text">
                <p>This is to certify that</p>
                <p style="margin: 15px 0;">
                    <span class="member-name">{{ $share->first_name }} {{ $share->last_name }}</span>
                </p>
                <p>Member No. {{ $share->customer_number }}</p>
                <p style="margin-top: 15px;">is the registered holder of</p>
                <p class="shares-detail">
                    {{ number_format($share->quantity) }} {{ $share->product_name }}
                </p>
                <p>of a par value of {{ number_format($share->par_value, 2) }} each,
                    totalling {{ number_format($share->total_value, 2) }}</p>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Share Class</div>
                    <div class="detail-value">{{ $share->product_name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Quantity</div>
                    <div class="detail-value">{{ number_format($share->quantity) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Par Value</div>
                    <div class="detail-value">{{ number_format($share->par_value, 2) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Total Value</div>
                    <div class="detail-value">{{ number_format($share->total_value, 2) }}</div>
                </div>
            </div>

            <div class="footer">
                <div class="signature-line">
                    <div class="line"></div>
                    <div class="label">Authorized Signatory</div>
                </div>
                <div class="date-issued">
                    <p>Date of Issue</p>
                    <p style="font-weight: bold; color: #1a3a5c;">{{ \Carbon\Carbon::parse($share->purchase_date)->format('F d, Y') }}</p>
                </div>
                <div class="signature-line">
                    <div class="line"></div>
                    <div class="label">Secretary</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
