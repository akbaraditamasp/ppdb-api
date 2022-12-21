<?php

namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class User extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "username",
        "password",
        "notif"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function result()
    {
        return $this->hasOne(Result::class);
    }
}
