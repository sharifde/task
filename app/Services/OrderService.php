<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        
        if ($data['order_id'] == Order::hasOrderId($data['order_id'])->first()) 
        {
            return;
        }

        // Scope functions
        // Retrieve a merchant record by its domain
        $merchant = Merchant::byDomain($data['merchant_domain'])->first();

        $userAffiliate = $merchant->user->affiliate;
        if (!$userAffiliate) {
            // Create a new affiliate if not already associated with the customer_email
            $userAffiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);
        }

        // Retrieve an affiliate record by the merchant's ID
        $affiliate = Affiliate::byMerchantId($merchant->id)->first();
    
        // Check if an order with the provided order_id already exists
        $existingOrder = Order::hasExternalOrderId($data['order_id'])->exists();
        if ($existingOrder) 
        {
            // Ignore duplicate order
            return;
        }

        // Calculate the commission owed
        $commissionOwed = $data['subtotal_price'] * $affiliate->commission_rate;
        // Create a new order
       $order = Order::create([
            'subtotal' => $data['subtotal_price'],
            'affiliate_id' => $affiliate->id,
            'merchant_id' => $merchant->id,
            'commission_owed' => $commissionOwed,
            'external_order_id' => $data['order_id'],
        ]);
    }
}
