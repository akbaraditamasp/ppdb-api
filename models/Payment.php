<?php

namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Payment extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "user_id",
        "amount",
        "inv_link",
        "is_paid"
    ];

    protected $casts = [
        "is_paid" => "bool"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     'password',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
