<?php

namespace App\Services;

use App\Models\Email;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        $kassaId = DB::table('settings')->where('name', 'shop_id')->first()->value;
        $key = DB::table('settings')->where('name', 'secret_key')->first()->value;
        $this->client->setAuth($kassaId, $key);
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
            $user = User::findOrFail($id);
            $user->card_token = $payment->payment_method->getId();
            $user->active_follower = true;
            $user->save();
        }

        return response()->json([]);
    }

    public function charge($user)
    {
        $success = false;
        $amount = DB::table('settings')->where('name', 'payment_amount')->first()->value;

        try {
            $response = $this->client->createPayment(
                array(
                    'amount' => array(
                        'value' => (int) $amount,
                        'currency' => 'RUB',
                    ),
                    'payment_method_id' => $user->card_token,
                    'description' => 'Подписка на сервисе',
                    'capture' => true
                ),
                uniqid('', true)
            );
            $payment = new Payment();
            $payment->amount = (int) $amount;
            $payment->user_id = $user->id;
            $payment->payment_system = Payment::YANDEX;
            $payment->uuid = $response->id;
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
                $payment->status = Payment::REJECTED;
                $success = false;
            }
            if ($response->getStatus() === PaymentStatus::SUCCEEDED) {
                $success = true;

                $payment->save();

                $user->week++;
                $days = DB::table('settings')->where('name', 'next_send_days')->first()->value;
                $user->next_payment_at = Carbon::now()->addDays((int) $days);
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

    public function setup(string $email)
    {
        $payment = new Payment();
        $amount = DB::table('settings')->where('name', 'setup_payment_amount')->first()->value;
        $payment->amount = (int) $amount;
//        $payment->user_id = $user->id;
        $payment->payment_system = Payment::YANDEX;
        $payment->save();

        $desc = 'Подписка на сервис';
        $meta = [
//            'user_id' => $user->id,
//            'payment_id' => $payment->id,
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
