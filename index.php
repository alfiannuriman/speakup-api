<?php

require_once 'lib/Response.php';
require_once 'config.php';

require_once 'app/Auth.php';
require_once 'app/Post.php';
require_once 'app/User.php';
require_once 'app/PostCounterSummary.php';
require_once 'app/PostLikes.php';
require_once 'app/PostComment.php';

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

    case ($request == '/auth/user' && $request_method == 'GET'):
        $authModule = new App\Auth;
        return $authModule->user();
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

    case ($request == '/user/profile' && $request_method == 'GET'):
        $userModule = new App\User;
        return $userModule->show();
        break;

    case ($request == '/user/profile' && $request_method == 'POST'):
        $userModule = new App\User;
        return $userModule->store();
        break;

    case ($request == '/post/likes' && $request_method == 'POST'):
        $postLikesModule = new App\PostLikes;
        return $postLikesModule->store();
        break;

    case ($request == '/post/likes' && $request_method == 'GET'):
        $postLikesModule = new App\PostLikes;
        return $postLikesModule->show();
        break;

    case ($request == '/user' && $request_method == 'GET'):
        $userModule = new App\User;
        return $userModule->index();
        break;

    case ($request == '/user/followers' && $request_method == 'GET'):
        $userModule = new App\User;
        return $userModule->followers();
        break;

    case ($request == '/user/followings' && $request_method == 'GET'):
        $userModule = new App\User;
        return $userModule->following();
        break;

    case ($request == '/post/comments' && $request_method == 'POST'):
        $postCommentModule = new App\PostComment;
        return $postCommentModule->store();
        break;

    case ($request == '/post/comments' && $request_method == 'GET'):
        $postCommentModule = new App\PostComment;
        return $postCommentModule->show();
        break;
    
    default:
        return Lib\Response::restJSON([
            'message' => "There's nothing in here, go back!",
            'data' => [
                'route' => $request,
                'method' => $request_method
            ]
        ], 404);
        break;
}