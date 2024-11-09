<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalOrder extends Model
{
    use HasFactory;

    protected $table = "final_orders";
    protected $guarded = [];
}
