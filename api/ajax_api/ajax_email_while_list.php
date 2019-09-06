<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "../../includes/api/init.php";
if (!session_id()) {
    session_start();
}
if(!isset($_SESSION["is_admin"])){
    exit();
}
$file = "../../config/cache/".md5('email_white_list');
if(!isset($_REQUEST["type"])){
    echo '{"success":"false","msg":"参数不完整!"}';
    exit();
}
$type = $_REQUEST["type"];
if($type == "set"){
    if(!isset($_REQUEST["email"]) || !isset($_REQUEST["name"])){
        echo '{"success":"false","msg":"参数不完整!"}';
        exit();
    }
    $data = null;
    $name = daddslashes($_REQUEST["name"]);
    $email = daddslashes($_REQUEST["email"]);
    if(file_exists($file)){
        $data = unserialize(file_get_contents($file));
        $num = count($data);
        if($num>=10){
            echo '{"success":"false","msg":"处理人员不可超过十个！"}';
            exit();
        }
        $data[$num]["name"] = $name;
        $data[$num]["email"] = $email;
        $data[$num]["addtime"] = date("Y-m-d H:i:s");
    }
    else{
        $data[0]["name"] = $name;
        $data[0]["email"] = $email;
        $data[0]["addtime"] = date("Y-m-d H:i:s");
    }
    if(file_put_contents($file,serialize($data))){
        echo '{"success":"true","msg":"添加成功！"}';
    }
    else{
        echo '{"success":"false","msg":"添加失败！"}';
    }
}
else if($type == "delete"){
    if(!isset($_REQUEST["id"])){
        echo '{"success":"false","msg":"参数不完整!"}';
        exit();
    }
    $id = daddslashes($_REQUEST["id"]);
    if(file_exists($file)){
        $data = unserialize(file_get_contents($file));
        unset($data[$id]);
        $data = array_values($data);
        file_put_contents($file,serialize($data));
        echo '{"success":"true","msg":"删除成功！"}';
    }
    else{
        echo '{"success":"false","msg":"数据不存在！"}';
    }

}