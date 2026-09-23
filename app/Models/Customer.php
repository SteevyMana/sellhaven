<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Customer extends Model
{
    use LogsActivity;
    protected $fillable = ['name', 'email', 'phone', 'city', 'status'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function customerReturns()
    {
        return $this->hasMany(CustomerReturn::class);
    }
}
