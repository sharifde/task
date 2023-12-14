<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['api_key'],
            'type' => User::TYPE_MERCHANT,
        ]);

        // create merchant
        $merchant = Merchant::create([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
            'user_id' => $user->id,
        ]);

        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        if($user)
        {
            $user->update([
                'email' => $data['email'],
                'password' => $data['api_key']
            ]);
        }
        
        if($user->merchant)
        {
            $user->merchant->update([
                'domain' => $data['domain'],
                'display_name' => $data['name'],
            ]);
        }
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // Retrieve a user record by its email
        $user = User::byEmail($email)->first();
        if (!$user) 
        {
            return null;
        }
        
        // Retrieve a merchant record by the user's ID
        $merchant = Merchant::byUserId($user->id)->first();
        if (!$merchant) 
        {
            return null;
        }

        return $merchant;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        foreach ($affiliate->orders as $key => $order) 
        {
            if($order->payout_status == Order::STATUS_UNPAID)
            {
                dispatch(new PayoutOrderJob($order));
            }
        }
    }
}
