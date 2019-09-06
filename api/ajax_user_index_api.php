<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
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
if (!isset ($_GET["token"])) {                                                    //根据token值判断执行什么
    exit();
}
$token        = $_GET["token"];
$pid          = $_GET["pid"];
$lastday      = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';                       //昨天开始时间
$today_middle = date("Y-m-d") . ' 12:00:00';                                      //今天中午
$today_begin  = date("Y-m-d") . ' 00:00:00';                                       //今天开始
$today_end    = date("Y-m-d") . "23:59:59";                                          //今天结束
$year_month   = date("Y-m");                                                      //当前月
$firstday     = "{$year_month}-01 00:00:00";                                        //这个月的开始
$user_index_api    = new user_index_api();
if ($token == "order_today") {
    echo $user_index_api->order_today($today_begin,$pid);
}
if ($token == "order_lastday") {
    echo $user_index_api->order_lastday($lastday, $today_begin,$pid);
}

class user_index_api
{
    //今天的收入
    function order_today($today_begin,$pid)
    {                                    
        global $DB;
        $value                    = null;
        $sql = "SELECT SUM(money) from `pay_order` WHERE `status` = 1 AND (`pid` = '{$pid}' OR `uid` = '{$pid}') AND `endtime`>='{$today_begin}'";
        $sql1 = "SELECT COUNT(money) from `pay_order` WHERE (`pid` = '{$pid}' OR `uid` = '{$pid}') AND `addtime`>='{$today_begin}'";
        $row = $DB->query($sql)->fetch();
        $row1 = $DB->query($sql1)->fetch();
        $value["order_today"]     = $row[0]==null?0:$row[0];                                                         //这里加入对null的处理,今天的收入
        $value["order_sum"] = $row1[0]+$this->order_sum($today_begin,$pid);                                    //总订单数
        return json_encode($value);
    }

    //总订单计算至昨天
    function order_sum($today_begin,$pid){
        global $DB,$cache;
        $json = null;
        $value = null;
        //昨天到以前的订单数不变
        $data=$cache->get('order_sum_'.$pid);
        if (empty($cache) || $data['today_begin'] != $today_begin) {
            //如果无缓存就执行查库
            $rs                         = $DB->query("SELECT COUNT(money) from `pay_order` WHERE (`pid` = '{$pid}' OR `uid` = '{$pid}') AND `addtime`<'{$today_begin}'");
            $row                        = $rs->fetch();
            $value = $row[0];
            $json["order_sum"]  = $row[0];
            $json["today_begin"]            = $today_begin;
            $cache->put('order_sum_'.$pid, $json);
        }else {
            //其它情况直接读取缓存
            $value = $data['order_sum'];
            $json["order_sum"]  = $data['order_sum'];
        }
        return $value;
    }

    //昨天的收入
    function order_lastday($lastday, $today_begin,$pid)
    {
        global $DB,$cache;
        $json = null;
        //昨天收入因不变化，所以一天只要算一次，先读取缓存文件，如不存在，或过期再重新计算
        $data=$cache->get('lastday_'.$pid);
        if (empty($cache) || $data['lastday'] != $lastday) {
            //如果无缓存就执行查库
            $rs                         = $DB->query("SELECT SUM(money) from `pay_order` WHERE `status` = 1 AND (`pid` = '{$pid}' OR `uid` = '{$pid}') AND `endtime`>='{$lastday}' AND `endtime`<='{$today_begin}'");
            $row                        = $rs->fetch();
            $json["order_lastday"]      = $row[0]==null?0:$row[0]; //这里加入对null的处理
            $json["lastday"]            = $lastday;
            $cache->put('lastday_'.$pid, $json);
        } else {
            //其它情况直接读取缓存
            $json["order_lastday"]      = $data['order_lastday'];
        }
        return json_encode($json);
    }

}

?>
