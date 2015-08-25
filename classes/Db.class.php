<?php

class Db {

    private static $instance;
    private static $message;

    private function __construct() {
        self::$instance = new PDO('mysql:host='._DB_HOST_.';dbname='. _DB_NAME_,_DB_USER_,_DB_PASS_, array(PDO::ATTR_ERRMODE =>
                                                                                                           PDO::ERRMODE_EXCEPTION,
                                                                                                           PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                                                                                                           PDO::MYSQL_ATTR_USE_BUFFERED_QUERY =>true));
    }

    public static function getMessage(  ) {
        return self::$message;
    }

    public static function getInstance(  ) {
        if(!self::$instance){
            self::$instance = new PDO('mysql:host='._DB_HOST_.';dbname='. _DB_NAME_,_DB_USER_,_DB_PASS_, array(PDO::ATTR_ERRMODE =>
                                                                                                               PDO::ERRMODE_EXCEPTION,
                                                                                                               PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                                                                                                               PDO::MYSQL_ATTR_USE_BUFFERED_QUERY =>true));
        }
        return self::$instance;
    }

    public static function prepare($sql){
        return self::getInstance()->prepare($sql);
    }

    /**
     * Effectuer une requête type SELECT
     * @param $sql string la requête sql
     * @return PDOStatement contenant le résultat de la requete
     */
    public static function query($sql){
        return self::getInstance()->query($sql);
    }

    /**
     * Effectuer une requête type INSERT, UPDATE...
     * @param $sql string la requête sql
     * @return int le nombre de lignes affectées.
     */
    public static function exec($sql){
        return self::getInstance()->exec($sql);
    }

    public static function getLastInsertId(){
        return self::getInstance()->lastInsertId();
    }

    public static  function insertModel($table, $fields){

    }
}