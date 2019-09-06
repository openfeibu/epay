<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../../config_base.php";
if(!session_id()){
    session_start();
}
if(isset($_SESSION['userid'])){
    $id = $_SESSION['userid'];
}elseif(isset($_SESSION['admin_id'])){
    $id = $_SESSION['admin_id'];
}else{
    echo '{"status":false,"msg":"无访问权限！"}';
    exit();
}

$data["id"] = $_REQUEST["agent_id"];
$data["com_phone"] = serialize(array("phone"=>$_REQUEST["phone"],"pass"=>"973546","time"=>date("Y-m-d H:i:s")));

$sql = "UPDATE `pay_user` SET `com_phone`=:com_phone WHERE `id`=:id";
$stmt = $DB->prepare($sql);
if($stmt->execute($data)){
    echo '{"status":true,"msg":"修改成功！"}';
    exit();
}
else{
    echo '{"status":false,"msg":"修改失败！"}';
    exit();
}