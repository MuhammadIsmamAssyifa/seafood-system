<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Order $order)
    {
        return PaymentResource::collection($order->payments()->latest()->get());
    }

    public function store(
        StorePaymentRequest $request,
        Order $order
    ): PaymentResource {
        $payment = $this->paymentService->createPayment(
            $order,
            $request->validated()
        );

        return new PaymentResource($payment);
    }

    public function summary(Order $order): JsonResponse
    {
        return response()->json([
            'data' => $this->paymentService->getSummary($order),
        ]);
    }
}
