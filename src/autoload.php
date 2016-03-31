<?php

spl_autoload_register(function($class) {
    if (strpos($class, "FCL") === 0) {
        $class = substr($class, 4);
        $class = strtolower($class);
        $filepath = __DIR__.'/'.$class.'.php';
        if (file_exists($filepath)) {
            include $filepath;
        }
    }
});