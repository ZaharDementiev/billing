<?php

namespace App\Console\Commands;

use App\Factories\PaymentSystemFactory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ChargeMoney extends Command
{
    protected $signature = 'follow:charge';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

   public function handle()
    {
        $users = User::where('next_payment_at', '<=', Carbon::now())
            ->get();

        $service = PaymentSystemFactory::all()[env('PAYMENT_SYSTEM')];

        foreach ($users as $user) {
            $service->charge($user);
        }
    }
}
