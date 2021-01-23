<?php

namespace Lib;

require_once __DIR__ . '/../app/Auth.php';

class Response
{
    public static function restJSON($data, $responseCode = 200)
    {
        http_response_code($responseCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public static function apiResponse($responseCode = 200, $info = null, $data = null)
    {
        $response_message = !is_null($info) ? $info : 'Request Successfully';
        $user = \App\Auth::getLoggedUser();

        $user_token = $user !== false ? $user->token : null;

        $response = [
            'code' => $responseCode,
            'data' => $data,
            'info' => $response_message,
            'token' => $user_token
        ];

        return static::restJSON($response, $responseCode);
    }
}