<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../config_base.php";
$url_compile = "{$website_urls}assets/compile/compile.php";
$a = file_get_contents($url_compile);
//var_dump($url_compile);
//var_dump($a);
header("Location: doc/00、index.php");

//自动压缩demo/demo文件夹

