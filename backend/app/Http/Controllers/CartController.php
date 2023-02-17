<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Http\Resources\ProductResource;
use App\Models\NonEloquent\MoneyInterface;
use App\Models\ProductInterface;
use App\Models\User;
use App\Services\CartInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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

    public function index(Request $request): JsonResponse|CartResource
    {
        try {
            $carts = new $this->cartService($request->user());
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't get the cart.",
            ], 400);
        }
        return new CartResource($carts);
    }

    public function store(Request $request): JsonResponse
    {
        $product = $this->productModel::query()
            ->find($request->get('id'));

        if (!isset($product)) {
            return response()->json([
                'message' => "Couldn't find the product to add to the cart."
            ], 400);
        }

        $cart = (new $this->cartService($request->user()));

        try {
            $cart->addProduct($product);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't add product.",
            ], 400);
        }

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request): JsonResponse|CartResource
    {
        $product = $this->productModel::query()
            ->find($request->get('id'));

        if (!isset($product)) {
            return response()->json([
                'message' => "Couldn't find the product to remove from the cart."
            ], 400);
        }

        $cart = (new $this->cartService($request->user()));

        try {
            $cart->removeProduct($product);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't remove product.",
            ], 400);
        }

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

        try {
            (new $this->cartService($user))->checkout();
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't checkout.",
            ], 400);
        }

        return response()->json([
            'message' => 'Successfully bought the items in the cart.'
        ]);
    }

    public function indexOrders(Request $request): JsonResponse|AnonymousResourceCollection
    {
        try {
            $carts = new $this->cartService($request->user());
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't get the orders.",
            ], 400);
        }

        $orders = ProductResource::collection($carts->getOrders());
        $orders::wrap('orders');
        return $orders;
    }

    private function moneyToFloat(MoneyInterface $money): float
    {
        return round($money->getEuros() + $money->getCents() / 100, 2);
    }
}
