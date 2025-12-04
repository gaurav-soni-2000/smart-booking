<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'service_id','date','start_time','end_time','client_email','client_name','status'
    ];

    public function service() {
        return $this->belongsTo(Service::class);
    }
}
