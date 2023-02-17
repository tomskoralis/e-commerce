<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductInterface;
use App\Services\StockInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private ProductInterface $productModel;
    private StockInterface $stockService;
    private const PRODUCTS_PER_PAGE = 20;

    public function __construct(
        ProductInterface $productModel,
        StockInterface   $stockService
    )
    {
        $this->productModel = $productModel;
        $this->stockService = new $stockService();
    }

    public function index(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $products = $this->stockService
                ->getProducts()
                ->paginate(self::PRODUCTS_PER_PAGE);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't get the products.",
            ], 400);
        }

        $productCollection = ProductResource::collection($products);
        $productCollection::wrap('products');

        return $productCollection;
    }

    public function show(string $product): JsonResponse|ProductResource
    {
        $product = $this->productModel::query()->find($product);

        if (!isset($product)) {
            return response()->json([
                'message' => "Couldn't find the product."
            ], 400);
        }

        return new ProductResource($product);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'unique:' . Product::class],
            'available' => ['required', 'numeric', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0.01', 'regex:/^\d*(?:\.\d{1,2})?$/'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:99.99', 'regex:/^\d*(?:\.\d{1,2})?$/'],
        ]);

        $price = $request->get('price');
        $priceEuros = floor($price);

        $product = (new $this->productModel([
            'name' => $request->get('name'),
            'available' => $request->get('available'),
            'price_euros' => $priceEuros,
            'price_cents' => round(($price - $priceEuros) * 100),
            'vat_rate' => $request->get('vat_rate'),
        ]));

        try {
            $this->stockService->addProduct($product);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't add the product.",
            ], 400);
        }

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, string $product): JsonResponse|ProductResource
    {
        $request->validate([
            'name' => ['required', 'string', Rule::unique(Product::class)->ignore($product)],
            'available' => ['required', 'numeric', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0.01', 'regex:/^\d*(?:\.\d{1,2})?$/'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:99.99', 'regex:/^\d*(?:\.\d{1,2})?$/'],
        ]);

        $price = $request->get('price');
        $priceEuros = floor($price);

        $productToEdit = $this->productModel::query()->find($product);

        if (!isset($productToEdit)) {
            return response()->json([
                'message' => "Couldn't find the product to update."
            ], 400);
        }

        try {
            $productToEdit->update([
                'name' => $request->get('name'),
                'available' => $request->get('available'),
                'price_euros' => $priceEuros,
                'price_cents' => ($price - $priceEuros) * 100,
                'vat_rate' => $request->get('vat_rate'),
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't update the product.",
            ], 400);
        }

        return new ProductResource($this->productModel::query()->find($product));
    }

    public function destroy(string $product): JsonResponse
    {
        $product = $this->productModel::query()->find($product);

        if (!isset($product)) {
            return response()->json([
                'message' => "Couldn't find the product to remove."
            ], 400);
        }

        try {
            $this->stockService->removeProduct($product);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't remove the product.",
            ], 400);
        }

        return response()->json([], 204);
    }


    public function getOutOfStock(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $products = $this->stockService->getOutOfStock()
                ->paginate(self::PRODUCTS_PER_PAGE);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Couldn't get the products.",
            ], 400);
        }

        $productCollection = ProductResource::collection($products);
        $productCollection::wrap('products');

        return $productCollection;
    }
}
