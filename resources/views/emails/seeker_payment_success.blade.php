<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobAllocate - Invoice</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F8FAFC;
            margin: 0;
            padding: 0;
            color: #1E293B;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #E2E8F0;
        }
        .header {
            background: linear-gradient(135deg, #174A7E 0%, #0F172A 100%);
            padding: 30px;
            text-align: center;
            color: #FFFFFF;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 13px;
            color: #E2E8F0;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            color: #0F172A;
        }
        .description {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }
        .invoice-card {
            background-color: #F8FAFC;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
            border: 1px solid #E2E8F0;
        }
        .invoice-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748B;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            margin-top: 0;
        }
        .invoice-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .invoice-row.total {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px dashed #CBD5E1;
            font-weight: 800;
            font-size: 16px;
            color: #174A7E;
        }
        .invoice-label {
            color: #64748B;
        }
        .invoice-value {
            color: #0F172A;
            font-weight: 600;
        }
        .footer {
            background-color: #F8FAFC;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #E2E8F0;
            font-size: 12px;
            color: #64748B;
        }
        .button {
            display: inline-block;
            background-color: #174A7E;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>JobAllocate</h1>
            <p>Right Job, Right Candidate</p>
        </div>
        <div class="content">
            <p class="greeting">Hi {{ $purchase->user->name }},</p>
            <p class="description">
                Thank you for your purchase on JobAllocate! Your package is active and ready for use. We have attached your transaction invoice details below.
            </p>
            
            <div class="invoice-card">
                <p class="invoice-title">Invoice details</p>
                <div class="invoice-row">
                    <span class="invoice-label">Package Name</span>
                    <span class="invoice-value">{{ $purchase->title }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Purchase Date</span>
                    <span class="invoice-value">{{ $purchase->activated_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Expiration Date</span>
                    <span class="invoice-value">{{ $purchase->expires_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Razorpay Order ID</span>
                    <span class="invoice-value">{{ $purchase->razorpay_order_id }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Razorpay Payment ID</span>
                    <span class="invoice-value">{{ $purchase->razorpay_payment_id }}</span>
                </div>
                <div class="invoice-row total">
                    <span>Total Amount Paid</span>
                    <span>₹ {{ number_format($purchase->price_inr, 2) }}</span>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Explore Dashboard</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} JobAllocate. All rights reserved.</p>
            <p>If you have any questions, reach us at support@joballocate.tech</p>
        </div>
    </div>
</body>
</html>
