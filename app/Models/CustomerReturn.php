<?php
// app/Models/CustomerReturn.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class CustomerReturn extends Model
{
    use LogsActivity;
    protected $fillable = ['customer_id', 'sale_id', 'order_id', 'status', 'date', 'note'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function sale()     { return $this->belongsTo(Sale::class); }
    public function order()   { return $this->belongsTo(Order::class); }
    public function details()  { return $this->hasMany(CustomerReturnDetail::class); }
}
