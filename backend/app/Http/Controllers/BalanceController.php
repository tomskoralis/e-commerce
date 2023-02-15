<?php

namespace App\Http\Controllers;

use App\Models\NonEloquent\MoneyInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    private MoneyInterface $money;

    public function __construct(MoneyInterface $money)
    {
        $this->money = $money;
    }

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'balance' => $this->formatMoney($user->getBalance()),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $amount = $request->get('amount');
        $amountEuros = floor($amount);

        $money = new $this->money(
            (round(($amount - $amountEuros) * 100)),
            $amountEuros
        );

        $user->incrementBalance($money);

        return response()->json([
            'message' => 'Successfully updated the balance.',
            'balance' => $this->formatMoney($user->getBalance()),
        ]);
    }

    private function formatMoney(MoneyInterface $money): string
    {
        return number_format(
            round($money->getEuros() + $money->getCents() / 100, 2),
            2,
            '.',
            ''
        );
    }
}
