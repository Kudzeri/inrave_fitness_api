<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Inrave API",
 *     version="1.0.0",
 *     description="Это документация по API для проекта Inrave."
 * )
 *
 * @OA\Server(
 *     url="https://inrave.promo/",
 *     description="Prod сервер API"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="BearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 *  )
 */

abstract class Controller
{
    //
}
