<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    protected $guarded = [];

    public function requiresBooklet(): bool
    {
        if (in_array((int)$this->id, [1, 2, 3, 4, 7])) {
            return true;
        }

        $name = $this->name_kurdish ?? '';
        if (str_contains($name, 'دەرهێنان') || 
            str_contains($name, 'تازە') || str_contains($name, 'تازه') ||
            str_contains($name, 'ناوگۆڕین') || 
            str_contains($name, 'دەفتەر گۆڕین') || str_contains($name, 'دەفتەرگۆڕین')) {
            return true;
        }

        return false;
    }
}
