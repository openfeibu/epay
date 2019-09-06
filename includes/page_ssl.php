<?php
//此文件API接口不能引用，否则非https的API将会重定向错误。
if($page_ssl){
    if(!((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))){
        Header("HTTP/1.1 301 Moved Permanently");
        header('Location: https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);//默认端口
        //header("Location: {$website_urls}".$_SERVER['REQUEST_URI']);
    }
}
