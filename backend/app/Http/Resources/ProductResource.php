<?php

namespace App\Http\Resources;

use App\Models\NonEloquent\MoneyInterface;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = 'product';

    public function toArray($request): array
    {
        /** @var ProductResource|Product $this */

        $product = [
            'id' => $this->id,
            'name' => $this->getName(),
            'available' => $this->getAvailable(),
            'price' => $this->formatMoney($this->getPrice()),
            'vat_rate' => rtrim(rtrim($this->getVatRate() * 100, '0'), '.'),
            'vat' => $this->formatMoney($this->getVat()),
        ];

        $count = $this->count;
        if ($count) {
            $product['count'] = $count;
        }
        return $product;
    }

    private function getVat(): MoneyInterface
    {
        /** @var ProductResource|Product $this */
        $vat = round(
            ($this->getPrice()->getEuros() + $this->getPrice()->getCents() / 100) * $this->getVatRate(),
            2
        );
        $vatEuros = floor($vat);
        $vatMoney = $this->getPrice();
        $vatMoney->setEuros($vat);
        $vatMoney->setCents(round(($vat - $vatEuros) * 100));
        return $vatMoney;
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
