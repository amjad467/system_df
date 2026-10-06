<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedReceiptNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'cancellation_reason',
        'blocked_by',
        'transaction_id',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
