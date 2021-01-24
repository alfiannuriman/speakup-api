<?php

namespace App;

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Helpers.php';

require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/PostCounterSummary.php';

use \Lib\Database;
use \Lib\Response;
use \Lib\Helpers;

use \App\Auth;
use \App\PostCounterSummary;

class PostLikes
{
    const LIKE_STATUS_LIKED = 1;
    const LIKE_STATUS_NORMAL = 0;

    public function store()
    {
        try {

            $article_id = $_GET['article_id'];

            if (isset($article_id)) {
                if ($this->savePostLike($article_id)) {
                    return Response::apiResponse(200, 'Post Liked Successfully');
                } else {
                    return Response::apiResponse(500, 'Liked Post Failed, please try again');
                }
            } else {
                return Response::apiResponse(400, 'Post Not Found');
            }

        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage());
        }
    }

    protected function savePostLike($article_id)
    {
        try {

            $db = new Database();

            $user = \App\Auth::getLoggedUser();

            if ($user !== false) {

                $query_find_article_counter = "SELECT article_counter_id, liked FROM post_article_counter WHERE view_by = :user_id AND article_id = :article_id LIMIT 0,1";
                $query_find_article_counter_params = [
                    ':user_id' => $user->user_id,
                    ':article_id' => $article_id
                ];

                $find_article_counter = $db->prepareQuery($query_find_article_counter, $query_find_article_counter_params)->first();

                if ($find_article_counter !== false) {
                    $query = "UPDATE `post_article_counter` SET `liked` = :liked  WHERE article_counter_id = :article_counter_id";

                    $liked_status = intval($find_article_counter->liked) == self::LIKE_STATUS_LIKED ? self::LIKE_STATUS_NORMAL : self::LIKE_STATUS_LIKED;

                    $query_params = [
                        ':liked' => $liked_status,
                        ':article_counter_id' => $find_article_counter->article_counter_id
                    ];
                } else {
                    $query = "
                        INSERT INTO `post_article_counter`(
                            `article_id`, `view_by`, `read_confirm_dtm`, `liked`
                        ) VALUES (
                            :article_id, :user_id, :read_confirm_dtm, :liked
                        )
                    ";

                    $query_params = [
                        ':article_id' => $article_id,
                        ':user_id' => $user->user_id,
                        ':read_confirm_dtm' => date('Y-m-d H:i:s'),
                        ':liked' => self::LIKE_STATUS_LIKED
                    ];
                }

                if ($db->prepareQuery($query, $query_params)) {
                    return PostCounterSummary::countPostCounterSummary($article_id);
                }

            }

            return false;

        } catch (\PDOException $e) {
            throw $e;
        }
    }
}