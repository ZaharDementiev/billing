<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;
use YooKassa\Model\CancellationDetailsReasonCode;
use YooKassa\Model\PaymentStatus;

class PaymentController extends Controller
{
    public function saveYandexDetails()
    {
        if (!auth()->check()) {
            return  redirect()->route('login');
        }
        $payment = new Payment();
        $payment->amount = Payment::SETUP_PAYMENT_VALUE;
        $payment->user_id = auth()->id();
        $payment->payment_system = Payment::YANDEX;
        $payment->save();

        $desc = 'Подписка на сервис';
        $meta = [
            'user_id' => auth()->id(),
            'payment_id' => $payment->id,
        ];

        $route = route('index');

        $payment = $this->setupPayment($meta, $payment->getRawOriginal('amount'), $route, $desc);

        return redirect()->to($payment->getConfirmation()->getConfirmationUrl());
    }

    private function setupPayment(array $meta, $amount, $route, $desc = 'Привязка карты к сервису')
    {
        $client = new Client();
        $meta['notify_user_id'] = auth()->id();
        try {
            return $client->createPayment(
                array(
                    'amount' => array(
                        'value' => $amount,
                        'currency' => 'RUB',
                    ),
                    'payment_method_data' => array(
                        'type' => Payment::YANDEX,
                    ),
                    'confirmation' => array(
                        'type' => 'redirect',
                        'return_url' => $route,
                    ),
                    'description' => $desc,
                    'capture' => true,
                    'save_payment_method' => true,
                    'metadata' => $meta
                ),
                uniqid('', true)
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public function chargeFollow()
    {
        $user = auth()->user();
        $client = new Client();
        $client->setAuth(config('packages.yandex_kassa_id'), config('packages.yandex_kassa_secret'));
        $success = false;

        try {
            $response = $client->createPayment(
                array(
                    'amount' => array(
                        'value' => Payment::PAYMENT_VALUE,
                        'currency' => 'RUB',
                    ),
                    'payment_method_id' => $user->card_token,
                    'description' => 'Подписка на сервисе',
                    'capture' => true
                ),
                uniqid('', true)
            );
            if ($response->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE) {
                $response = $client->capturePayment(
                    array(
                        'amount' => $response->amount,
                    ),
                    $response->id,
                    uniqid('', true)
                );
            }
            if ($response->getStatus() === PaymentStatus::CANCELED &&
                $response->cancellation_details->getReason() == CancellationDetailsReasonCode::PERMISSION_REVOKED) {
                return;
            }
            if ($response->getStatus() === PaymentStatus::SUCCEEDED) {
                $success = true;
                Log::info('SUCCESS is ' . $success);

                return;
            }
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";
            Log::error($e->getMessage());
            return;
        }
        if (!$success) {
            Log::info('SUCCESS is ' . $success);
//            if ($this->debt_days == 12) {
//                $this->delete();
//            } else {
//                $this->active = false;
//                if ($this->debt_days <= 0) {
//                    $this->debt_days = 3;
//                } else {
//                    $this->debt_days *= 2;
//                }
//
//                $this->next_payment_at = Carbon::now()->addDays($this->debt_days);
//                $this->save();
//            }
        }
    }
}
