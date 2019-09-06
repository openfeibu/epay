<?php
require_once __DIR__.DIRECTORY_SEPARATOR."vendor/autoload.php";
spl_autoload_register(function($class_name){
    $file = __DIR__.DIRECTORY_SEPARATOR.str_replace("\\","/",$class_name).".php";
    if(file_exists($file)){
        require_once $file;
    }
});
