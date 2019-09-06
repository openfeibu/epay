<?php
date_default_timezone_set("Asia/Hong_Kong");

require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/autoload.php";
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
$user = get_current_user();
$dir = "/home/$user/";
$files = "/cron.*php.*/";

//此函数慎用
\epay\file::delete($dir,$files);
