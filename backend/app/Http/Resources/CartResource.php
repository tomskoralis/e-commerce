<?php

namespace App\Http\Resources;

use App\Models\NonEloquent\MoneyInterface;
use App\Services\CartService;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public static $wrap = 'cart';

    public function toArray($request): array
    {
        /** @var CartResource|CartService $this */
        return [
            'products' => ProductResource::collection($this->getProducts()),
            'subtotal' => $this->formatMoney($this->getSubtotal()),
            'vat' => $this->formatMoney($this->getVatAmount()),
            'total' => $this->formatMoney($this->getTotal()),
        ];
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
