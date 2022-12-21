<?php

namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Result extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "user_id",
        "result"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     'password',
    // ];

    protected $casts = [
        "result" => "bool",
        "user_id" => "int"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
