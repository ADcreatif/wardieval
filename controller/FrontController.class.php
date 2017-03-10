<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 05/03/2016
 * Time: 21:50
 */
abstract class FrontController {

    protected $message = [];
    public $tpl_vars = [];

    function __construct() {

        if (!empty($_POST)) {
            if (isset($_POST['ajax'])) {
                $this->ajax();
            } else {
                $this->tpl_vars = array_merge($this->tpl_vars, (array)$this->httpPost());
            }
        } else {
            $this->tpl_vars = array_merge($this->tpl_vars, (array)$this->display());
        }
    }


    /**
     * Display function must return template vars as an array
     **/
    protected function display() {
    }

    /**
     * Override to manage any post requests
     **/
    protected function httpPost() {
    }

    protected function ajax() {
    }

    /**
     * @deprecated
     * @param $message
     */
    protected function addMessage($message) {
        array_push($this->message, $message);
    }

    /**
     * @deprecated
     */
    public function getMessages() {
        return $this->message;
    }

}