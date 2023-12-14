<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // Retrieve a user record by its email
        $merchantUser = User::byEmail($email)->first();
        
        if ($merchantUser && $merchantUser->merchant()->exists()) {
            throw new AffiliateCreateException('This email is already associated with a merchant.');
        }

        // Check if this email is already in use for another affiliate associated with the same merchant
        $affiliateUser = Affiliate::byUserAndMerchant(optional($merchantUser)->id, $merchant->id)->first();
        if ($affiliateUser) {
            throw new AffiliateCreateException('This email is already in use for another affiliate associated with the same merchant.');
        }

        $user = $merchantUser ?? User::create([
            'email' => $email,
            'name' => $name,
            'type' => 'default_type_value'
        ]);

        $affiliate = Affiliate::updateOrCreate(
            ['user_id' => $user->id, 'merchant_id' => $merchant->id],
            ['commission_rate' => $commissionRate, 'discount_code' => $this->apiService->createDiscountCode($merchant)['code']]
        );

        // Send the AffiliateCreated mail
        $mail = new AffiliateCreated($affiliate);
        Mail::send($mail);

        return $affiliate;
    }
}
