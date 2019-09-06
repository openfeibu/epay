<?php
define('IN_WMF', true);
require_once __DIR__.DIRECTORY_SEPARATOR."../config_base.php";
if(isset($VERSIONVKEY) && $VERSIONVKEY != ''){
    //VERSIONVKEY要与APP中的VERSIONVKEY一致
    define("VERSIONVKEY",$VERSIONVKEY);
    require_once __DIR__.DIRECTORY_SEPARATOR."notify3.php";
}else{
    define("VERSIONVKEY",md5(uniqid(microtime(true),true)));
    require_once __DIR__.DIRECTORY_SEPARATOR."notify3.php";
}
