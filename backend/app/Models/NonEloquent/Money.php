<?php

namespace App\Models\NonEloquent;

class Money implements MoneyInterface
{
    private ?int $cents;
    private ?int $euros;

    public function __construct(
        ?int $cents = 0,
        ?int $euros = 0,
    )
    {
        $this->cents = $cents;
        $this->euros = $euros;
    }

    public function getCents(): int
    {
        return $this->cents;
    }

    public function setCents(int $cents): self
    {
        $this->cents = $cents;
        return $this;
    }

    public function getEuros(): int
    {
        return $this->euros;
    }

    public function setEuros(int $euros): self
    {
        $this->euros = $euros;
        return $this;
    }
}
