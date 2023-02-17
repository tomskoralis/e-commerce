<?php

namespace App\Http\Resources;

use App\Models\NonEloquent\MoneyInterface;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
            'price' => $this->getPrice()->getAmountFormatted(),
            'vat_rate' => rtrim(rtrim($this->getVatRate() * 100, '0'), '.'),
            'vat' => $this->getVat()->getAmountFormatted(),
        ];

        $count = $this->count;
        if ($count) {
            $product['count'] = $count;
        }
        $boughtAt = $this->bought_at;
        if ($boughtAt) {
            $product['bought_at'] = Carbon::make($boughtAt)->format('d/m/Y G:i');
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
}
