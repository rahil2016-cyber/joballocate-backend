<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Job Application Received</title>
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
            background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
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
            color: #E0E7FF;
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
        .applicant-card {
            background-color: #EEF2F6;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
            border: 1px solid #E2E8F0;
        }
        .applicant-name {
            font-size: 18px;
            font-weight: 700;
            color: #3730A3;
            margin-top: 0;
            margin-bottom: 6px;
        }
        .applicant-detail {
            font-size: 13px;
            color: #475569;
            margin-bottom: 6px;
        }
        .cover-letter {
            font-style: italic;
            font-size: 13px;
            color: #64748B;
            border-left: 3px solid #3730A3;
            padding-left: 12px;
            margin-top: 14px;
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
            <h1>New Application Received</h1>
            <p>JobAllocate - Employer Portal</p>
        </div>
        <div class="content">
            <p class="greeting">Hi {{ $application->jobPost->company->name }},</p>
            <p class="description">
                You have received a new job application for the position: <strong>{{ $application->jobPost->title }}</strong>.
            </p>
            
            <div class="applicant-card">
                <div class="applicant-name">{{ $application->user->name }}</div>
                <div class="applicant-detail">📧 <strong>Email:</strong> {{ $application->user->email }}</div>
                <div class="applicant-detail">📞 <strong>Phone:</strong> {{ $application->user->phone }}</div>
                @if($application->cover_letter)
                    <div class="applicant-detail" style="margin-top: 10px;"><strong>Cover Letter:</strong></div>
                    <div class="cover-letter">"{{ $application->cover_letter }}"</div>
                @endif
            </div>

            <p class="description">
                Please open the JobAllocate app to view their full profile, resume, and manage their application status.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} JobAllocate. All rights reserved.</p>
            <p>For support, please contact billing@joballocate.tech</p>
        </div>
    </div>
</body>
</html>
