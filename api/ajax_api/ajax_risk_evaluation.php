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
    echo '{"error":"0","msg":"抱歉您暂无操作权限！"}';
    exit();
}

if(!isset($_REQUEST["token"]) || !isset($_REQUEST["mobile_url"])){
    echo '{"error":"0","msg":"参数不完整！"}';
    exit();
}
$token = $_REQUEST["token"];
$risk_evaluation = new risk_evaluation();
if($token == "get_risk_evaluation"){
    echo $risk_evaluation->get_risk_evaluation($_REQUEST["mobile_url"]);
}


class risk_evaluation{
    //获取风险测评的数据
    function get_risk_evaluation($mobile_url){
        global $DB;                                                                                                    //全局数据库操作变量$DB
        $date = date("Y-m-d 00:00:00");                                                                      //测评时间（今天）
        $sql = "SELECT count(money),sum(money) FROM pay_order WHERE status=1 AND addtime >= '$date' AND mobile_url = '$mobile_url'";              //查询成功交易的量
        $sql1 = "SELECT count(money),sum(money) FROM pay_order WHERE status!=1 AND addtime >= '$date' AND mobile_url = '$mobile_url'";            //查询成功交易的量
        $res = $DB->query($sql)->fetch();
        $res1 = $DB->query($sql1)->fetch();
        $value["success_order_num"] = isset($res[0])?$res[0]:0;                                                                       //交易成功的订单数
        $value["success_order_money"] = isset($res[1])?$res[1]:0;                                                                     //交易成功的订单金额
        $value["error_order_num"] = isset($res1[0])?$res1[0]:0;                                                                        //交易失败的订单数
        $value["error_order_money"] = isset($res1[1])?$res1[1]:0;                                                                      //交易失败的订单金额
        return json_encode($value);
    }
}