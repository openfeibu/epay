<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
$config = array(
    "key" => "GzUsMXjYaFdOmzBjHu1bGFjeIEYzAhoW"
);

$pdo = $DB;

function create_token($arr,$token){
    $str = "";
    foreach ($arr as $v){
        $str .= $v."@";
    }
    $str = md5($str).$token;
    return md5($str);
}