<?php

namespace App\Services;

use App\Models\NonEloquent\MoneyInterface;
use App\Models\ProductInterface;
use Illuminate\Support\Collection;


interface CartInterface
{
    public function addProduct(ProductInterface $product): self;

    public function removeProduct(ProductInterface $product): self;

    public function getProducts(): Collection;

    public function getSubtotal(): MoneyInterface;

    public function getVatAmount(): MoneyInterface;

    public function getTotal(): MoneyInterface;
}
