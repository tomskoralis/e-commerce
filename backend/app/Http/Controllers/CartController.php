<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Http\Resources\ProductResource;
use App\Models\NonEloquent\MoneyInterface;
use App\Models\ProductInterface;
use App\Models\User;
use App\Services\CartInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private ProductInterface $productModel;
    private CartInterface $cartService;

    public function __construct(
        ProductInterface $productModel,
        CartInterface    $cartService,
    )
    {
        $this->productModel = $productModel;
        $this->cartService = $cartService;
    }

    public function index(Request $request): CartResource
    {
        return new CartResource(
            new $this->cartService($request->user())
        );
    }

    public function store(Request $request): JsonResponse
    {
        $productId = $request->get('id');
        $product = $this->productModel::query()->find($productId);

        if (!isset($product)) {
            return response()->json([
                'message' => "Couldn't find the product to add to the cart."
            ], 400);
        }

        $cart = (new $this->cartService($request->user()));
        $cart->addProduct($product);

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request): JsonResponse|CartResource
    {
        $productId = $request->get('id');
        $product = $this->productModel::query()->find($productId);

        if (!isset($product)) {
            return response()->json([
                'message' => "Couldn't find the product to remove from the cart."
            ], 400);
        }

        $cart = (new $this->cartService($request->user()));
        $cart->removeProduct($product);

        return new CartResource($cart);
    }

    public function checkout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $cart = (new $this->cartService($user));

        if ($cart->getProducts()->isEmpty()) {
            return response()->json([
                'message' => 'No products in the cart!',
            ], 400);
        }

        $balance = $user->getBalance();
        $total = $cart->getTotal();

        if ($this->moneyToFloat($total) > $this->moneyToFloat($balance)) {
            return response()->json([
                'message' => 'Not enough balance!',
                'balance' => $this->moneyToFloat($balance),
                'total' => $this->moneyToFloat($total),
            ], 400);
        }

        $products = $user->productsInCart()->get();
        foreach ($products as $product) {
            if ($product->count > $product->available) {
                return response()->json([
                    'message' => 'Not enough products available!',
                    'product' => new ProductResource($product),
                    'available' => $product->available
                ], 400);
            }
        }

        (new $this->cartService($user))
            ->checkout();

        return response()->json([
            'message' => 'Successfully bought the items in the cart.'
        ]);
    }

    private function moneyToFloat(MoneyInterface $money): float
    {
        return round($money->getEuros() + $money->getCents() / 100, 2);
    }
}
