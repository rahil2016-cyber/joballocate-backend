@if($summaryPlain !== '')
    <h2 class="rc-h2">Summary</h2>
    <p class="rc-summary">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $summaryPlain))) !!}</p>
@endif
@if($pdRows !== [] || $showIndiaRow)
    <h2 class="rc-h2">Personal details</h2>
    <table class="rc-table">
        @foreach($pdRows as $label => $val)
            <tr><th>{{ e($label) }}</th><td>{{ e($val) }}</td></tr>
        @endforeach
        @if($showIndiaRow)
            <tr><th>Residing in India</th><td>{{ !empty($resume['residing_in_india']) ? 'Yes' : 'No' }}</td></tr>
        @endif
    </table>
@endif
@if($hasEdu)
    <h2 class="rc-h2">Education</h2>
    @php $listedEdu = false; @endphp
    @foreach($resume['education_list'] ?? [] as $ed)
        @if(is_array($ed) && ($F::filled($ed['title'] ?? null) || $F::filled($ed['institution'] ?? null) || $F::filled($ed['year'] ?? null) || $F::filled($ed['marks'] ?? null) || $F::filled($ed['mode'] ?? null)))
            @php $listedEdu = true; @endphp
            <div class="rc-block">
                <strong>{{ e($ed['title'] ?? '') }}</strong>
                <span class="rc-muted">{{ e($ed['institution'] ?? '') }}</span><br>
                {{ e($ed['year'] ?? '') }}
                @if($F::filled($ed['marks'] ?? null)) · {{ e($ed['marks']) }} @endif
                @if($F::filled($ed['mode'] ?? null)) · {{ e($ed['mode']) }} @endif
            </div>
        @endif
    @endforeach
    @if(! $listedEdu && ($F::filled($resume['graduation']['course'] ?? null) || $F::filled($resume['graduation']['college'] ?? null)))
        <p class="rc-muted">{{ e($resume['graduation']['course'] ?? '') }} @ {{ e($resume['graduation']['college'] ?? '') }}</p>
    @endif
@endif
@if($hasIntern)
    <h2 class="rc-h2">Internships</h2>
    @foreach($resume['internships'] ?? [] as $x)
        @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
            <div class="rc-block">
                <strong>{{ e($x['heading'] ?? '') }}</strong>
                <span class="rc-muted">{{ e($x['dates'] ?? '') }}</span>
                @if($F::filled($x['body'] ?? null))
                    <p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>
                @endif
            </div>
        @endif
    @endforeach
@endif
@if($hasProj)
    <h2 class="rc-h2">Projects</h2>
    @foreach($resume['projects'] ?? [] as $x)
        @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
            <div class="rc-block">
                <strong>{{ e($x['heading'] ?? '') }}</strong>
                <span class="rc-muted">{{ e($x['dates'] ?? '') }}</span>
                @if($F::filled($x['body'] ?? null))
                    <p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>
                @endif
            </div>
        @endif
    @endforeach
@endif
@if($hasWork)
    <h2 class="rc-h2">Work experience</h2>
    @foreach($resume['work_experience'] ?? [] as $x)
        @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
            <div class="rc-block">
                <strong>{{ e($x['heading'] ?? '') }}</strong>
                <span class="rc-muted">{{ e($x['dates'] ?? '') }}</span>
                @if($F::filled($x['body'] ?? null))
                    <p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>
                @endif
            </div>
        @endif
    @endforeach
@endif
@if(!empty($resume['academic_achievements']))
    @php $ach = array_values(array_filter($resume['academic_achievements'], fn ($a) => $F::filled(is_string($a) ? $a : null))); @endphp
    @if($ach !== [])
        <h2 class="rc-h2">Academic achievements</h2>
        <ul class="rc-ul">@foreach($ach as $a)<li>{{ e($a) }}</li>@endforeach</ul>
    @endif
@endif
@if(!empty($resume['awards_honors']))
    @php $aw = array_values(array_filter($resume['awards_honors'], fn ($a) => $F::filled(is_string($a) ? $a : null))); @endphp
    @if($aw !== [])
        <h2 class="rc-h2">Awards &amp; honors</h2>
        <ul class="rc-ul">@foreach($aw as $a)<li>{{ e($a) }}</li>@endforeach</ul>
    @endif
@endif
