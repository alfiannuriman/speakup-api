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

class PostComment
{
    public function store()
    {
        try {

            $article_id = $_POST['article_id'];

            if (isset($article_id)) {
                if ($this->savePostComment($article_id, $_POST)) {
                    return Response::apiResponse(200, 'Post commented Successfully');
                } else {
                    return Response::apiResponse(500, 'Commented Post Failed, please try again');
                }
            } else {
                return Response::apiResponse(400, 'Post Not Found');
            }

        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage());
        }
    }

    public function show()
    {
        try {

            $article_id = $_GET['article_id'];

            if (isset($article_id)) {
                return Response::apiResponse(200, 'Get Post Liked Successfully', $this->getPostLike($article_id));
            } else {
                return Response::apiResponse(400, 'Post Not Found', []);
            }

        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage(), []);
        }
    }

    // GET LIST OF USER WHO LIKED THE POST
    protected function getPostLike($article_id)
    {
        try {

            $db = new Database();

            $query = "
                SELECT post_article_counter.*, act_users.user_id AS user_id, act_users.name AS full_name
                FROM post_article_counter JOIN act_users ON act_users.user_id = post_article_counter.view_by
                WHERE article_id = :article_id AND liked = :liked_status
            ";

            $query_params = [
                ':article_id' => $article_id,
                'liked_status' => self::LIKE_STATUS_LIKED
            ];

            return $db->prepareQuery($query, $query_params)->get();

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function savePostComment($article_id, array $data)
    {
        try {

            $db = new Database();

            $user = \App\Auth::getLoggedUser();

            if ($user !== false) {

                $query = "
                    INSERT INTO `post_article_comment`(
                        `parent_comment_id`, 
                        `article_id`, 
                        `comment`, 
                        `comment_by`, 
                        `comment_dtm`, 
                        `update_dtm`
                    ) 
                    VALUES (
                        :parent_comment_id,
                        :article_id,
                        :comment,
                        :comment_by,
                        :comment_dtm,
                        :update_dtm
                    )
                ";

                $query_params = [
                    ':parent_comment_id' => isset($data['parent_comment_id']) ? $data['parent_comment_id'] : null,
                    ':article_id' => $article_id,
                    ':comment' => $data['comment'],
                    ':comment_by' => $user->user_id,
                    ':comment_dtm' => date('Y-m-d H:i:s'),
                    ':update_dtm' => date('Y-m-d H:i:s'),
                ];

                if ($db->prepareQuery($query, $query_params)) {
                    return $this->savePostCounter($article_id);
                }
            }

            return false;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function savePostCounter($article_id)
    {
        try {

            $db = new Database();

            $user = \App\Auth::getLoggedUser();

            if ($user !== false) {

                $query_find_article_counter = "SELECT article_counter_id, num_commented FROM post_article_counter WHERE view_by = :user_id AND article_id = :article_id LIMIT 0,1";
                $query_find_article_counter_params = [
                    ':user_id' => $user->user_id,
                    ':article_id' => $article_id
                ];

                $find_article_counter = $db->prepareQuery($query_find_article_counter, $query_find_article_counter_params)->first();

                if ($find_article_counter !== false) {
                    $query = "UPDATE `post_article_counter` SET `num_commented` = :num_commented  WHERE article_counter_id = :article_counter_id";

                    $query_params = [
                        ':num_commented' => $find_article_counter->num_commented + 1,
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
                        ':num_commented' => 1
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