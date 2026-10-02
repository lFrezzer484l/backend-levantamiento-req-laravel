<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Requirements API",
    version: "1.0.0",
    description: "API para la gestión de requerimientos."
)]
#[OA\Server(
    url: "http://127.0.0.1:4000",
    description: "Servidor local"
)]
#[OA\Server(
    url: "https://backendlevantamiento-req-laravel.onrender.com",
    description: "Servidor de producción"
)]
class OpenApi
{
}