<?php

class DB {
    private static $instance = null;
    private $database = null;
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function query($query) {
        return $this->database->query($query);
    }

    public function row($result) {
        return mysqli_fetch_array($result);
    }

    public function count($result) {
        return mysqli_num_rows($result);
    }

    public function lastId() {
        return mysqli_insert_id($this->database);
    }

    public static function escape($string) {
        return mysqli_real_escape_string(utf8_decode($string));
    }


    private function __construct(){
        if(is_null($this->database)) {
            $this->database = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
        }
    }
    private function __clone(){}
}

?>