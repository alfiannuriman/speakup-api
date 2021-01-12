<?php

require_once 'lib/Response.php';
require_once 'config.php';

require_once 'app/Auth.php';

$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '':
        return Lib\Response::restJSON(['message' => $configs['database']]);
        break;

    case '/':
        return Lib\Response::restJSON(['message' => 'SpeakUp API V1']);
        break;

    case '/auth/register':
        $authModule = new App\Auth;
        return $authModule->register();
        break;
    
    default:
        return Lib\Response::restJSON(['message' => "There's nothing in here"], 404);
        break;
}