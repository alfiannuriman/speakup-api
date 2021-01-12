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
                    return Response::restJSON(['data' => $create_user]);
                } else {
                    return Response::restJSON(['errors' => 'Create user failed, please try again'], 500);
                }

            } else {
                return Response::restJSON(['errors' => 'User already exists'], 400);
            }
        } catch (\Exception $e) {
            return Response::restJSON(['errors' => $e->getMessage()], 500);
        }
    }

    protected function createUser(array $data, $status = null)
    {
        try {

            $db = new Database();

            $query = "INSERT INTO `act_users`(`code`, `name`, `email`, `password`, `user_status_id`, `create_dtm`) 
                        VALUES (:username, :name, :email, :password, :status, :create_dtm)";

            $data = [
                ':username' => $data['username'],
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
                ':username' => $username,
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
