<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../../config_base.php";
if(!session_id()){
    session_start();
}
if(isset($_SESSION['userid'])){
    $id = $_SESSION['userid'];
}
else{
    echo '{"success":"false","msg":"非法操作！"}';
    exit();
}
/*去除时间限制
$begintime = date("Y-m-d 00:00:00");
$endtime = date("Y-m-d 23:59:59");

$sql = " `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' and status=0";

 */
if(!isset($_REQUEST["qq_email"])){
    echo '{"success":"false","msg":"参数缺失！"}';
    exit();
}
$row = $DB->query("SELECT * FROM `pay_user` WHERE `id`='{$id}'")->fetch();
if(isset($row["note2"])){
    echo '{"success":"false","msg":"重复绑定属于非法操作！"}';
    exit();
}
$data["email"] = daddslashes($_REQUEST["qq_email"]);
$data["up_num"] = "1";//修改次数
$data["em_token"] = rand(1,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
$data["em_time"] = date("Y-m-d H:i:s",strtotime("-6 minute"));
$data["up_time"] = date("Y-m-d H:i:s");
$data = serialize($data);
//var_dump($data);
$sql2 = "UPDATE `pay_user` SET `note2`='{$data}' WHERE `id`='{$id}'";                        //查询语句
$numrows = $DB->query($sql2);
if($numrows){
    echo '{"success":"true","msg":"邮箱绑定成功！"}';
}
else{
    echo '{"success":"true","msg":"邮箱绑定失败，请重试！"}';
}
?>
