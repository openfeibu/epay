<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../config_base.php";
if(!session_id()){
    session_start();
}
if(isset($_SESSION['userid'])){
    $id = $_SESSION['userid'];
}elseif(isset($_SESSION['admin_id'])){
    $id = $_SESSION['admin_id'];
}else{
    echo "error";
    exit();
}
$sql = "SELECT * FROM `pay_recharge` WHERE `id` = '0' ";
$result = $DB->query($sql)->fetch();
if(!$result){
    echo "error";
    exit();
}
$balance = round($result['balance']/100,2);
echo $balance;
