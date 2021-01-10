<?php

namespace Lib;

class Response
{
    public static function restJSON($data, $responseCode = 200)
    {
        http_response_code($responseCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}