<?php

namespace Controller;

use Carbon\Carbon;
use Dompdf\Options;
use EndyJasmi\Cuid;
use Model\Payment;
use Model\Profile;
use Model\Result;
use Model\User;
use Respect\Validation\Validator as v;
use Siluet\AdminAuth;
use Siluet\App;
use Siluet\Auth;
use Siluet\Eloquent;
use Siluet\Notif;
use Siluet\Validation;
use Siluet\Xendit as SiluetXendit;
use Xendit\Invoice;
use Xendit\Xendit;

class UserController
{
    public function login()
    {
        ["username" => $username, "password" => $password, "notif" => $notif] = Validation::validate([
            "query" => [
                "username" => v::stringType()->notEmpty(),
                "password" => v::stringType()->notEmpty(),
                "notif" => v::optional(v::stringType())
            ]
        ]);
        App::controller(function () use ($username, $password, $notif) {
            /**
             * @var User
             */
            $user = User::where("username", $username)->firstOrFail();
            if (!password_verify($password, $user->password)) {
                App::$response = App::$response->withStatus(401);
                return ["error" => "Unauthorized"];
            }

            $token = Auth::makeToken($user, $notif);

            return $user->toArray() + ["token" => $token];
        });
    }

    public function my()
    {
        Auth::validate();
        App::controller(function () {
            return User::with(["profile", "result", "payment"])->where("id", Auth::$user->id)->first()->toArray();
        });
    }

