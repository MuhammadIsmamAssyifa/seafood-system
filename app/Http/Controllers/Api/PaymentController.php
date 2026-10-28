<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Enums\PaymentStatus;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}


    public function store(
        Request $request,
        Order $order
    ) {

        $payment = $this->paymentService
            ->createPayment(
                $order,
                $request->validate([
                    'method' => [
                        'required',
                        'string',
                    ],

                    'amount' => [
                        'required',
                        'numeric',
                        'min:1',
                    ],
                ])
            );


        return new PaymentResource($payment);
    }

    public function summary(Order $order)
    {
        $paid = $order->payments()
            ->where('status', PaymentStatus::Paid)
            ->sum('amount');


        return response()->json([
            'order_id' => $order->id,

            'grand_total' => $order->grand_total,

            'paid' => $paid,

            'remaining' => max(
                0,
                $order->grand_total - $paid
            ),
        ]);
    }
}
