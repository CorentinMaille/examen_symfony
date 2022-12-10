<?php

namespace App\Controller;

use App\Entity\Cart;

abstract class OrderUtils
{
    /**
     * Compute the totalPrice of an order (Cart)
     * @param Cart $order
     * @return float|int
     */
    public static function computeTotalPriceOne(Cart $order): float|int
    {
        $totalPrice = 0;
        $unreachableProduct = false;
        foreach ($order->getCartContents() as $orderLine) {
            if ($orderLine->getProduct() == null) {
                $unreachableProduct = true;
            }
            $totalPrice += $orderLine->getQuantity() *  $orderLine->getProduct()?->getPrice();
        }

        return $unreachableProduct ? -1 : $totalPrice;
    }

    /**
     * Compute the totalPrice of each given order (Cart)
     * @param array $orders
     * @return array
     */
    public static function computeTotalPriceMany(array $orders): array
    {
        foreach ($orders as $order) {
            $order->setTotalPrice(
                OrderUtils::computeTotalPriceOne($order)
            );
        }
        return $orders;
    }
}
