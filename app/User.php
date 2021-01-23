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

                if ($this->saveUserProfile($user, $request)) {
                    return Response::apiResponse(200, 'Profile saved successfully');
                } else {
                    return Response::apiResponse(500, 'Save profile failed');
                }

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

            $user_profile = $db->prepareQuery($query, $query_params)->first();

            if ($user_profile !== false) {
                $user_profile->followers = $this->getUserFollower($user->user_id);
                $user_profile->following = $this->getUserFollowing($user->user_id);
            }

            return $user_profile;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function getUserFollower($user_id)
    {
        try {
            $db = new Database();
            
            $query = "SELECT COUNT(user_follow_id) AS total FROM `act_user_follows` WHERE object_id = :user_id AND `status` = 0";
            $query_params = [':user_id' => $user_id];
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
            $query_params = [':user_id' => $user_id];
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

            $query_find_profile = "SELECT detail_id FROM act_user_detail WHERE user_id = :user_id LIMIT 0,1";
            $query_find_profile_params = [':user_id' => $user->user_id];

            $find_user_profile = $db->prepareQuery($query_find_profile, $query_find_profile_params)->first();

            if ($find_user_profile !== false) {

                $query = "UPDATE `act_user_detail` SET 
                    telepon = :telepon, gender = :gender, birth_date = :birth_date, birth_place = :birth_place, update_dtm = :update_dtm
                     WHERE user_id = :user_id
                ";

                $query_params = [
                    ':user_id' => $user->user_id,
                    ':telepon' => $data['telepon'],
                    ':gender' => $data['gender'],
                    ':birth_date' => date('Y-m-d', strtotime($data['birth_date'])),
                    ':birth_place' => $data['birth_place'],
                    ':update_dtm' => date('Y-m-d H:i:s')
                ];
                
            } else {
                $query = "
                    INSERT INTO `act_user_detail`(
                        `user_id`, `telepon`, `gender`, `birth_date`, `birth_place`, `update_dtm`
                    ) VALUES (
                        :user_id, :telepon, :gender, :birth_date, :birth_place, :update_dtm
                )";

                $query_params = [
                    ':user_id' => $user->user_id,
                    ':telepon' => $data['telepon'],
                    ':gender' => $data['gender'],
                    ':birth_date' => date('Y-m-d', strtotime($data['birth_date'])),
                    ':birth_place' => $data['birth_place'],
                    ':update_dtm' => date('Y-m-d H:i:s')
                ];
            }

            $inserted = $db->prepareQuery($query, $query_params);

            if ($inserted) {
                if (isset($data['full_name'])) {
                    // UPDATE USER FULL NAME
                    $query_user = "UPDATE act_users SET name = :full_name WHERE user_id = :user_id";
                    $query_user_params = [
                        ':full_name' => $data['full_name'],
                        ':user_id' => $user->user_id
                    ];
    
                    return $db->prepareQuery($query_user, $query_user_params);
                }

                return true;

            } else {
                return false;
            }

        } catch (\PDOException $e) {
            throw $e;
        }
    }
}