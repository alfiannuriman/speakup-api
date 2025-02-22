<?php

namespace App;

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';

require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Post.php';

use \Lib\Database;
use \Lib\Response;

use \App\Auth;
use \App\Post;

class User
{
    public function index()
    {
        try {
            return Response::apiResponse(200, 'Get user list successfully', $this->getUserLists($_GET));
        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage(), []);
        }
    }

    public function show()
    {
        try {
            $user_id = null;
            $user = Auth::getLoggedUser();

            if (isset($_GET['user_id'])) {
                $user_id = $_GET['user_id'];
            } else if ($user !== false) {
                $user_id = $user->user_id;
            } else {
                return Response::apiResponse(401, 'Access token not found');
            }

            $followed_by = ($user !== false) ? $user->user_id : null;

            $user_profile = $this->getUserProfile($user_id, $followed_by);
            return Response::apiResponse(200, 'Profile getted successfully', $user_profile);

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

    public function followers()
    {
        try {
            $user_id = null;
            $user = Auth::getLoggedUser();

            if (isset($_GET['user_id'])) {
                $user_id = $_GET['user_id'];
            } else if ($user !== false) {
                $user_id = $user->user_id;
            } else {
                return Response::apiResponse(400, 'Theres no data found', []);
            }

            return Response::apiResponse(200, 'Get user followers list successfully', $this->getUserFollowers($user_id));
        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage(), []);
        }
    }

    public function following()
    {
        try {
            $user_id = null;
            $user = Auth::getLoggedUser();

            if (isset($_GET['user_id'])) {
                $user_id = $_GET['user_id'];
            } else if ($user !== false) {
                $user_id = $user->user_id;
            } else {
                return Response::apiResponse(400, 'Theres no data found', []);
            }

            return Response::apiResponse(200, 'Get user following list successfully', $this->getUserFollowings($user_id));
        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage(), []);
        }
    }

    public function follow()
    {
        try {
            $user = Auth::getLoggedUser();

            if ($user !== false) {

                $request = $_POST;

                if (isset($request['user_id'])) {
                    if ($this->saveUserFollow($user->user_id, $request['user_id'])) {
                        return Response::apiResponse(200, 'User Followed successfully');
                    } else {
                        return Response::apiResponse(500, 'Failed to follow user');
                    }
                } else {
                    return Response::apiResponse(404, 'Cannot find user to follow');
                }

            } else {
                return Response::apiResponse(401, 'Access token not found');
            }

        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage());
        }
    }

    public function unfollow()
    {
        try {
            $user = Auth::getLoggedUser();

            if ($user !== false) {

                $request = $_POST;

                if (isset($request['user_id'])) {
                    if ($this->saveUserUnfollow($user->user_id, $request['user_id'])) {
                        return Response::apiResponse(200, 'User Unfollowed successfully');
                    } else {
                        return Response::apiResponse(500, 'Failed to unfollow user');
                    }
                } else {
                    return Response::apiResponse(404, 'Cannot find user to unfollow');
                }

            } else {
                return Response::apiResponse(401, 'Access token not found');
            }

        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage());
        }
    }

    protected function getUserLists($params)
    {
        try {
            $db = new Database();
            
            $query = "SELECT user.user_id AS user_id, user.code AS username, user.name AS full_name, detail.avatar_filename, detail.avatar_file_url
            FROM act_users user LEFT JOIN act_user_detail detail ON detail.user_id = user.user_id
            WHERE user.name LIKE :searched_user";
            
            $query_params = [':searched_user' => '%' . $params['full_name'] . '%'];

            return $db->prepareQuery($query, $query_params)->get();

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function getUserProfile($user_id, $followed_by = null)
    {
        try {
            $db = new Database();
            
            $query = "SELECT user.name AS full_name, detail.*
                FROM act_users AS user
                LEFT JOIN act_user_detail AS detail ON detail.user_id = user.user_id
                WHERE user.user_id = :user_id";
            
            $query_params = [':user_id' => $user_id];

            $user_profile = $db->prepareQuery($query, $query_params)->first();
            $user_profile->followers = $this->getUserTotalFollower($user_id);
            $user_profile->following = $this->getUserTotalFollowing($user_id);
            $user_profile->is_followed = !is_null($followed_by) ? $this->checkFollowedStatus($followed_by, $user_id) : false;

            $post_module = new Post();

            $user_profile->posts = $post_module->getOwnedPost($user_id);

            return $user_profile;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function getUserTotalFollower($user_id)
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

    protected function getUserFollowers($user_id)
    {
        try {
            $db = new Database();
            
            $query = "
                SELECT user.user_id AS user_id, user.code AS username, user.name AS full_name, detail.avatar_filename, detail.avatar_file_url
                FROM act_user_follows JOIN act_users user ON user.user_id = act_user_follows.subject_id
                LEFT JOIN act_user_detail detail ON detail.user_id = user.user_id
                WHERE act_user_follows.status = 0 AND act_user_follows.object_id = :user_id
            ";

            $query_params = [':user_id' => $user_id];
            return $db->prepareQuery($query, $query_params)->get();

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function getUserFollowings($user_id)
    {
        try {
            $db = new Database();
            
            $query = "
                SELECT user.user_id AS user_id, user.code AS username, user.name AS full_name, detail.avatar_filename, detail.avatar_file_url
                FROM act_user_follows JOIN act_users user ON user.user_id = act_user_follows.object_id
                LEFT JOIN act_user_detail detail ON detail.user_id = user.user_id
                WHERE act_user_follows.status = 0 AND act_user_follows.subject_id = :user_id
            ";

            $query_params = [':user_id' => $user_id];
            return $db->prepareQuery($query, $query_params)->get();

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function getUserTotalFollowing($user_id)
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

    protected function checkFollowedStatus($subject_id, $object_id)
    {
        try {
            $db = new Database();
            
            $query = "SELECT * FROM `act_user_follows` WHERE subject_id = :subject_id AND object_id = :object_id AND `status` = 0";
            $query_params = [
                'subject_id' => $subject_id,
                'object_id' => $object_id
            ];

            return $db->prepareQuery($query, $query_params)->exists();
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function saveUserFollow($subject_id, $object_id)
    {
        try {
            $db = new Database();
            
            $query_global_params = [
                'subject_id' => $subject_id,
                'object_id' => $object_id
            ];

            $query_find_follow = "SELECT * FROM `act_user_follows` WHERE subject_id = :subject_id AND object_id = :object_id AND `status` = 0";
            $find_follow = $db->prepareQuery($query_find_follow, $query_global_params)->first();

            if ($find_follow == false) {
                $query_create_follow = "
                    INSERT INTO `act_user_follows`(
                        `subject_id`, `object_id`, `start_dtm`
                    ) VALUES (
                        :subject_id, :object_id, :start_dtm
                )";

                $query_global_params['start_dtm'] = date('Y-m-d H:i:s');

                return $db->prepareQuery($query_create_follow, $query_global_params);
            } else {
                return true;
            }

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function saveUserUnfollow($subject_id, $object_id)
    {
        try {
            $db = new Database();
            
            $query_global_params = [
                'subject_id' => $subject_id,
                'object_id' => $object_id
            ];

            $query_find_follow = "SELECT * FROM `act_user_follows` WHERE subject_id = :subject_id AND object_id = :object_id AND `status` = 0";
            $find_follow = $db->prepareQuery($query_find_follow, $query_global_params)->first();

            if ($find_follow !== false) {
                $query_create_follow = "
                    INSERT INTO `act_user_follows`(
                        `subject_id`, `object_id`, `start_dtm`
                    ) VALUES (
                        :subject_id, :object_id, :start_dtm
                )";

                $query_delete_follow = "DELETE FROM `act_user_follows` WHERE user_follow_id = :user_follow_id";
                $query_delete_follow_params = [':user_follow_id' => $find_follow->user_follow_id];

                return $db->prepareQuery($query_delete_follow, $query_delete_follow_params);
            } else {
                return true;
            }

        } catch (\PDOException $e) {
            throw $e;
        }
    }
}