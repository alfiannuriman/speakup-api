<?php

require_once 'lib/Response.php';
require_once 'config.php';

require_once 'app/Auth.php';
require_once 'app/Post.php';

$request = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request) {
    case '':
        return Lib\Response::restJSON(['message' => 'SpeakUp API V1']);
        break;

    case '/':
        return Lib\Response::restJSON(['message' => 'SpeakUp API V1']);
        break;

    case ($request == '/auth/register' && $request_method == 'POST'):
        $authModule = new App\Auth;
        return $authModule->register();
        break;

    case ($request == '/auth/login' && $request_method == 'POST'):
        $authModule = new App\Auth;
        return $authModule->login();
        break;

    case '/test':
        return Lib\Response::restJSON(['data' => App\Auth::getLoggedUser()]);
        break;
    
    case ($request == '/post' && $request_method == 'POST'):
        $postModule = new App\Post;
        return $postModule->store();
        break;
    
    case ($request == '/post' && $request_method == 'GET'):
        $postModule = new App\Post;
        return $postModule->index();
        break;
    
    
    default:
        return Lib\Response::restJSON([
            'message' => "There's nothing in here",
            'data' => [
                'route' => $request
            ]
        ], 404);
        break;
}