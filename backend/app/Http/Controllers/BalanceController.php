<?php

namespace App\Http\Controllers;

use App\Models\NonEloquent\MoneyInterface;
use App\Models\User;
use Illuminate\Database\QueryException;
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
            'balance' => $user->getBalance()->getAmountFormatted(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'regex:/^\d*(?:\.\d{1,2})?$/'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $amount = $request->get('amount');
        $amountEuros = floor($amount);

        $money = new $this->money(
            (round(($amount - $amountEuros) * 100)),
            $amountEuros
        );

        try {
            $user->incrementBalance($money);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't updated the balance.",
            ], 400);
        }

        return response()->json([
            'message' => 'Successfully updated the balance.',
            'balance' => $user->getBalance()->getAmountFormatted(),
        ]);
    }
}
