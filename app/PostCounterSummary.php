<?php

namespace App;

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Helpers.php';

use \Lib\Database;
use \Lib\Response;
use \Lib\Helpers;

class PostCounterSummary
{
    public static function countPostCounterSummary($article_id)
    {
        try {

            $db = new Database();

            $query = "SELECT SUM(num_viewed) AS total_viewed, SUM(num_commented) AS total_commented, SUM(liked) AS total_liked FROM post_article_counter WHERE article_id = :article_id";
            $query_params = [':article_id' => $article_id];

            $post_summary = $db->prepareQuery($query, $query_params)->first();

            $query_find_summary = "SELECT post_article_counter_sumary_id FROM post_article_counter_sumary WHERE article_id = :article_id LIMIT 0,1";
            $query_find_summary_params = [':article_id' => $article_id];

            $find_summary = $db->prepareQuery($query_find_summary, $query_find_summary_params)->exists();

            if ($find_summary) {
                $query_counter_summary = "
                    INSERT INTO `post_article_counter_sumary`(
                        `article_id`, `num_viewed`, `num_commented`, `num_liked`
                    ) VALUES (
                        :article_id, :num_viewed, :num_commented, :num_liked
                    ) ON DUPLICATE KEY 
                    UPDATE num_viewed = :num_viewed, num_commented = :num_commented, num_liked = :num_liked
                ";
            } else {
                $query_counter_summary = "
                    UPDATE `post_article_counter_sumary` SET num_viewed = :num_viewed, num_commented = :num_commented, num_liked = :num_liked WHERE article_id = :article_id
                ";
            }

            $query_counter_summary_params = [
                ':article_id' => $article_id,
                ':num_viewed' => !is_null($post_summary->total_viewed) ? $post_summary->total_viewed : 0,
                ':num_commented' => !is_null($post_summary->total_commented) ? $post_summary->total_commented : 0,
                ':num_liked' => !is_null($post_summary->total_liked) ? $post_summary->total_liked : 0
            ];

            return $db->prepareQuery($query_counter_summary, $query_counter_summary_params);

        } catch (\PDOException $e) {
            throw $e;
        }
    }
}