    public function card()
    {
        Auth::validate();
        $name = Auth::$user->profile->fullname;
        $ttl = Auth::$user->profile->birth_of_place . ", " . Auth::$user->profile->birthday->format("d-m-Y");
        $path = __DIR__ . "/../public/uploaded/" . Auth::$user->profile->photo;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        // instantiate and use the dompdf class
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml("
            <html>
            <head>
            <style>
                @page { size: 9cm 5.5cm landscape;margin:3px }
                body {
                    font-size: 10pt;margin:3px
                }
                table td {
                    padding: 2px 0;
                }
            </style>
            </head>
            <body>
            <div style=\"border-bottom: 1px solid #000;padding: 11px 0;text-align:center\">
            KARTU PESERTA UJIAN <br/>
            PENERIMAAN PESERTA DIDIK BARU
            </div>
            <table style=\"width:100%;margin:10px 5px\">
            <tr>
                <td rowspan=\"4\" style=\"width:30%\">
                <img src=\"$base64\" style=\"width:85%;height:100px;object-fit:cover;margin: -5px 0;\"/>
                </td>
                <td width=\"15%\" valign=\"top\">Nama</td>
                <td width=\"10px\" valign=\"top\">:</td>
                <td valign=\"top\">$name</td>
            </tr>
            <tr>
                <td valign=\"top\">TTL</td>
                <td valign=\"top\">:</td>
                <td valign=\"top\">$ttl</td>
            </tr>
            <tr></tr>
            <tr></tr>
            </table>
            </body>
            </html>
        ");

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        App::$response->getBody()->write($dompdf->output());
        App::$response = App::$response->withHeader("Content-Type", "application/pdf");
        App::finish();
    }

    public function register()
    {
        $body = Validation::validate([
            "body" => [
                "username" => v::stringType()->notEmpty(),
                "password" => v::stringType()->notEmpty(),
                "fullname" => v::stringType()->notEmpty(),
                "gender" => v::stringType()->notEmpty()->in(["l", "p"]),
                "nisn" => v::stringType()->notEmpty(),
                "birth_of_place" => v::stringType()->notEmpty(),
                "birthday" => v::date("Y-m-d"),
                "religion" => v::stringType()->notEmpty(),
                "address" => v::stringType()->notEmpty(),
                "phone" => v::stringType()->notEmpty()->numericVal(),
                "school_origin" => v::stringType()->notEmpty(),
                "parent_status" => v::stringType()->notEmpty()->in(["mother", "father", "guard"]),
                "parent_name" => v::stringType()->notEmpty(),
                "parent_nik" => v::stringType()->notEmpty()->numericVal(),
                "kk_number" => v::stringType()->notEmpty()->numericVal(),
                "parent_place_of_birth" => v::stringType()->notEmpty(),
                "parent_birthday" => v::date("Y-m-d"),
                "profession" => v::stringType()->notEmpty(),
                "income" => v::stringType()->notEmpty()->numericVal(),
                "parent_address" => v::stringType()->notEmpty(),
                "notif" => v::optional(v::stringType())
            ],
            "file" => [
                "photo" => v::image()
            ]
        ]);
        App::controller(function () use ($body) {
            $data = [];
            Eloquent::getCapsule()->connection()->transaction(
                function () use ($body, &$data) {
                    $user = new User();
                    $user->username = $body["username"];
                    $user->password = password_hash($body["password"], PASSWORD_BCRYPT);
                    $user->save();
                    $notif = $body["notif"];

                    unset($body["username"]);
                    unset($body["password"]);
                    unset($body["notif"]);

                    $profile = new Profile();
                    foreach ($body as $key => $value) {
                        if ($key === "photo") {
                            $filename = sprintf(
                                '%s.%s',
                                Cuid::cuid(),
                                pathinfo($value->getClientFilename(), PATHINFO_EXTENSION)
                            );

                            /**
                             * @var UploadedFile $file 
                             */
                            $photo = $value;
                            $photo->moveTo(__DIR__ . "/../public/uploaded/$filename");

                            $profile->$key = $filename;
                        } else {
                            $profile->$key = $value;
                        }
                    }

                    $user->profile()->save($profile);

                    if (isset($file)) {
                        $file->moveTo(__DIR__ . "/../uploaded/$filename");
                    }

                    Xendit::setApiKey($_ENV["XENDIT_API_KEY"]);

                    $params = [
                        'external_id' => "user-" . $user->id,
                        'amount' => 100000,
                        'description' => 'Pembayaran PPDB',
                        'invoice_duration' => 60 * 60 * 24,
                        'currency' => 'IDR',
                        'items' => [
                            [
                                'name' => 'Pembayaran PPDB',
                                'quantity' => 1,
                                'price' => 100000,
                            ],
                        ],
                    ];

                    ["invoice_url" => $url] = Invoice::create($params);

                    $payment = new Payment();
                    $payment->amount = 100000;
                    $payment->inv_link = $url;
                    $payment->is_paid = false;

                    $user->payment()->save($payment);

                    $token = Auth::makeToken($user, $notif);

                    $data = $user->toArray() + [
                        "token" => $token
                    ] + [
                        "profile" => $profile->toArray()
                    ] + [
                        "payment" => $payment->toArray()
                    ];
                }
            );

            return $data;
        });
    }

    public function callback()
    {
        ["merchant_name" => $merchant, "id" => $id] = Validation::validate([
            "body" => [
                "merchant_name" => v::optional(v::stringType()->notEmpty()),
                "id" => v::optional(v::stringType()->notEmpty())
            ]
        ]);
        App::controller(function () use ($merchant, $id) {
            if ($merchant === "Xendit") {
                return ["success" => "Hello xendit!"];
            }

            $token = App::$request->getHeaderLine("x-callback-token") ?? "";

            if ($token !== $_ENV["X_CALLBACK_TOKEN"]) {
                App::$response = App::$response->withStatus(401);
                return ["error" => "Unauthorized"];
            }

            $getInvoice = (SiluetXendit::get())::retrieve($id);

            $payment = Payment::where("user_id", str_replace("user-", "", $getInvoice["external_id"]))->firstOrFail();
            $user = User::findOrFail($payment->user_id);
            $notif = Notif::send($user->notif, "Pembayaran Berhasil", "Pembayaran anda berhasil dikonfirmasi");

            if ($getInvoice["status"] === "SETTLED" || $getInvoice["status"] === "PAID") {
                $payment->is_paid = true;
                $payment->save();
            }

            return $payment->toArray();
        });
    }

    public function result($id)
    {
        AdminAuth::validate();
        ["result" => $result] = Validation::validate([
            "body" => [
                "result" => v::boolVal()
            ]
        ]);
        App::controller(function () use ($result, $id) {
            $resultIn = Result::where("user_id", $id)->firstOrNew();
            $resultIn->result = $result;
            $resultIn->user_id = $id;
            $resultIn->save();

            $user = User::findOrFail($id);

            $notif = Notif::send($user->notif, "Hasil Ujian", "Silahkan cek hasil ujian anda");

            return $resultIn->toArray();
        });
    }

    public function index()
    {
        AdminAuth::validate();
        App::controller(function () {
            $users = User::whereHas("payment", function ($query) {
                return $query->where("is_paid", true);
            })->with(["profile", "result"])->get();

            return $users->toArray();
        });
    }
}
