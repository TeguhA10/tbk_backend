<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'coa_id',
        'description',
        'debit',
        'credit',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'debit' => 'float',
        'credit' => 'float',
    ];

    public function coa()
    {
        return $this->belongsTo(Coa::class);
    }
}
