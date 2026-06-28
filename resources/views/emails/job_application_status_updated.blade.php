<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status Update</title>
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
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
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
            color: #D1FAE5;
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
        .status-badge {
            display: inline-block;
            background-color: #D1FAE5;
            color: #065F46;
            font-weight: 800;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 14px;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .job-card {
            background-color: #F0FDF4;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
            border: 1px solid #A7F3D0;
        }
        .job-title {
            font-size: 18px;
            font-weight: 700;
            color: #047857;
            margin-top: 0;
            margin-bottom: 6px;
        }
        .job-company {
            font-size: 14px;
            font-weight: 600;
            color: #0F172A;
            margin-bottom: 12px;
        }
        .note-card {
            background-color: #F8FAFC;
            border-left: 4px solid #10B981;
            padding: 14px;
            border-radius: 0 8px 8px 0;
            margin-top: 15px;
            font-size: 13px;
            color: #475569;
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
            <h1>Application Status Update</h1>
            <p>JobAllocate - Career Partner</p>
        </div>
        <div class="content">
            <p class="greeting">Hi {{ $application->user->name }},</p>
            <p class="description">
                The employer has updated the status of your application for the position:
            </p>
            
            <div class="job-card">
                <div class="job-title">{{ $application->jobPost->title }}</div>
                <div class="job-company">{{ $application->jobPost->company->name }}</div>
                <div>New Status:</div>
                <div class="status-badge">{{ $application->status->value }}</div>
                
                @if($application->employer_note)
                    <div class="note-card">
                        <strong>Message from employer:</strong><br>
                        "{{ $application->employer_note }}"
                    </div>
                @endif
            </div>

            <p class="description">
                You can review more details or contact the employer using the JobAllocate mobile app.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} JobAllocate. All rights reserved.</p>
            <p>If you have any questions, reach us at support@joballocate.tech</p>
        </div>
    </div>
</body>
</html>
