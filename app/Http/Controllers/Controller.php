<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function Ok($data = [], $message = "Ok!", $others = []){
        return response([
            "ok" => true,
            "data" => $data,
            "message" => $message,
            "others" => $others,
        ]);
    }

    protected function BadRequest($validator, $message = "Request didn't pass the validation!", $others = []){
        return response([
            "ok" => false,
            "errors" => $validator->errors(),
            "message" => $message,
            "others" => $others
        ], 400);
    }

    protected function NotFound( string $message = "Not found!", $others = []){
        return response()->json([
            "ok" => false,
            "message" => $message,
        ], 404);
    }

    protected function Unauthorized($message = "Unauthorized!", $others = []){
        return response([
            "ok" => false,
            "message" => $message,
            "others" => $others
        ], 401);
    }

    protected function Forbidden($message = "Forbidden!", $others = []){
        return response([
            "ok" => false,
            "message" => $message,
            "others" => $others
        ], 403);
    }

    protected function Created($data = [], $message = "Created!", $others = []){
        return response([
            "ok" => true,
            "data" => $data,
            "message" => $message,
            "others" => $others
        ], 201);
    }
}
