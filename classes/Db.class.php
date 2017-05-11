<?php

/**
 * Class Db
 * Charge et instancie PDO, elle possède également quelques alias tels que prepare/query...
 */
class Db {

    private $instance;

    function __construct() {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ];
        $this->instance = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS, $options);
    }

    public function queryOne($sql, array $arguments = [], $fetchColumn = false) {
        $query = $this->instance->prepare($sql);
        $query->execute($arguments);

        if ($fetchColumn)
            return $query->fetchColumn();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function query($sql, array $arguments = []) {
        $query = $this->instance->prepare($sql);
        $query->execute($arguments);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $sql string
     * @param array $arguments prepared
     * @return int the last inserted id or the number of affected rows
     */
    public function exec($sql, array $arguments = []) {
        $query = $this->instance->prepare($sql);
        $query->execute($arguments);

        return $this->instance->lastInsertId() != 0 ? $this->instance->lastInsertId() : $query->rowCount();
    }

    /**
     * Sécurise les données qui seront injecté dans la base
     * @param string $string donnée SQL qui sera injecté
     * @param boolean $htmlOK est ce que le champ contiens des balises HTML ? (optionel)
     * @return string la donnée sécurisée
     */
    public static function sanitize($string, $htmlOK = false) {

        if (get_magic_quotes_gpc()) $string = stripslashes($string);
        if (!is_numeric($string)) {
            // @ pour masquer la deprecated de mysql_real_escape_string
            $string = function_exists('mysql_real_escape_string') ? @mysql_real_escape_string($string) : addslashes($string);
            if (!$htmlOK) $string = strip_tags(nl2br($string));
        }
        return $string;
    }

    /** Retourne l'exception
     * @param  string $message
     * @param  string $sql
     * @return string

    private static function ExceptionLog($message, $sql = "") {
     * $exception = "<h2>Zut une erreur SQL... </h2><p>Ce site à bien été codé avec les pieds. Mais pas d'inquiétude on est sur le coup !</p>";
     * if (_DEBUG_) {
     * $exception .= $message;
     * $exception .= "<br>\r\nRaw SQL : $sql";
     * echo '<pre>';
     * debug_print_backtrace();
     * echo '</pre>';
     * }
     * return $exception;
     * }*/
}