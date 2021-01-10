<?php

require 'lib/Response.php';
require 'config.php';
require 'lib/Database.php';

$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '':
        return Lib\Response::restJSON(['message' => $configs['database']]);
        break;

    case '/':
        return Lib\Response::restJSON(['message' => 'SpeakUp API V1']);
        break;

    case '/db/test':
        
        try {
            $database = new \Lib\Database();
            return Lib\Response::restJSON(['message' => 'Database connection looks good']);
        } catch (\PDOException $e) {
            return Lib\Response::restJSON(['errors' => 'Database error : ' . $e->getMessage()], 500);
        }
        break;
    
    default:
        return Lib\Response::restJSON(['message' => "There's nothing in here"], 404);
        break;
}