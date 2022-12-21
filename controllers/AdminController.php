<?php

namespace Controller;

use Model\Admin;
use Respect\Validation\Validator as v;
use Siluet\AdminAuth;
use Siluet\App;
use Siluet\Validation;

class AdminController
{
    public function login()
    {
        ["username" => $username, "password" => $password] = Validation::validate([
            "query" => [
                "username" => v::stringType()->notEmpty(),
                "password" => v::stringType()->notEmpty()
            ]
        ]);
        App::controller(function () use ($username, $password) {
            /**
             * @var Admin
             */
            $user = Admin::where("username", $username)->firstOrFail();
            if (!password_verify($password, $user->password)) {
                App::$response = App::$response->withStatus(401);
                return ["error" => "Unauthorized"];
            }

            $token = AdminAuth::makeToken($user);

            return $user->toArray() + ["token" => $token];
        });
    }
}
