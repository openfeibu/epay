<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/AES.class.php";
$token = "";
if(isset($_REQUEST["super_secret_key"])){
    $super_secret_key = $_REQUEST["super_secret_key"];
    $AES = new \com\weimifu\AES\AES("DECKmboEbp7Sfi3Fy7dPNpJl3fLojlUn");
    $token_key = $AES->decrypt($super_secret_key);
    $token_key_arr = explode("_",$token_key);
    $number = explode("z",$token_key_arr[1]);//var_dump($number);echo $aes->decrypt($aes_token_key);

    foreach ($number as $value){
        $token .= $token_key_arr[0][$value];
    }
    if($token = "mXTji7hXM4yhph7NUbokgS4oAxG4coPu"){
        //账户余额
        $sql4 = "SELECT `balance` FROM `pay_recharge` WHERE `id` = 0; ";
        $row3 = $DB->query($sql4)->fetch();
        $balance = round($row3['0'],0) / 100;
        //当前订单总数
        $today_begin  = date("Y-m-d"). ' 00:00:00';
        $sql5 = "SELECT COUNT(*) FROM `pay_order` WHERE `addtime`>='{$today_begin}' ";
        $row5 = $DB->query($sql5)->fetch();
        $order_today_sum = isset($row5['0'])?$row5['0']:0;
        $sql6 = "SELECT COUNT(*) FROM `pay_order` WHERE `addtime`>='{$today_begin}' AND `data` like '{\"is_mobile\":false%' ";
        $row6 = $DB->query($sql6)->fetch();
        $order_today_PC_sum = isset($row6['0'])?$row6['0']:0;
        $sql6 = "SELECT COUNT(*) FROM `pay_order` WHERE `addtime`>='{$today_begin}' AND `data` like '{\"is_mobile\":false%' AND `status`='1' ";
        $row6 = $DB->query($sql6)->fetch();
        $order_today_PC_sum_true = isset($row6['0'])?$row6['0']:0;
    }
    else{
        echo '{"error":"0"}';
        exit();
    }
}
else{
    if (!session_id()) {
        session_start();
    }

    if (isset($_SESSION['admin_id']) ) {
        $id   = $_SESSION['admin_id'];
    } else if (isset ($_SESSION['userid'])) {
        $id   = $_SESSION['userid'];
    } else {
        echo '{"error":"0"}';
        exit();
    }
    if (!isset ($_REQUEST["token"])) {                                                                              //根据token值判断执行什么
        exit();
    }
}
$token        = $_REQUEST["token"];                                                                                  //用于取回某一天的数据
$date =isset($_REQUEST["lastday_time"]) ? $_REQUEST["lastday_time"] : date("Y-m-d");
$lastday      = date("Y-m-d", strtotime("-1 day",strtotime($date))) . ' 00:00:00';                //昨天开始时间
$today_middle = $date . ' 12:00:00';                                                                                //今天中午
$today_begin  = $date . ' 00:00:00';                                                                                //今天开始
$today_end    = $date . "23:59:59";                                                                                 //今天结束
$year_month   = date("Y-m");                                                                                //当前月
$firstday     = "{$year_month}-01 00:00:00";                                                                       //这个月的开始
$index_api    = new index_api();
if ($token == "order_firstday") {
    echo $index_api->order_firstday($firstday,$today_begin);
}
if ($token == "order_today") {
    if(isset($_REQUEST["super_secret_key"])){
        $data11 = json_decode($index_api->order_today($today_begin),true);
        $data11["order_today_sum"] = $order_today_sum;
        $data11["order_today_PC_sum"] = $order_today_PC_sum;
        $data11["order_today_PC_sum_true"] = $order_today_PC_sum_true;
        echo json_encode($data11)."@".$balance;
    }
    else{
        echo $index_api->order_today($today_begin);
    }

}
if ($token == "order_lastday") {
    echo $index_api->order_lastday($lastday, $today_begin);
}

class index_api
{
    function order_today($today_begin)
    {                                    //今天的收入
        global $DB;
        $value                    = null;
        $sql                      = "SELECT count(*) as a,sum(money) as b from `pay_order` WHERE `status` = '1' AND `endtime` >= '{$today_begin}' ";
        $rs                       = $DB->query($sql);
        $row                      = $rs->fetch();
        $value["order_today"]     = $row[1]==null?0:$row[1]; //这里加入对null的处理
        $value["order_today_num"] = $row[0];
        return json_encode($value);
    }

    function order_lastday($lastday, $today_begin)
    {                         //昨天的收入
        global $DB,$cache;
        $json = null;
        //昨天收入因不变化，所以一天只要算一次，先读取缓存文件，如不存在，或过期再重新计算
        $data=$cache->get('lastday');
        if (empty($cache) || $data['lastday'] != $lastday) {
            //如果无缓存就执行查库
            $rs                         = $DB->query("SELECT count(*) as a,sum(money) as b from pay_order where `status` = 1 and endtime>='$lastday' and endtime<'{$today_begin}' limit 1");
            $row                        = $rs->fetch();
            $json["order_lastday"]      = $row[1]==null?0:$row[1]; //这里加入对null的处理
            $json["order_lastday_num"]  = $row[0];
            $json["lastday"]            = $lastday;
            $cache->put('lastday', $json);
        } else {
            //其它情况直接读取缓存
            $json["order_lastday"]      = $data['order_lastday'];
            $json["order_lastday_num"]  = $data['order_lastday_num'];
        }
        return json_encode($json);
    }

    function order_firstday($firstday,$today_begin)
    {                                   //这个月的收入
        global $DB,$cache;
        $json = null;
        //月初到昨天收入因不变化，所以一天只要算一次，先读取缓存文件，如不存在，或过期再重新计算
        $data=$cache->get('order_firstday');

        if (empty($cache) || $data['lastday'] != $today_begin) {
            //如果无缓存就执行查库
            $sql                        = "SELECT count(*) as a,sum(money) as b from `pay_order` WHERE `status` = '1' AND `endtime` >= '{$firstday}' AND `endtime` < '{$today_begin}'; ";
            $rs                         = $DB->query($sql);
            $row                        = $rs->fetch();
            $json["order_firstday"]     = $row[1]==null?0:$row[1]; //这里加入对null的处理
            $json["order_firstday_num"] = $row[0];
            $json["lastday"]            = $today_begin;
            $cache->put('order_firstday', $json);
        }
        else {
            //其它情况直接读取缓存
            $json["order_firstday"]     = $data['order_firstday'];
            $json["order_firstday_num"] = $data['order_firstday_num'];
        }
        return json_encode($json);
        //return '{"order_firstday":"' . $row[1] . '","order_firstday_num":"' . $row[0] . '"}';
    }

}

?>
