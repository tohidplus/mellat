<?php

namespace Tohidplus\Mellat\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MellatLog extends Model
{
    protected $fillable=[
        'ref_id',
        'amount',
        'order_id',
        'payer_id',
        'sale_order_id',
        'sale_reference_id',
        'message',
        'res_code',
        'status',
    ];

    #-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#
    # Scopes
    #-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#
    public function scopeSuccessful(Builder $builder)
    {
        return $builder->where('status','successful');
    }

    public function scopeUnsuccessful(Builder $builder)
    {
        return $builder->where('status','unsuccessful');
    }

    public function scopePending(Builder $builder)
    {
        return $builder->where('status','pending');
    }

}
