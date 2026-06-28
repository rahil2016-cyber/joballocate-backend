<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted successfully</title>
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
            background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
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
            color: #E0F2FE;
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
        .job-card {
            background-color: #F0F9FF;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
            border: 1px solid #BAE6FD;
        }
        .job-title {
            font-size: 18px;
            font-weight: 700;
            color: #0369A1;
            margin-top: 0;
            margin-bottom: 6px;
        }
        .job-company {
            font-size: 14px;
            font-weight: 600;
            color: #0F172A;
            margin-bottom: 12px;
        }
        .job-detail {
            font-size: 13px;
            color: #475569;
            margin-bottom: 6px;
        }
        .footer {
            background-color: #F8FAFC;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #E2E8F0;
            font-size: 12px;
            color: #64748B;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application Submitted</h1>
            <p>JobAllocate - Connecting Talent with Dreams</p>
        </div>
        <div class="content">
            <p class="greeting">Hi {{ $application->user->name }},</p>
            <p class="description">
                Your application for the following position has been successfully submitted! The employer has been notified and will review your profile shortly.
            </p>
            
            <div class="job-card">
                <div class="job-title">{{ $application->jobPost->title }}</div>
                <div class="job-company">{{ $application->jobPost->company->name }}</div>
                @if($application->jobPost->location)
                    <div class="job-detail">📍 <strong>Location:</strong> {{ $application->jobPost->location }}</div>
                @endif
                @if($application->jobPost->employment_type)
                    <div class="job-detail">💼 <strong>Job Type:</strong> {{ $application->jobPost->employment_type }}</div>
                @endif
                <div class="job-detail">📅 <strong>Applied on:</strong> {{ $application->applied_at->format('M d, Y H:i A') }}</div>
            </div>

            <p class="description">
                We'll keep you updated as the status of your application changes. You can also view the status at any time inside the JobAllocate app.
            </p>
            <p class="description">Best of luck with your application!</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} JobAllocate. All rights reserved.</p>
            <p>If you have any questions, reach us at support@joballocate.tech</p>
        </div>
    </div>
</body>
</html>
