<?php
//*************************************************************************************************
// FileName : finance.php
// FilePath : core/
// Author   : Christian Marty
// Date		: 22.12.2025
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace Finance;

enum Currency
{
    case Unknown;
    case CHF;
    case USD;
    case EUR;
    case GBP;

    public function toCode(): string | null
    {
        return match ($this) {
            Currency::CHF => "CHF",
            Currency::USD => "USD",
            Currency::EUR => "EUR",
            Currency::GBP => "GBP",
            default => null
        };
    }

    public function jsonSerialize(): string
    {
        return $this->toCode();
    }

    static function fromCode(string $code): Currency
    {
        return match (strtoupper($code)) {
            "CHF" =>  Currency::CHF,
            "USD" => Currency::USD,
            "EUR" => Currency::EUR,
            "GBP" => Currency::GBP,
            default => Currency::Unknown
        };
    }
}