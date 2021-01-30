<?php

namespace App;

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Helpers.php';

use \Lib\Database;
use \Lib\Response;
use \Lib\Helpers;

class Post
{
    const POST_SCOPE_ALL = 0;
    const POST_SCOPE_CLOSE_FRIEND = 1;

    const POST_STATUS_PUBLISHED = 1;
    const POST_STATUS_DELETED = 2;
    const POST_STATUS_ARCHIVED = 3;

    public function index()
    {
        try {

            $request = $_GET;
        
            return Response::apiResponse(200, null, $this->getUserPost($request));

        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage(), []);
        }
    }

    public function store()
    {
        try {

            $request = $_POST;
            $article_id = $this->createPost($request);

            if ($article_id !== false) {
                if (isset($_FILES['medias'])) {
                    if ($this->storePostMedia($article_id, $_FILES['medias'])) {
                        return Response::apiResponse(200, 'Create Post success');
                    }
                }
            }

            return Response::apiResponse(500, 'Create Post failed, please try again');

        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage());
        }
    }

    protected function getUserPost(array $params = null)
    {
        try {

            $db = new Database();
            $user = \App\Auth::getLoggedUser();

            $response = [];

            if ($user !== false) {

                $query = "
                    SELECT *, act_users.name AS created_by_name FROM post_article
                    JOIN post_article_subjects ON post_article_subjects.article_id = post_article.article_id
                    JOIN act_users ON act_users.user_id = post_article.created_by
                    WHERE post_article_subjects.subject_id = :user_id 
                ";

                $query_params = [];

                if (!is_null($params)) {
                    
                    if (isset($params['article_id'])) {
                        $query .= " AND article_id = :article_id";
                        $query_params[':article_id'] = $params['article_id'];
                    }

                    if (isset($params['scope'])) {
                        $query .= " AND scope = :scope";
                        $query_params[':scope'] = $params['scope'];
                    }

                    if (isset($params['limit'])) {
                        $query .= " LIMIT :limited";
                        $query_params[':limited'] = $params['limit'];
                    }

                }

                $query_params[':user_id'] = $user->user_id;

                $posts = $db->prepareQuery($query, $query_params)->get();

                if (count($posts) > 0) {
                    foreach ($posts as $key => $post) {
                        $post->medias = $this->getPostMedia($post->article_id);
                        array_push($response, $post);
                    }
                }
            }

            return $response;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    public function getOwnedPost($user_id, $params = null)
    {
        try {

            $db = new Database();
            $user = \App\Auth::getLoggedUser();

            $response = [];

            if ($user !== false) {

                $query = "
                    SELECT post_article.*, act_users.name AS created_by_name FROM post_article
                    JOIN act_users ON act_users.user_id = post_article.created_by
                    WHERE post_article.created_by = :user_id 
                ";

                $query_params = [];

                if (!is_null($params)) {
                    
                    if (isset($params['article_id'])) {
                        $query .= " AND article_id = :article_id";
                        $query_params[':article_id'] = $params['article_id'];
                    }

                    if (isset($params['scope'])) {
                        $query .= " AND scope = :scope";
                        $query_params[':scope'] = $params['scope'];
                    }

                    if (isset($params['limit'])) {
                        $query .= " LIMIT :limited";
                        $query_params[':limited'] = $params['limit'];
                    }

                }

                $query_params[':user_id'] = $user_id;

                $posts = $db->prepareQuery($query, $query_params)->get();

                if (count($posts) > 0) {
                    foreach ($posts as $key => $post) {
                        $post->medias = $this->getPostMedia($post->article_id);
                        array_push($response, $post);
                    }
                }
            }

            return $response;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function getPostMedia($article_id)
    {
        try {

            $db = new Database();
            $query_post = "SELECT * FROM post_article_media WHERE article_id = :article_id";

            return $posts = $db->prepareQuery($query_post, [':article_id' => $article_id])->get();

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function createPost(array $data)
    {
        try {

            $db = new Database();

            $user = \App\Auth::getLoggedUser();

            if ($user !== false) {
                $query = "INSERT INTO `post_article`(
                    `content`, `scope`, 
                    `status_id`, `created_dtm`, 
                    `created_by`
                  ) 
                  VALUES 
                    (
                      :content, :scope, :status_id, :created_dtm, :created_by
                    )";
    
                $inserted = $db->prepareQuery($query , [
                    ':content' => $data['content'],
                    ':scope' => $data['scope'],
                    ':status_id' => $data['status_id'],
                    ':created_dtm' => date('Y-m-d H:i:s'),
                    ':created_by' => $user->user_id
                ]);

                if ($inserted) {
                    $article_id = $db->insertedId();
                    $this->createPostSubject($article_id);

                    return $article_id;
                }

            }

            return false;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function createPostSubject($article_id)
    {
        try {

            $db = new Database();

            $user = \App\Auth::getLoggedUser();

            if ($user !== false) {
                $query_followers_user = "SELECT subject_id FROM `act_user_follows` WHERE object_id = :user_id AND `status` = 0";
                
                $followers = $db->prepareQuery($query_followers_user, [
                    ':user_id' => $user->user_id
                ])->get();

                $this->savePostSubject($article_id, $user->user_id);

                if ($followers && count($followers) > 0) {
                    foreach ($followers as $follower) {
                        $this->savePostSubject($article_id, $follower->subject_id);
                    }
                }

            }

            return true;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function savePostSubject($article_id, $subject_id)
    {
        try {

            $db = new Database();

            $user = \App\Auth::getLoggedUser();

            if ($user !== false) {
                $query = "
                    INSERT INTO `post_article_subjects`(
                        `article_id`, `subject_id`, `added_by`, `added_dtm`
                    ) VALUES (
                        :article_id, :subject_id, :added_by, :added_dtm
                    )
                ";

                $query_params = [
                    ':article_id' => $article_id,
                    ':subject_id' => $subject_id,
                    ':added_by' => $user->user_id,
                    ':added_dtm' => date('Y-m-d H:i:s')
                ];

                return $db->prepareQuery($query, $query_params);
            }

            return false;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function storePostMedia($article_id, array $uploaded_medias)
    {
        for ($i=0; $i < count($uploaded_medias['name']); $i++) { 
            $file_name = Helpers::generateFileName();
            $file_extension = Helpers::getUploadedExtension($uploaded_medias['name'][$i]);
            $filename = $file_name . '.' . $file_extension;

            if (move_uploaded_file($uploaded_medias['tmp_name'][$i], './medias/' . $filename) !== false) {
                if ($this->savePostMedia($article_id, $filename) == false) {
                    return false;
                }
            } else {
                return false;
            }   
        }

        return true;
    }

    protected function savePostMedia($article_id, $filename)
    {
        try {

            $db = new Database();

            $query = "INSERT INTO `post_article_media`(`article_id`, `filename`, `file_url`) VALUES (:article_id, :filename, :file_url)";

            return $db->prepareQuery($query , [
                ':article_id' => $article_id, 
                ':filename' => $filename, 
                ':file_url' => Helpers::getBaseUrl(true) . 'medias/' . $filename
            ]);

        } catch (\PDOException $e) {
            throw $e;
        }
    }
}