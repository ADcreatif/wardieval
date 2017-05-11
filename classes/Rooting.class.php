<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 05/03/2016
 * Time: 14:07
 */
class Rooting {

    function bootstrap() {
        spl_autoload_register([$this, "autoload"]);
    }

    function autoload($className) {

        if (substr($className, -10, 10) == "Controller") {
            $fileName = "controller/$className.class.php";
        } elseif (substr($className, -5, 5) == "Model") {
            $fileName = "model/$className.class.php";
        } else {
            $fileName = "classes/$className.class.php";
        }

        if (is_file($fileName))
            require $fileName;
        else
            throw new DomainException("impossible de trouver la classe $className dans $fileName");
    }

}