<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobAllocate - Employer Subscription Invoice</title>
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
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
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
            color: #1E293B;
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
            background-color: #0F172A;
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
            <h1>JobAllocate Employer</h1>
            <p>Right Job, Right Candidate</p>
        </div>
        <div class="content">
            <p class="greeting">Hi {{ $payment->company->name }},</p>
            <p class="description">
                Your Corporate Plan subscription has been successfully purchased and activated! You can now post jobs and access premium candidate services. Below is the detailed invoice of your subscription payment.
            </p>
            
            <div class="invoice-card">
                <p class="invoice-title">Subscription details</p>
                <div class="invoice-row">
                    <span class="invoice-label">Plan Name</span>
                    <span class="invoice-value">{{ $payment->package ? $payment->package->title : 'Corporate Package' }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Subscription Cycle</span>
                    <span class="invoice-value">Month #{{ $payment->cycle_number }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Payment Date</span>
                    <span class="invoice-value">{{ $payment->purchased_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Razorpay Order ID</span>
                    <span class="invoice-value">{{ $payment->razorpay_order_id }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Razorpay Payment ID</span>
                    <span class="invoice-value">{{ $payment->razorpay_payment_id }}</span>
                </div>
                <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 12px 0;">
                <div class="invoice-row">
                    <span class="invoice-label">Base Price</span>
                    <span class="invoice-value">₹ {{ number_format($payment->amount_inr / 1.18, 2) }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">GST (18%)</span>
                    <span class="invoice-value">₹ {{ number_format($payment->amount_inr - ($payment->amount_inr / 1.18), 2) }}</span>
                </div>
                <div class="invoice-row total">
                    <span>Total Amount Paid</span>
                    <span>₹ {{ number_format($payment->amount_inr, 2) }}</span>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Go to Employer Panel</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} JobAllocate. All rights reserved.</p>
            <p>If you need assistance, please contact billing@joballocate.tech</p>
        </div>
    </div>
</body>
</html>
