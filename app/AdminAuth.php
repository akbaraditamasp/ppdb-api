<?php

namespace Siluet;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Model\Admin;

class AdminAuth
{
    public static ?Admin $user = null;

    public static function makeToken(Admin $user)
    {
        $payload = [
            "admin" => $user->id
        ];

        $jwt = JWT::encode($payload, $_ENV["JWT_KEY"] ?? "123456", 'HS256');

        return $jwt;
    }

    public static function validate()
    {
        try {
            $token = App::$request->getHeaderLine("Authorization");
            $token = explode(" ", $token);

            $decoded = JWT::decode($token[1] ?? "", new Key($_ENV["JWT_KEY"] ?? "123456", 'HS256'));
            $decoded = (array) $decoded;

            static::$user = Admin::findOrFail($decoded["admin"]);
        } catch (\Exception $e) {
            App::$response->getBody()->write(json_encode([
                "error" => "Unauthorized"
            ]));
            App::$response = App::$response->withStatus(401)->withHeader("Content-Type", "application/json");
            App::finish();
        }
    }
}
