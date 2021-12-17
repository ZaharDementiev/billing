<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use YooKassa\Model\Notification\NotificationCanceled;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\NotificationEventType;
use YooKassa\Model\PaymentInterface;
use YooKassa\Client;
use YooKassa\Model\CancellationDetailsReasonCode;
use YooKassa\Model\PaymentStatus;

class PaymentController extends Controller
{
    public function saveYandexDetails(Request $request)
    {
        $requestBody = $request->all();

        $notification = ($requestBody['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
            ? new NotificationSucceeded($requestBody) : null;
        if (!$notification) {
            if ($requestBody['event'] === NotificationEventType::PAYMENT_CANCELED) {
                $notification = new NotificationCanceled($requestBody);
                if ($payment = $notification->getObject()) {
                    if ($payment->getMetadata() && $payment->getMetadata()->notify_user_id) {
                        Log::error('Payment Fail');
                        abort(500);
                    }
                }
            }
            return response()->json([]);
        }

        $payment = $notification->getObject();

        if (!$payment->payment_method->saved) {
            return response()->json([]);
        }

        $metadata = $request->metadata ?? null;
        if (!$metadata) {
            return response()->json([]);
        }
        $user = User::find($request->metadata->user_id);
        if (!$user) {
            Log::error('no user id');
            abort(500);
        }

        $user->card_token = $request->payment_method->getId();
        $user->save();

        return response()->json([]);
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
