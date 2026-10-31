<?php

namespace App\Enums;

enum WasteReason: string
{
    case Expired = 'expired';
    case Damaged = 'damaged';
    case CookingLoss = 'cooking_loss';
    case WrongPreparation = 'wrong_preparation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Expired => 'Kadaluarsa / Expired',
            self::Damaged => 'Rusak / Busuk',
            self::CookingLoss => 'Susut Masak / Handling',
            self::WrongPreparation => 'Salah Olah / Masak',
            self::Other => 'Lainnya',
        };
    }
}

