<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Payment extends Model
{
    use LogsActivity;
    protected $fillable = ['order_id', 'purchase_id', 'method', 'amount', 'status', 'date', 'note'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}