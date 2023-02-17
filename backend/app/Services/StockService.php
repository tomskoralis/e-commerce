<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

class StockService implements StockInterface
{
    private ProductInterface $productModel;

    public function __construct()
    {
        $this->productModel = App::make(ProductInterface::class);
    }

    public function addProduct(ProductInterface $product): self
    {
        $product->save();
        return $this;
    }

    public function removeProduct(ProductInterface $product): self
    {
        Cart::query()->where(
            'product_id', $product->id
        )->delete();

        Product::query()->where([
            'name' => $product->getName()
        ])->delete();

        return $this;
    }

    public function getProducts(): Builder
    {
        return $this->productModel::query()
            ->where('available', '>', 0)
            ->orderByDesc('id');
    }

    public function getOutOfStock(): Builder
    {
        return $this->productModel::query()
            ->where('available', '=', 0)
            ->orderByDesc('id');
    }
}
