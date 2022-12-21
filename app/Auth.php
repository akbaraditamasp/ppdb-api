<?php

namespace Siluet;

use Model\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    public static ?User $user = null;

    public static function makeToken(User $user, $notif)
    {
        $payload = [
            "id" => $user->id
        ];

        $jwt = JWT::encode($payload, $_ENV["JWT_KEY"] ?? "123456", 'HS256');

        $user->notif = $notif;
        $user->save();

        return $jwt;
    }

    public static function validate()
    {
        try {
            $token = App::$request->getHeaderLine("Authorization");
            $token = explode(" ", $token);

            $decoded = JWT::decode($token[1] ?? "", new Key($_ENV["JWT_KEY"] ?? "123456", 'HS256'));
            $decoded = (array) $decoded;

            static::$user = User::findOrFail($decoded["id"]);
        } catch (\Exception $e) {
            App::$response->getBody()->write(json_encode([
                "error" => "Unauthorized"
            ]));
            App::$response = App::$response->withStatus(401)->withHeader("Content-Type", "application/json");
            App::finish();
        }
    }
}
