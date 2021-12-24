<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use \CloudPayments\Manager;
use Illuminate\Support\Facades\Request;

class CloudPaymentService implements ChargableService
{
    private $client;

    public function __construct()
    {
        $this->client = new Manager('pk_09d95bbb21704af9a2d891daf0933', '6504efafa08f29f8c22b8db0b99ad9f4');
//        $kassaId = DB::table('settings')->where('name', 'shop_id')->first()->value;
//        $key = DB::table('settings')->where('name', 'secret_key')->first()->value;
//        $this->client->setAuth($kassaId, $key);
    }

    public function charge($user)
    {
        // TODO: Implement charge() method.
    }

    public function setup(string $email)
    {
        if (User::where('email', $email)->exists()) {
            return [
                'error' => true
            ];
        }

        $user = new User();
        $user->email = $email;
        $user->save();

        $payment = new Payment();
        $amount = DB::table('settings')->where('name', 'setup_payment_amount')->first()->value;
        $payment->amount = (int) $amount;
        $payment->user_id = $user->id;
        $payment->payment_system = Payment::CLOUD;
        $payment->save();

        return [
            'error'         => false,
            'amount'        => $payment->amount,
            'email'         => $user->email,
            'days'          => (int) DB::table('settings')->where('name', 'next_send_days')->first()->value,
            'payment_id'    => $payment->id,
            'key'           => DB::table('settings')->where('name', 'shop_id')->first()->value
        ];
    }

    public function notify(array $request)
    {

    }
}
