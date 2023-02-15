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
            'subtotal' => $this->getSubtotal()->getAmountFormatted(),
            'vat' => $this->getVatAmount()->getAmountFormatted(),
            'total' => $this->getTotal()->getAmountFormatted(),
        ];
    }
}
