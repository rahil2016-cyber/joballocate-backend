<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Posted Successfully</title>
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
        .job-card {
            background-color: #F8FAFC;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
            border: 1px solid #E2E8F0;
        }
        .job-title {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            margin-top: 0;
            margin-bottom: 6px;
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
            <h1>Job Posted Successfully</h1>
            <p>JobAllocate - Employer Portal</p>
        </div>
        <div class="content">
            <p class="greeting">Hi {{ $jobPost->company->name }},</p>
            <p class="description">
                Your new job post has been successfully created and published on JobAllocate!
            </p>
            
            <div class="job-card">
                <div class="job-title">{{ $jobPost->title }}</div>
                @if($jobPost->location)
                    <div class="job-detail">📍 <strong>Location:</strong> {{ $jobPost->location }}</div>
                @endif
                @if($jobPost->experience_level)
                    <div class="job-detail">🎓 <strong>Experience:</strong> {{ $jobPost->experience_level }}</div>
                @endif
                @if($jobPost->employment_type)
                    <div class="job-detail">💼 <strong>Job Type:</strong> {{ $jobPost->employment_type }}</div>
                @endif
            </div>

            <p class="description">
                You will receive email notifications as soon as candidates apply to this job. You can manage your job posting and review applications at any time from the JobAllocate app.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} JobAllocate. All rights reserved.</p>
            <p>For support, please contact billing@joballocate.tech</p>
        </div>
    </div>
</body>
</html>
