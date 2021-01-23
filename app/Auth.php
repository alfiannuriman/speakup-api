<?php

namespace App;

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';

use \Lib\Database;
use \Lib\Response;

class Auth
{
    const USER_STATUS_ACTIVE = 1;
    const USER_STATUS_INACTIVE = 2;
    const USER_STATUS_BANNED = 3;

    public function register()
    {
        try {
            $request = $_POST;

            $email = $request['email'];
            $username = $request['username'];
            $password = $request['password'];

            if (!$this->isUserExists($email, $username)) {

                if ( ($create_user = $this->createUser($request)) !== false ) {
                    return Response::apiResponse(200, 'User Registered Successfully', $create_user);
                } else {
                    return Response::apiResponse(500, 'Create user failed, please try again');
                }

            } else {
                return Response::apiResponse(400, 'User already exists');
            }
        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage());
        }
    }

    public function login()
    {
        try {
            $request = $_POST;

            $username = $request['username'];
            $password = $request['password'];

            if ( ($user = $this->findUserByUsername($username)) !== false) {

                if ($this->verifyPasswordHash($password, $user->password)) {
                    $user->token = $auth_token = $this->setUserAuthToken($user->user_id);

                    if ($auth_token !== false) {
                        return Response::apiResponse(200, 'Login Successfull', $user);
                    }
                }

                return Response::apiResponse(400, 'Login failed, please try again');

            } else {
                return Response::apiResponse(404, 'User not found');
            }
        } catch (\Exception $e) {
            return Response::apiResponse(500, $e->getMessage());
        }
    }

    public function user()
    {
        return Response::apiResponse(200, static::getLoggedUser());
    }

    public static function getLoggedUser()
    {
        $headers = getallheaders();

        if (array_key_exists('Authorization', $headers)) {
            $auth_token = trim(str_replace('Bearer', '', $headers['Authorization']));
            $user = self::findUserByToken($auth_token);

            if ($user !== null) {
                return $user;
            }
        }

        return false;
    }

    protected function generateAuthToken()
    {
        return bin2hex(random_bytes(20));
    }
    
    protected function setUserAuthToken($user_id)
    {
        try {

            $db = new Database();

            $token = $this->generateAuthToken();
            $query = "UPDATE act_users SET token = :token WHERE user_id = :user_id";

            $set_token = $db->prepareQuery($query , [
                ':token' => $token,
                ':user_id' => $user_id
            ]);

            return $set_token ? $token : false;

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function findUserByToken($token)
    {
        try {

            $db = new Database();

            $query = "SELECT * FROM act_users WHERE token = :token LIMIT 0,1";

            return $db->prepareQuery($query , [
                ':token' => $token
            ])->first();

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function findUserByUsername($username)
    {
        try {

            $db = new Database();

            $query = "SELECT * FROM act_users WHERE code = :username LIMIT 0,1";

            return $db->prepareQuery($query , [
                ':username' => strtolower($username)
            ])->first();

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function createUser(array $data, $status = null)
    {
        try {

            $db = new Database();

            $query = "INSERT INTO `act_users`(`code`, `name`, `email`, `password`, `user_status_id`, `create_dtm`) 
                        VALUES (:username, :name, :email, :password, :status, :create_dtm)";

            $data = [
                ':username' => strtolower($data['username']),
                ':name' => isset($data['name']) ? $data['name'] : $data['username'],
                ':email' => $data['email'],
                ':password' => $this->generatePasswordHash($data['password']),
                ':status' => !is_null($status) ? $status : self::USER_STATUS_ACTIVE,
                ':create_dtm' => date('Y-m-d H:i:s')
            ];

            return $db->prepareQuery($query, $data);

        } catch (\PDOException $e) {
            throw $e;
        }
    }

    protected function isUserExists($email, $username)
    {
        try {

            $db = new Database();

            $query = "SELECT user_id FROM act_users WHERE code = :username AND email = :email LIMIT 0,1";
            $is_user_exists = $db->prepareQuery($query , [
                ':username' => strtolower($username),
                ':email' => $email
            ])->exists();

            return $is_user_exists;

        } catch (\PDOException $e) {
            throw $e;
        }
    }
    
    protected function generatePasswordHash($plain_password)
    {
        // GENERATE PASSWORD USING BCRYPT ALGORITHM
        return password_hash($plain_password, PASSWORD_BCRYPT);
    }

    protected function verifyPasswordHash($plain_password, $hashed_password)
    {
        return password_verify($plain_password, $hashed_password);
    }
}
