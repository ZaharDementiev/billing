<?php

namespace App\Console\Commands;

use App\Factories\PaymentSystemFactory;
use App\Jobs\SendEmailJob;
use App\Models\Email;
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
        $emails = Email::all();
        $users = User::where('next_payment_at', '<=', Carbon::now())
            ->get();

        $service = PaymentSystemFactory::all()[config('packages.payment_system')];

        foreach ($users as $user) {
            if ($service->charge($user)) {
                dispatch(new SendEmailJob($user->email, $emails[$user->week + 1]))->onQueue('mailing');
            }
        }
    }
}
