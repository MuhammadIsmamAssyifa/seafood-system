<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\OrderItemChange;
use App\Services\OrderItemChangeService;
use Illuminate\Http\Request;

class OrderItemChangeController extends Controller
{
    public function __construct(
        protected OrderItemChangeService $changeService
    ) {}


    /**
     * Membuat request perubahan item
     */
    public function store(
        Request $request,
        OrderItem $orderItem
    ) {

        $change = $this->changeService
            ->create(
                $orderItem,
                $request->validate([

                    'type' => [
                        'required',
                        'string',
                    ],


                    'quantity' => [
                        'nullable',
                        'numeric',
                        'min:0.001',
                    ],


                    'price' => [
                        'nullable',
                        'numeric',
                        'min:0',
                    ],


                    'reason' => [
                        'required',
                        'string',
                    ],

                ])
            );


        return response()->json([
            'message' => 'Perubahan order berhasil dibuat.',
            'data' => $change,
        ]);
    }



    /**
     * Customer approve
     */
    public function approve(
        OrderItemChange $change
    ) {

        $result = $this->changeService
            ->approve($change);


        return response()->json([
            'message' =>
            'Perubahan order disetujui.',

            'data' =>
            $result,
        ]);
    }



    /**
     * Customer reject
     */
    public function reject(
        OrderItemChange $change
    ) {

        $result = $this->changeService
            ->reject($change);


        return response()->json([
            'message' =>
            'Perubahan order ditolak.',

            'data' =>
            $result,
        ]);
    }

    public function replace(
        Request $request,
        OrderItem $orderItem
    ) {

        $item = $this->changeService
            ->replace(
                $orderItem,
                $request->validate([

                    'menu_variant_id' => [
                        'required',
                        'exists:menu_variants,id',
                    ],

                    'quantity' => [
                        'required',
                        'numeric',
                        'min:0.001',
                    ],

                    'reason' => [
                        'required',
                        'string',
                    ],

                ])
            );


        return response()->json([
            'message' =>
            'Item berhasil diganti.',

            'data' =>
            $item,
        ]);
    }
}
