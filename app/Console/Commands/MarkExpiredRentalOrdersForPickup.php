<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MarkExpiredRentalOrdersForPickup extends Command
{
    protected $signature = 'orders:mark-expired-pickups';

    protected $description = 'Move rental orders past their end date to pickup pending.';

    public function handle(): int
    {
        $timezone = config('isiin.display_timezone', 'Asia/Jakarta');
        $count = Order::query()
            ->where('status', 'installed')
            ->whereDate('end_date', '<', Carbon::today($timezone))
            ->update(['status' => 'pickup_pending']);

        $this->info("{$count} pesanan dipindahkan ke tahap menunggu pengambilan.");

        return self::SUCCESS;
    }
}
