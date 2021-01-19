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

    public function store()
    {
        try {

            $request = $_POST;
            $article_id = $this->createPost($request);

            if ($article_id !== false) {
                if (isset($_FILES['medias'])) {
                    // return print_r($_FILES['medias']);
                    // exit();
                    // return Response::restJSON(['message' => $_FILES['medias']]);
                    if ($this->storePostMedia($article_id, $_FILES['medias'])) {
                        return Response::restJSON(['message' => 'Create Post success']);
                    }
                }
            }

            return Response::restJSON(['message' => 'Create Post failed, please try again'], 500);

        } catch (\Exception $e) {
            return Response::restJSON(['errors' => $e->getMessage()], 500);
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

                return $inserted ? $db->insertedId() : false;
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