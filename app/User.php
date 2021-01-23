<?php

namespace App;

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';

require_once __DIR__ . '/../app/Auth.php';

use \Lib\Database;
use \Lib\Response;

use \App\Auth;

class User
{
    public function show()
    {
        try {
            $user = Auth::getLoggedUser();

            if ($user !== false) {
                $user_profile = $this->getUserProfile($user);
                return Response::apiResponse(200, 'Profile getted successfully', $user_profile);
            } else {
                return Response::apiResponse(401, 'Access token not found');
            }

        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage());
        }
    }

    public function store()
    {
        try {
            $user = Auth::getLoggedUser();

            if ($user !== false) {

                $request = $_POST;

                $user_profile = $this->getUserProfile($user);
                return Response::apiResponse(200, 'Profile getted successfully', $user_profile);
            } else {
                return Response::apiResponse(401, 'Access token not found');
            }

        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage());
        }
    }

    protected function getUserProfile(Object $user)
    {
        try {
            $db = new Database();
            
            $query = "SELECT user.name AS full_name, detail.*
                FROM act_users AS user
                LEFT JOIN act_user_detail AS detail ON detail.user_id = user.user_id
                WHERE user.user_id = :user_id";
            
            $query_params = [':user_id' => $user->user_id];

            return $db->prepareQuery($query, $query_params)->first();

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function getUserFollower($user_id)
    {
        try {
            $db = new Database();
            
            $query = "SELECT COUNT(user_follow_id) AS total FROM `act_user_follows` WHERE object_id = :user_id AND `status` = 0";
            $query_params = [':user_id' => $user->user_id];
            $follower = $db->prepareQuery($query, $query_params);

            return $follower ? $follower->first()->total : 0;

        } catch (\PDOException $e) {
            throw $e;
        }
    }


    protected function getUserFollowing($user_id)
    {
        try {
            $db = new Database();
            
            $query = "SELECT COUNT(user_follow_id) AS total FROM `act_user_follows` WHERE subject_id = :user_id AND `status` = 0";
            $query_params = [':user_id' => $user->user_id];
            $following = $db->prepareQuery($query, $query_params);

            return $following ? $following->first()->total : 0;

        } catch (\PDOException $e) {
            throw $e;
        }
    }
    
    protected function saveUserProfile($user, array $data)
    {
        try {

            $db = new Database();

            $query = "
                INSERT INTO `act_user_detail`(
                    `user_id`, `telepon`, `gender`, `birth_date`, `birth_place`
                ) VALUES (
                    :user_id, :telepon, :gender, :birth_date, :birth_place
            )";

            $query_params = [
                ':user_id' => $user->user_id,
                ':telepon' => $data['telepon']
            ];

            $inserted = $db->prepareQuery($query , $data);

            if ($inserted) {
                return true;
            }

            return $inserted ? $db->insertedId() : false;

        } catch (\PDOException $e) {
            throw $e;
        }
    }
}