<?php
/*
 * 数据库连接
 */
date_default_timezone_set("Asia/Hong_Kong");
require_once __DIR__."/../config.php";
ini_set("display_errors","on");

try{
    $DB = new PDO("mysql:host={$dbconfig['host']};dbname={$dbconfig['dbname']};port={$dbconfig['port']};charset=utf8",$dbconfig['user'],$dbconfig['pwd']);
}catch (Exception $e){
    exit('链接数据库失败:'.$e->getMessage());
}

$DB->query("set names utf8");
$DB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
