<?php

    namespace Classes;
    use mysqli;

    class User
    {
        private $host;
        private $user;
        private $password;
        private $db;

        public $connection;
        public $userGoogleKey;

        public function __construct()
        {
            $this->host = 'db';
            $this->user = 'root';
            $this->password = 'awesomemanu';
            $this->db = 'test';

            $this->connection = new mysqli($this->host, $this->user, $this->password, $this->db);
        
            if($this->connection->connect_error)
            {
                die("Error: ". $this->connection->connect_error);
            }
        }


        public function authenticateUser($postData)
        {
            $userFound = $this->connection->query("select * from users WHERE email like '%". 
                $postData['email']."%'");

            if($userFound)
            {
                if($userFound->num_rows)
                {
                    while($row =  $userFound->fetch_assoc()){
                        $this->userGoogleKey = $row["google_key"];
                    }

                    $userFound->close();
                    return true;
                }
            }

            return false;
        }

        public function getKeyFromUserAccount($postData)
        {
            $userFound = $this->connection->query("select * from users WHERE email like '%". 
                $postData['email']."%'");

            if($userFound)
            {
                if($userFound->num_rows)
                {
                    while($row =  $userFound->fetch_assoc()){
                        return $row["google_key"];
                    }

                    $userFound->close();
                    return true;
                }
            }
        }

        public function addKeyToUserAccount($post, $key)
        {
            $this->connection->query("update users set google_key='".$key."' where email ='".$post['email']."'");
        }
    }
