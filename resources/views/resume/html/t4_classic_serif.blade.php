<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    <style>
        body { margin: 0; background: #fff; color: #111; font-family: Georgia, 'Times New Roman', serif; }
        .wrap { max-width: 700px; margin: 0 auto; padding: 48px 36px; }
        h1 { font-size: 36px; font-weight: normal; border-bottom: 1px solid #333; padding-bottom: 12px; margin: 0 0 8px; }
        .contact { font-size: 13px; font-style: italic; color: #444; margin-bottom: 28px; }
        h2 { font-size: 15px; font-variant: small-caps; letter-spacing: .15em; margin: 28px 0 8px; }
        p, li { font-size: 14px; line-height: 1.55; }
        ul { padding-left: 18px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>{{ e($resume['full_name']) }}</h1>
    <div class="contact">{{ e($resume['professional_title']) }} — {{ e($resume['email']) }} — {{ e($resume['mobile']) }} — {{ e($resume['location']) }}</div>
    <h2>Summary</h2>
    <p>{{ nl2br(e($resume['summary'])) }}</p>
    <h2>Education</h2>
    <ul>
        @foreach($resume['education_list'] as $ed)
            <li><strong>{{ e($ed['title']) }}</strong>, {{ e($ed['institution']) }}, {{ e($ed['year']) }}{{ $ed['marks'] ? ', '.$ed['marks'] : '' }}</li>
        @endforeach
        @if(empty($resume['education_list']))
            <li>{{ e($resume['graduation']['course']) }}, {{ e($resume['graduation']['college']) }}</li>
        @endif
    </ul>
    <h2>Experience</h2>
    @foreach($resume['work_experience'] as $x)
        <p><strong>{{ e($x['heading']) }}</strong> ({{ e($x['dates']) }})</p>
        @if($x['body'])<p>{{ nl2br(e($x['body'])) }}</p>@endif
    @endforeach
    <h2>Languages & certifications</h2>
    <p>{{ e(implode('; ', $resume['languages'])) }} @if(!empty($resume['certifications']))<br>{{ e(implode('; ', $resume['certifications'])) }}@endif</p>
</div>
</body>
</html>
