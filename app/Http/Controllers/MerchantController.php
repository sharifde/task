<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $from = $request->from;
        $to = $request->to;

        $count = Order::dateRangeCount($from, $to);
        $commission_owed = Order::commissionOwed($from, $to);
        $revenue = Order::totalRevenue($from, $to);

        $response = [
            'count' => $count,
            'commissions_owed' => $commission_owed,
            'revenue' => $revenue
        ];

        return response()->json($response);
    }
}
