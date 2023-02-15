<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\NonEloquent\MoneyInterface;
use App\Models\Product;
use App\Models\ProductInterface;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class CartService implements CartInterface
{
    private User $user;
    private MoneyInterface $money;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->money = App::make(MoneyInterface::class);
    }

    public function addProduct(ProductInterface $product): self
    {
        $cart = Cart::query()->where([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'status' => null,
        ]);

        if ($cart->exists()) {
            $cart->update([
                'count' => $cart->get()->value('count') + 1,
            ]);
        } else {
            $cart = (new Cart())->fill([
                'count' => 1,
            ]);
            $cart->user()->associate($this->user);
            $cart->product()->associate($product);
            $cart->save();
        }
        return $this;
    }

    public function removeProduct(ProductInterface $product): self
    {
        $cart = Cart::query()->where([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'status' => null,
        ]);

        if ($cart->exists()) {
            $count = $cart->get()->value('count');
            if ($count <= 1) {
                $cart->delete();
            } else {
                $cart->update([
                    'count' => $count - 1,
                ]);
            }
        }
        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->user->productsInCart()->get();
    }

    public function getSubtotal(): MoneyInterface
    {
        $subTotal = new $this->money(0, 0);
        foreach ($this->user->productsInCart()->get() as $product) {
            /** @var Product $product */

            $subTotal->setEuros(
                $subTotal->getEuros() +
                $product->getPrice()->getEuros() * $product->count
            );

            $subTotal->setCents(
                $subTotal->getCents() +
                $product->getPrice()->getCents() * $product->count
            );
        }

        return $this->moneyCentsToEuros($subTotal);
    }

    public function getVatAmount(): MoneyInterface
    {
        $vatTotal = new $this->money(0, 0);
        foreach ($this->user->productsInCart()->get() as $product) {
            /** @var Product $product */

            $vat = round(
                (
                    $product->getPrice()->getEuros() +
                    $product->getPrice()->getCents() / 100
                ) * $product->getVatRate(),
                2
            );
            $vatEuros = floor($vat);

            $vatTotal->setEuros(
                $vatTotal->getEuros() + $vatEuros * $product->count
            );
            $vatTotal->setCents(
                $vatTotal->getCents() + round(($vat - $vatEuros) * 100 * $product->count)
            );
        }

        return $this->moneyCentsToEuros($vatTotal);
    }

    public function getTotal(): MoneyInterface
    {
        $total = new $this->money(0, 0);
        foreach ($this->user->productsInCart()->get() as $product) {
            /** @var Product $product */

            $vat = round(
                (
                    $product->getPrice()->getEuros() +
                    $product->getPrice()->getCents() / 100
                ) * $product->getVatRate(),
                2
            );
            $vatEuros = floor($vat);

            $total->setEuros(
                $total->getEuros() +
                $product->getPrice()->getEuros() * $product->count +
                $vatEuros * $product->count
            );
            $total->setCents(
                $total->getCents() +
                $product->getPrice()->getCents() * $product->count +
                round(($vat - $vatEuros) * 100 * $product->count)
            );
        }

        return $this->moneyCentsToEuros($total);
    }

    public function checkout(): self
    {
        $this->user->decrementBalance($this->getTotal());

        foreach ($this->user->productsInCart()->get() as $product) {
            $product->update([
                'available' => $product->available - $product->count,
            ]);
        }

        $this->user->carts()
            ->where('status', '=', null)
            ->update([
                'status' => 'bought',
            ]);

        return $this;
    }

    private function moneyCentsToEuros(MoneyInterface $money): MoneyInterface
    {
        if ($money->getCents() > 99) {
            $euros = floor($money->getCents() / 100);
            $money->setEuros($money->getEuros() + $euros);
            $money->setCents($money->getCents() - $euros * 100);
        }
        return $money;
    }
}
