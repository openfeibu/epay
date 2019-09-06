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
if(!isset($_REQUEST["validation_key"]) || $_REQUEST["validation_key"]==""){
    echo '{"success":"false","msg":"参数缺失！"}';
    exit();
}
$sql = "SELECT * FROM `pay_user` WHERE `id`='{$id}'";
$row = $DB->query($sql)->fetch();
if(!isset($row["note2"])){
    echo '{"success":"false","msg":"未发送令牌不可验证！"}';
    exit();
}
$email = unserialize($row["note2"]);
$date = date("Y-m-d H:i:s",strtotime("-5 minute"));
//var_dump($email);
//echo $date."<br>".$email["em_time"];
if($email["em_time"]<$date){
    echo '{"success":"false","msg":"该令牌已经失效！"}';
    exit();
}
if($_REQUEST["validation_key"] == $email["em_token"]){
    echo '{"success":"true","msg":"令牌验证通过，请在30秒内复制完成！否则需要重新验证！","key":"'.$row["key"].'"}';
}
else{
    echo '{"success":"false","msg":"请输入正确的令牌！"}';
    exit();
}