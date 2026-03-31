<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobSeekerProfile;
use App\Models\SeekerPackagePurchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminAnalyticsController extends Controller
{
    use ApiResponses;

    public function overview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        $period = $validated['period'] ?? 'daily';
        $today = now()->startOfDay();
        [$from, $appsExpr, $purchasesExpr] = $this->periodSql($period);

        $activeUserIds = collect()
            ->merge(
                Application::query()
                    ->whereNotNull('applied_at')
                    ->where('applied_at', '>=', $today)
                    ->pluck('user_id')
                    ->all()
            )
            ->merge(
                SeekerPackagePurchase::query()
                    ->where('created_at', '>=', $today)
                    ->pluck('user_id')
                    ->all()
            )
            ->filter()
            ->unique()
            ->values();

        $appRows = Application::query()
            ->selectRaw("{$appsExpr} as bucket, COUNT(*) as c, COUNT(DISTINCT user_id) as dau")
            ->whereNotNull('applied_at')
            ->where('applied_at', '>=', $from)
            ->groupBy(DB::raw($appsExpr))
            ->orderBy('bucket')
            ->get();

        $purchaseRows = SeekerPackagePurchase::query()
            ->selectRaw("{$purchasesExpr} as bucket, COALESCE(SUM(price_inr),0) as spend_inr, COUNT(*) as purchases")
            ->where('created_at', '>=', $from)
            ->groupBy(DB::raw($purchasesExpr))
            ->orderBy('bucket')
            ->get();

        $map = [];
        foreach ($appRows as $r) {
            $k = (string) $r->bucket;
            $map[$k] = [
                'bucket' => $k,
                'applications' => (int) $r->c,
                'daily_active_users' => (int) $r->dau,
                'avg_minutes' => ((int) $r->dau) > 0 ? round(((int) $r->c * 3) / (int) $r->dau, 1) : 0,
                'spend_inr' => 0,
                'purchases' => 0,
            ];
        }
        foreach ($purchaseRows as $r) {
            $k = (string) $r->bucket;
            if (! isset($map[$k])) {
                $map[$k] = [
                    'bucket' => $k,
                    'applications' => 0,
                    'daily_active_users' => 0,
                    'avg_minutes' => 0,
                    'spend_inr' => 0,
                    'purchases' => 0,
                ];
            }
            $map[$k]['spend_inr'] = (int) $r->spend_inr;
            $map[$k]['purchases'] = (int) $r->purchases;
        }
        ksort($map);
        $timeline = array_values($map);

        return $this->ok([
            'period' => $period,
            'daily_active_users_today' => $activeUserIds->count(),
            // We don't have session tracking yet; use action-based engagement proxy.
            'estimated_avg_active_minutes_today' => round(
                (float) Application::query()
                    ->whereNotNull('applied_at')
                    ->where('applied_at', '>=', $today)
                    ->count() * 3 / max(1, $activeUserIds->count()),
                1
            ),
            'applications_today' => Application::query()
                ->whereNotNull('applied_at')
                ->where('applied_at', '>=', $today)
                ->count(),
            'purchases_today' => SeekerPackagePurchase::query()
                ->where('created_at', '>=', $today)
                ->count(),
            'timeline' => $timeline,
            'total_spend_period_inr' => (int) collect($timeline)->sum('spend_inr'),
        ]);
    }

    public function seekerUsage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $today = now()->startOfDay();

        $q = User::query()
            ->where('role', UserRole::JobSeeker->value)
            ->select(['id', 'name', 'email', 'phone', 'is_active', 'created_at']);

        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        $rows = $q->latest('id')->paginate((int) ($validated['per_page'] ?? 20));
        $ids = collect($rows->items())->pluck('id')->all();

        $appsToday = Application::query()
            ->selectRaw('user_id, COUNT(*) as c')
            ->whereIn('user_id', $ids)
            ->whereNotNull('applied_at')
            ->where('applied_at', '>=', $today)
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        $appsTotal = Application::query()
            ->selectRaw('user_id, COUNT(*) as c')
            ->whereIn('user_id', $ids)
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        $lastApplied = Application::query()
            ->selectRaw('user_id, MAX(applied_at) as dt')
            ->whereIn('user_id', $ids)
            ->groupBy('user_id')
            ->pluck('dt', 'user_id');

        $resumePurchases = SeekerPackagePurchase::query()
            ->selectRaw('user_id, SUM(CASE WHEN kind IN ("resume","resume_pdf","combo") THEN 1 ELSE 0 END) as c')
            ->whereIn('user_id', $ids)
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        $jobPurchases = SeekerPackagePurchase::query()
            ->selectRaw('user_id, SUM(CASE WHEN kind IN ("job_applications","combo") THEN 1 ELSE 0 END) as c')
            ->whereIn('user_id', $ids)
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        $spend = SeekerPackagePurchase::query()
            ->selectRaw('user_id, COALESCE(SUM(price_inr),0) as s')
            ->whereIn('user_id', $ids)
            ->groupBy('user_id')
            ->pluck('s', 'user_id');

        $timeSeconds = JobSeekerProfile::query()
            ->whereIn('user_id', $ids)
            ->pluck('total_time_spent_seconds', 'user_id');

        $data = collect($rows->items())->map(function (User $u) use ($appsToday, $appsTotal, $lastApplied, $resumePurchases, $jobPurchases, $spend, $timeSeconds) {
            $secs = (int) ($timeSeconds[$u->id] ?? 0);

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'is_active' => (bool) $u->is_active,
                'created_at' => $u->created_at?->toIso8601String(),
                'applications_today' => (int) ($appsToday[$u->id] ?? 0),
                'applications_total' => (int) ($appsTotal[$u->id] ?? 0),
                'last_applied_at' => $lastApplied[$u->id] ?? null,
                'resume_purchase_count' => (int) ($resumePurchases[$u->id] ?? 0),
                'job_purchase_count' => (int) ($jobPurchases[$u->id] ?? 0),
                'total_spend_inr' => (int) ($spend[$u->id] ?? 0),
                'estimated_active_minutes_today' => (int) (($appsToday[$u->id] ?? 0) * 3),
                'time_spent_seconds_total' => $secs,
                'time_spent_minutes_total' => round($secs / 60, 1),
            ];
        })->values()->all();

        return $this->ok(
            $data,
            'OK',
            [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ]
        );
    }

    private function periodSql(string $period): array
    {
        switch ($period) {
            case 'monthly':
                return [
                    now()->subMonths(11)->startOfMonth(),
                    "DATE_FORMAT(applied_at, '%Y-%m')",
                    "DATE_FORMAT(created_at, '%Y-%m')",
                ];
            case 'weekly':
                return [
                    now()->subWeeks(11)->startOfWeek(Carbon::MONDAY),
                    "DATE_FORMAT(applied_at, '%x-W%v')",
                    "DATE_FORMAT(created_at, '%x-W%v')",
                ];
            case 'daily':
            default:
                return [
                    now()->subDays(13)->startOfDay(),
                    "DATE(applied_at)",
                    "DATE(created_at)",
                ];
        }
    }
}

