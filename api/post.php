<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/autoload.php";
date_default_timezone_set("Asia/Hong_Kong");
$log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/post.log.php";
\epay\log::writeLog($log_file,$_REQUEST);
