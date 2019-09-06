<?php
date_default_timezone_set("Asia/Hong_Kong");

set_time_limit(0);
require_once __DIR__."/../includes/api/autoload.php";
$date = date("Ymd");
$log_dir = __DIR__.DIRECTORY_SEPARATOR."../etc/log".$date;
if(!file_exists($log_dir)){
    \epay\file::copydir(__DIR__.DIRECTORY_SEPARATOR."../etc/log",$log_dir);
    \epay\file::copydir(__DIR__.DIRECTORY_SEPARATOR."../etc/log2",__DIR__.DIRECTORY_SEPARATOR."../etc/log");
    echo "ok";
}else{
    echo "exist";
}
