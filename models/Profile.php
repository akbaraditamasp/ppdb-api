<?php

namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Profile extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "fullname",
        "photo",
        "gender",
        "nisn",
        "birth_of_place",
        "birthday",
        "religion",
        "address",
        "phone",
        "school_origin",
        "parent_status",
        "parent_name",
        "parent_nik",
        "kk_number",
        "parent_place_of_birth",
        "parent_birthday",
        "profession",
        "income",
        "parent_address",
        "user_id",
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
        "birthday" => "date",
        "parent_birthday" => "date",
        "income" => "int"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
