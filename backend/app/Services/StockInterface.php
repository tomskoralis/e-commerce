<?php

namespace App\Services;

use App\Models\ProductInterface;
use Illuminate\Database\Eloquent\Builder;

interface StockInterface
{
    public function addProduct(ProductInterface $product): self;

    public function removeProduct(ProductInterface $product): self;

    public function getProducts(): Builder;
}
