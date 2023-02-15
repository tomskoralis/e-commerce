<?php

namespace App\Models;

use App\Models\NonEloquent\MoneyInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    private MoneyInterface $money;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->money = App::make(MoneyInterface::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function productsInCart(): Builder
    {
        return Product::query()
            ->where([
                'user_id' => $this->id,
                'status' => null,
            ])
            ->join('carts', 'products.id', '=', 'carts.product_id')
            ->selectRaw('products.*, carts.count');
    }

    public function incrementBalance(MoneyInterface $money): self
    {
        $cents = $this->balance_cents + $money->getCents();
        if ($cents > 99) {
            $euros = floor($cents / 100);
            $money->setEuros($money->getEuros() + $euros);
            $money->setCents($money->getCents() - $euros * 100);
        }

        $this->increment('balance_euros', round($money->getEuros()));
        $this->increment('balance_cents', round($money->getCents()));

        return $this;
    }

    public function decrementBalance(MoneyInterface $money): self
    {
        $centsDifference = $this->balance_cents - $money->getCents();
        if ($centsDifference < 0) {
            $eurosDifference = ceil(abs($centsDifference / 100));
            $money->setEuros($money->getEuros() + $eurosDifference);
            $money->setCents($money->getCents() - $eurosDifference * 100);
        }

        $this->decrement('balance_euros', round($money->getEuros()));
        $this->decrement('balance_cents', round($money->getCents()));

        return $this;
    }

    public function getBalance(): MoneyInterface
    {
        return new $this->money($this->balance_cents, $this->balance_euros);
    }
}
