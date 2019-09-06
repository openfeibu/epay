<?php
error_reporting(0);
if(!isset($_REQUEST["APP_PUBLIC_KEY"])){
    echo '{"msg":"参数不正确！"}';
    exit();
}
$tt = "v6Jky6dNRXtj6LA80ymSuDaWyZt9iXL69WtVnF2W+jAaI5Gd5UaZPtTUrlbf0lQGCmxBxBh16pM+geGWXBg8gYk/c0VIS0wHbOKQMtoclkmUNzQhgm/pisSHqegnm/kifoz7f0M79EiE3joxo6UBUDEvhL8ATXG+oKKUc1KUooilGvDyHDoG1FErSp40v4A1k4/ZtgSuX9QXZ6FEOzHYE9OIISUx8DwjIHbqdnLbEp031ess/YHkM/J+a81fK0EEGkf04k+wm21jNNftKam0EsexIRy7ddk2aFm00ipUEHJmU5eV9kUmmUx/H9kgiqRt/Vv+AHtv/JLKqlJeriGbioWHAw8yVwlJgBS8PRxMlXBp3MZVGTWwRhv8u+tKlr4ixTlfUw46Z8Axxc470b9z5CXqb5CmbNwax+/ztMpnxK/b2oW1+dXWOfxTZyNBc/EL";
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../config/config_base.php";
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
$app_public_key = $_REQUEST["APP_PUBLIC_KEY"];
//file_put_contents("../config/cache/app_public_key.json",$app_public_key);
//echo $app_public_key."<br>";
$app_public_key_arr = json_decode($app_public_key,true);
//var_dump($app_public_key_arr);echo "<br>";
ksort($app_public_key_arr);
//var_dump($app_public_key_arr);
$app_public_key = json_encode($app_public_key_arr,JSON_UNESCAPED_UNICODE);
//echo $app_public_key."<br>";
//var_dump($app_public_key_arr);


$sql = "SELECT * FROM `pay_channel` WHERE `id` = '{$app_public_key_arr["channel_id"]}'";
$re = $DB->query($sql);
if(!$re->rowCount()){
    echo '{"msg":"通道不存在！"}';
    exit();
}
$res = $re->fetch();
$APP_PUBLIC_KEY = array();//数组初始化
$APP_PUBLIC_KEY["VERSION2"] = $VERSION2;
$APP_PUBLIC_KEY["VERSIONV1"] = $VERSIONV1;
$APP_PUBLIC_KEY["VERSIONVKEY"] = $VERSIONVKEY;
$APP_PUBLIC_KEY["VERSION_RETURN_URL"] = $VERSION_RETURN_URL;
$APP_PUBLIC_KEY["userids"] = $res["appid"];
$APP_PUBLIC_KEY["usernames"] = $res["subject"];
$APP_PUBLIC_KEY["signkey"] = $res["private_key"];
$APP_PUBLIC_KEY["UPDATE_URL"] = $UPDATE_URL;
$APP_PUBLIC_KEY["channel_id"] = $res["id"];
ksort($APP_PUBLIC_KEY);
$APP_PUBLIC_KEY = json_encode($APP_PUBLIC_KEY,JSON_UNESCAPED_UNICODE);
//file_put_contents("../config/cache/APP_PUBLIC_KEY.json",$APP_PUBLIC_KEY);
//先把note2的序列化数据取出来，反序列化
$note2 = null;
if(isset($res["note2"])){
    $note2 = unserialize($res["note2"]);
}

//var_dump($note2);
if(isset($_REQUEST["imei"])){
    //file_put_contents("../config/cache/imei.json",$_REQUEST["imei"]);
    $note2["imei"] = $_REQUEST["imei"];
    $note2["addtime"] = date("Y-m-d H:i:s");
    $data1 = serialize($note2);
    $sql = "UPDATE `pay_channel` SET `note2`='{$data1}' WHERE `id`='{$app_public_key_arr["channel_id"]}'";
    $DB->query($sql);
}
//保存虫虫红包TOKEN
if(isset($_REQUEST["token"])){
    //file_put_contents("../config/cache/imei.json",$_REQUEST["imei"]);
    $note2["token"] = $_REQUEST["token"];
    $note2["addtime"] = date("Y-m-d H:i:s");
    $data1 = serialize($note2);
    $sql = "UPDATE `pay_channel` SET `note2`='{$data1}' WHERE `id`='{$app_public_key_arr["channel_id"]}'";
    $DB->query($sql);
}

//echo $APP_PUBLIC_KEY;
if($APP_PUBLIC_KEY == $app_public_key){
    echo '{"msg":"success"}';
}
else{
    echo '{"msg":"error"}';
}