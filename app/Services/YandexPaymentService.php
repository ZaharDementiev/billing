<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\Email;
use App\Models\Payment;
use Carbon\Carbon;
use http\Client\Curl\User;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;
use YooKassa\Model\CancellationDetailsReasonCode;
use YooKassa\Model\Notification\NotificationCanceled;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\NotificationEventType;
use YooKassa\Model\PaymentStatus;

class YandexPaymentService implements ChargableService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuth(config('packages.yandex_kassa_id'), config('packages.yandex_kassa_secret'));
    }

    public function notify(array $request)
    {
        $requestBody = $request;

        $notification = ($requestBody['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
            ? new NotificationSucceeded($requestBody) : null;
        if (!$notification) {
            if ($requestBody['event'] === NotificationEventType::PAYMENT_CANCELED) {
                $notification = new NotificationCanceled($requestBody);
                if ($payment = $notification->getObject()) {
                    if ($payment->getMetadata() && $payment->getMetadata()->user_id) {
                        Log::info('Payment Fail . ' . $payment->metadata->user_id);
                    }
                }
            }
            return response()->json([]);
        }

        $payment = $notification->getObject();

        if (!$payment->payment_method->saved) {
            return response()->json([]);
        }

        if ($id = $payment->getMetadata()->user_id) {
            $user = \App\Models\User::findOrFail($id);
            $user->card_token = $payment->payment_method->getId();
            $user->active_follower = true;
            $user->save();
        }

        return response()->json([]);
    }

    public function charge($user)
    {
        $success = false;

        try {
            $response = $this->client->createPayment(
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
                $response = $this->client->capturePayment(
                    array(
                        'amount' => $response->amount,
                    ),
                    $response->id,
                    uniqid('', true)
                );
            }
            if ($response->getStatus() === PaymentStatus::CANCELED &&
                $response->cancellation_details->getReason() == CancellationDetailsReasonCode::PERMISSION_REVOKED) {
                return null;
            }
            if ($response->getStatus() === PaymentStatus::SUCCEEDED) {
                $success = true;
                $payment = new Payment();
                $payment->amount = Payment::PAYMENT_VALUE;
                $payment->user_id = $user->id;
                $payment->payment_system = Payment::YANDEX;
                $payment->uuid = $response->id;
                $payment->save();

                $user->week++;
                $user->next_payment_at = Carbon::now()->addDays(Email::NEXT_SENT_DAYS);
                $user->active_follower = true;
                $user->save();
            }
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";
            Log::error($e->getMessage());
            return null;
        }

        if (!$success) {
            $user->active_follower = false;
            $user->save();
            return null;
        }

        return true;
    }

    public function setup(bool $authCheck, $user)
    {
        if (!$authCheck) {
            return  redirect()->route('login');
        }

        $payment = new Payment();
        $payment->amount = Payment::SETUP_PAYMENT_VALUE;
        $payment->user_id = $user->id;
        $payment->payment_system = Payment::YANDEX;
        $payment->save();

        $desc = 'Подписка на сервис';
        $meta = [
            'user_id' => $user->id,
            'payment_id' => $payment->id,
        ];

        $route = route('index');

        $payment = $this->setupPayment($meta, $payment->getRawOriginal('amount'), $route, $desc);

        return redirect()->to($payment->getConfirmation()->getConfirmationUrl());
    }

    private function setupPayment(array $meta, $amount, $route, $desc = 'Привязка карты к сервису')
    {
        try {
            $array = array(
                'amount' => array(
                    'value' => $amount,
                    'currency' => 'RUB',
                ),
                'payment_method_data' => array(
                    'type' => 'bank_card',
                ),
                'confirmation' => array(
                    'type' => 'redirect',
                    'return_url' => $route,
                ),
                'description' => $desc,
                'capture' => true,
                'save_payment_method' => true,
                'metadata' => $meta
            );
            return $this->client->createPayment($array, uniqid('', true));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
