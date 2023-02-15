<?php

namespace App\Models;

use App\Models\NonEloquent\MoneyInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Product extends Model implements ProductInterface
{
    use HasFactory;

    protected $fillable = [
        'name',
        'available',
        'price_euros',
        'price_cents',
        'vat_rate',
    ];

    private MoneyInterface $money;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->money = App::make(MoneyInterface::class);
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setAvailable(int $available): self
    {
        $this->available = $available;
        return $this;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }

    public function setPrice(MoneyInterface $price): self
    {
        if ($price->getCents() > 99) {
            $euros = floor($price->getCents() / 100);
            $price->setEuros($price->getEuros() + $euros);
            $price->setCents($price->getCents() - $euros * 100);
        }

        $this->price_cents = round($price->getCents());
        $this->price_euros = round($price->getEuros());
        return $this;
    }

    public function getPrice(): MoneyInterface
    {
        return new $this->money($this->price_cents, $this->price_euros);
    }

    public function setVatRate(float $vat): self
    {
        $this->vatRate = $vat;
        return $this;
    }

    public function getVatRate(): float
    {
        return $this->vat_rate / 100;
    }
}
