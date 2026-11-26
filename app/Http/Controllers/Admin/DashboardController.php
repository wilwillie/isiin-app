<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $rate = (float) config('isiin.commission_rate');
        $chartEnd = Carbon::today();
        $chartStart = $chartEnd->copy()->subDays(29);
        $commissionExpression = 'SUM(ROUND((rental_fee + delivery_fee + installation_fee) * ?, 0))';

        $totalCommission = (int) (DB::table('orders')
            ->where('status', 'completed')
            ->selectRaw("COALESCE({$commissionExpression}, 0) as commission", [$rate])
            ->first()->commission);

        $monthCommission = (int) (DB::table('orders')
            ->where('status', 'completed')
            ->whereRaw('DATE(COALESCE(completed_at, updated_at)) >= ?', [Carbon::today()->startOfMonth()->toDateString()])
            ->whereRaw('DATE(COALESCE(completed_at, updated_at)) <= ?', [$chartEnd->toDateString()])
            ->selectRaw("COALESCE({$commissionExpression}, 0) as commission", [$rate])
            ->first()->commission);

        $dailyCommissions = DB::table('orders')
            ->where('status', 'completed')
            ->whereRaw('DATE(COALESCE(completed_at, updated_at)) BETWEEN ? AND ?', [
                $chartStart->toDateString(),
                $chartEnd->toDateString(),
            ])
            ->selectRaw(
                'DATE(COALESCE(completed_at, updated_at)) as completion_day, ' . $commissionExpression . ' as commission',
                [$rate],
            )
            ->groupBy('completion_day')
            ->get()
            ->pluck('commission', 'completion_day');

        $chart = collect(range(0, 29))->map(function (int $offset) use ($chartStart, $dailyCommissions): array {
            $date = $chartStart->copy()->addDays($offset);

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'commission' => (int) ($dailyCommissions->get($date->toDateString()) ?? 0),
            ];
        });

        $chartMax = max(1, (int) $chart->max('commission'));
        $chart = $chart->values()->map(function (array $point, int $index) use ($chartMax): array {
            $point['x'] = round(45 + ($index * (760 / 29)), 2);
            $point['y'] = round(205 - ($point['commission'] / $chartMax * 165), 2);

            return $point;
        });
        $chartPoints = $chart->map(fn (array $point): string => $point['x'] . ',' . $point['y'])->implode(' ');
        $chartAreaPoints = '45,205 ' . $chartPoints . ' 805,205';

        $completedOrders = Order::query()
            ->with(['customer', 'vendor', 'package'])
            ->where('status', 'completed')
            ->orderByRaw('COALESCE(completed_at, updated_at) DESC')
            ->paginate(15);

        $stats = [
            'total_commission' => $totalCommission,
            'month_commission' => $monthCommission,
            'completed_count' => Order::query()->where('status', 'completed')->count(),
            'commission_rate' => $rate,
            'open_disputes' => Dispute::query()->where('status', 'open')->count(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'chart',
            'chartMax',
            'chartPoints',
            'chartAreaPoints',
            'completedOrders',
        ));
    }
}
