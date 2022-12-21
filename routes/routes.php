<?php

$router->mount("/api", function () use ($router) {
    $router->mount(
        "/user",
        function () use ($router) {
            $router->get("/login", "UserController@login");
            $router->post("/update/(\d+)", "UserController@result");
            $router->get("/my", "UserController@my");
            $router->get("/card", "UserController@card");
            $router->post("/callback", "UserController@callback");
            $router->post("/", "UserController@register");
            $router->get("/", "UserController@index");
        }
    );
    $router->mount(
        "/admin",
        function () use ($router) {
            $router->get("/login", "AdminController@login");
        }
    );
});
