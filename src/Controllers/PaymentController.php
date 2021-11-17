<?php

namespace Payever\Santander\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Payever\Santander\Exceptions\SantanderFraudException;
use Payever\Santander\Exceptions\SantanderRequestException;
use Payever\Santander\Exceptions\SantanderValidationException;
use Payever\Santander\Payment\Response\SantanderOrder;
use Payever\Santander\Payment\SantanderGateway;

abstract class PaymentController extends Controller
{
    const NOTIFICATION_TYPE_PAYMENT_CHANGED = 'payment.changed';

    public function test()
    {
        return 'This is a Santander Package';
    }

    /**
     * @throws SantanderValidationException
     * @throws SantanderRequestException
     * @throws SantanderFraudException
     */
    public function notify(string $orderId, Request $request)
    {
        $request->validate([
            'data.payment.id' => 'string|required',
            'data.payment.reference' => "string|required|in:{$orderId}",
            'data.payment.status' => 'string|required',
            'data.payment.specific_status' => 'string',
        ]);

        $signatureReceived = $request->header('X-Payever-Signature');
        $paymentId = $request->input('data')['payment']['id'] ?? null;
        $reference = $request->input('data')['payment']['reference'] ?? null;
        $status = $request->input('data')['payment']['status'] ?? null;
        $specificStatus = $request->input('data')['payment']['specific_status'] ?? null;

        $order = new SantanderOrder;
        $order->setPaymentId($paymentId);
        $order->setReference($reference);
        $order->setStatus($status);
        $order->setSpecificStatus($specificStatus);
        if (!$order->signatureMatch($signatureReceived)) {
            throw new SantanderFraudException('Invalid X-Payever-Signature.');
        }

        $notificationType = $request->input('notification_type');
        if ($notificationType === self::NOTIFICATION_TYPE_PAYMENT_CHANGED && $order->isReadyToShip()) {
            /** @var SantanderGateway $gateway */
            $gateway = resolve(SantanderGateway::class);
            $gateway->shippingGoods($order->getPaymentId());
        }

        return response('', 201);
    }
}
