<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingRule extends Model
{
    protected $fillable = ['weekday','start_time','end_time','slot_interval'];
}
