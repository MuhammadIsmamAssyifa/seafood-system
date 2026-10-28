<?php

namespace App\Services;

use App\Enums\OrderItemChangeStatus;
use App\Enums\OrderItemChangeType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderBatchStatus;
use App\Models\MenuVariant;
use App\Models\OrderItem;
use App\Models\OrderItemChange;
use App\Services\OrderCalculationService;
use Illuminate\Support\Facades\DB;

class OrderItemChangeService
{
    public function __construct(
        protected OrderCalculationService $calculationService
    ) {}

    /**
     * Membuat request perubahan
     */
    public function create(
        OrderItem $item,
        array $data
    ): OrderItemChange {

        return DB::transaction(function () use ($item, $data) {


            if (
                in_array(
                    $item->status,
                    [
                        OrderItemStatus::Preparing,
                        OrderItemStatus::Ready,
                        OrderItemStatus::Served,
                        OrderItemStatus::Cancelled,
                    ]
                )
            ) {

                throw new \DomainException(
                    'Item yang sudah diproses tidak dapat diubah.'
                );
            }



            return OrderItemChange::create([

                'order_item_id' =>
                $item->id,


                'type' =>
                $data['type'],


                'old_quantity' =>
                $item->quantity,


                'old_price' =>
                $item->price_snapshot,


                'new_quantity' =>
                $data['quantity'] ?? null,


                'new_price' =>
                $data['price'] ?? null,


                'reason' =>
                $data['reason'],


                'status' =>
                OrderItemChangeStatus::Pending,


                'created_by' =>
                auth()->id(),

            ]);
        });
    }



    /**
     * Customer approve perubahan
     */
    public function approve(
        OrderItemChange $change
    ) {

        return DB::transaction(function () use ($change) {


            if (
                $change->status !==
                OrderItemChangeStatus::Pending
            ) {

                throw new \DomainException(
                    'Perubahan sudah diproses.'
                );
            }


            $item = $change->orderItem;



            switch ($change->type) {


                case OrderItemChangeType::QuantityChange:


                    $item->update([

                        'quantity' =>
                        $change->new_quantity,


                        'confirmed_quantity' =>
                        $change->new_quantity,


                        'price_snapshot' =>
                        $change->new_price,


                        'subtotal' =>
                        $change->new_quantity
                            *
                            $change->new_price,

                    ]);

                    break;



                case OrderItemChangeType::Cancel:


                    $item->update([

                        'status' =>
                        OrderItemStatus::Cancelled,

                        'subtotal' =>
                        0,

                    ]);

                    break;



                case OrderItemChangeType::Replace:


                    /*
                    Replace akan dibuat sebagai
                    item baru.
                    */

                    break;
            }



            $change->update([

                'status' =>
                OrderItemChangeStatus::Approved

            ]);

            $this->calculationService
                ->recalculate(
                    $item->order
                );



            return $change->fresh();
        });
    }



    /**
     * Customer menolak
     */
    public function reject(
        OrderItemChange $change
    ) {

        $change->update([

            'status' =>
            OrderItemChangeStatus::Rejected

        ]);


        return $change->fresh();
    }

    public function replace(
        OrderItem $oldItem,
        array $data
    ) {

        return DB::transaction(function () use ($oldItem, $data) {


            if (
                in_array(
                    $oldItem->status,
                    [
                        OrderItemStatus::Preparing,
                        OrderItemStatus::Ready,
                        OrderItemStatus::Served,
                    ]
                )
            ) {

                throw new \DomainException(
                    'Item sudah diproses dan tidak dapat diganti.'
                );
            }



            /*
        |--------------------------------------------------------------------------
        | Cancel item lama
        |--------------------------------------------------------------------------
        */

            $oldItem->update([
                'status' =>
                OrderItemStatus::Cancelled,
            ]);



            /*
        |--------------------------------------------------------------------------
        | Ambil menu baru
        |--------------------------------------------------------------------------
        */

            $menuVariant = MenuVariant::with('menu')
                ->findOrFail(
                    $data['menu_variant_id']
                );



            /*
        |--------------------------------------------------------------------------
        | Buat item pengganti
        |--------------------------------------------------------------------------
        */

            $newItem = OrderItem::create([

                'order_batch_id' =>
                $oldItem->order_batch_id,


                'menu_variant_id' =>
                $menuVariant->id,


                'menu_name_snapshot' =>
                $menuVariant->menu->name,


                'variant_name_snapshot' =>
                $menuVariant->name,


                'price_snapshot' =>
                $menuVariant->price,


                'sales_unit' =>
                $menuVariant->sales_unit,


                'quantity' =>
                $data['quantity'],


                'requested_quantity' =>
                $data['quantity'],


                'confirmed_quantity' =>
                null,


                'subtotal' =>
                $menuVariant->price *
                    $data['quantity'],


                'inventory_quantity' =>
                $data['quantity'] *
                    $menuVariant->conversion_rate,


                'status' =>
                OrderItemStatus::Waiting,

            ]);



            /*
        |--------------------------------------------------------------------------
        | Catat perubahan
        |--------------------------------------------------------------------------
        */

            OrderItemChange::create([

                'order_item_id' =>
                $oldItem->id,


                'type' =>
                OrderItemChangeType::Replace,


                'old_quantity' =>
                $oldItem->quantity,


                'old_price' =>
                $oldItem->price_snapshot,


                'new_quantity' =>
                $data['quantity'],


                'new_price' =>
                $menuVariant->price,


                'replacement_item_id' =>
                $newItem->id,


                'status' =>
                OrderItemChangeStatus::Approved,


                'reason' =>
                $data['reason'],


                'created_by' =>
                auth()->id(),

            ]);



            $this->calculationService
                ->recalculate(
                    $oldItem->order
                );


            return $newItem;
        });
    }
}
