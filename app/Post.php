<?php

namespace App;

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';

use \Lib\Database;
use \Lib\Response;

class Post
{
    const POST_SCOPE_ALL = 0;
    const POST_SCOPE_CLOSE_FRIEND = 1;

    const POST_STATUS_PUBLISHED = 1;
    const POST_STATUS_DELETED = 2;
    const POST_STATUS_ARCHIVED = 3;

    public function store()
    {
        try {

            $request = $_POST;

            if ($this->createPost($request)) {
                return Response::restJSON(['message' => 'Create Post success']);
            } else {
                return Response::restJSON(['message' => 'Create Post failed, please try again'], 500);
            }

        } catch (\Exception $e) {
            return Response::restJSON(['errors' => $e->getMessage()], 500);
        }
    }

    public function createPost(array $data)
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
    
                return $db->prepareQuery($query , [
                    ':content' => $data['content'],
                    ':scope' => $data['scope'],
                    ':status_id' => $data['status_id'],
                    ':created_dtm' => date('Y-m-d H:i:s'),
                    ':created_by' => $user->user_id
                ]);
            }

            return false;

        } catch (\PDOException $e) {
            throw $e;
        }
    }
}