<?php
require_once __DIR__.'/../includes/api/init.php';
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
header("Content-type:application/json;charset=utf-8");
date_default_timezone_set("Asia/Hong_Kong");

$result = array();
if(isset($_REQUEST['act']) && isset($_REQUEST['pid'])){
    $act = \epay\daddslashes($_REQUEST['act']);
    $pid = \epay\daddslashes($_REQUEST['pid']);
    $sign = isset($_REQUEST['sign']) ? \epay\daddslashes($_REQUEST['sign']) : "";
}else{
    $result['result'] = 'error';
    $result['msg'] = '参数不完整';
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
    exit();
}

//获取商户密钥
$user = \epay\user::find_user($pid);
$key = $user['key'];
//验证签名
$data = $_REQUEST;
$sign2 = \epay\getSign($data,$key);
//查询订单不验证签名(如果已发送sign，则验证签名)，其他接口需要验证签名
if($act == "order"){
    if($sign != "" && $sign != $sign2){
        $result['result'] = 'error';
        $result['msg'] = '签名错误';
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit();
    }
}else{
    if($sign != $sign2){
        $result['result'] = 'error';
        $result['msg'] = '签名错误';
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit();
    }
}


switch($act){
    case 'add':
        exit('{"code":-4,"msg":"当前接口仅作为备用接口使用"}');
        $type = 1;
        $key = random(32);
        $sds = $DB->query("INSERT INTO `pay_user` (`key`, `url`, `addtime`, `type`, `active`) VALUES ('{$key}', '{$url}', '{$date}', '{$type}', '1')");
        $pid = $DB->lastInsertId();

        if($sds){
            $result = array("code" => 1,"msg" => "添加支付商户成功！","pid" => $pid,"key" => $key,"type" => $type);
        }else{
            $result = array("code" => -1,"msg" => "添加支付商户失败！");
        }
        break;
    case 'apply':
        $token = \epay\daddslashes($_REQUEST['token']);
        $row = $DB->query("SELECT * FROM panel_user WHERE token='{$token}' limit 1")->fetch();
        if($row && $row['active'] == 1){
            $type = 0;
            $key = random(32);
            $sds = $DB->query("INSERT INTO `pay_user` (`key`, `url`, `addtime`, `type`, `active`, `uid`) VALUES ('{$key}', '{$url}', '{$date}', '{$type}', '1', '{$row['id']}')");
            $pid = $DB->lastInsertId();

            if($sds){
                $result = array("code" => 1,"msg" => "添加支付商户成功！","pid" => $pid,"key" => $key,"type" => $type);
            }else{
                $result = array("code" => -1,"msg" => "添加支付商户失败！");
            }
        }else{
            $result = array("code" => -1,"msg" => "TOKEN ERROR");
        }
        break;
    case 'query':
        $pid = intval($_REQUEST['pid']);
        $key = \epay\daddslashes($_REQUEST['key']);
        $row = $DB->query("SELECT * FROM pay_user WHERE id='{$pid}' limit 1")->fetch();
        if($row){
            if($key == $row['key']){
                $result = array(
                    "code"         => 1,
                    "pid"          => $pid,
                    "key"          => $key,
                    "type"         => $type,
                    "active"       => $row['active'],
                    "money"        => $row['money'],
                    "account"      => $row['account'],
                    "username"     => $row['username'],
                    "settle_money" => $conf['settle_money'],
                    "settle_fee"   => $conf['settle_fee'],
                    "money_rate"   => $conf['money_rate'],
                );
            }else{
                $result = array("code" => -2,"msg" => "KEY校验失败");
            }
        }else{
            $result = array("code" => -3,"msg" => "PID不存在");
        }
        break;
    case 'change':
        $pid = intval($_REQUEST['pid']);
        $key = \epay\daddslashes($_REQUEST['key']);
        $account = \epay\daddslashes($_REQUEST['account']);
        $username = \epay\daddslashes($_REQUEST['username']);
        $row = $DB->query("SELECT * FROM pay_user WHERE id='{$pid}' limit 1")->fetch();
        if($row){
            if($key == $row['key']){
                if($account == null || $username == null){
                    $result = array("code" => -1,"msg" => "保存错误,请确保每项都不为空!");
                }elseif($row['type'] != 2 && !empty($row['account']) && !empty($row['username']) && $row['account'] != $account && (strpos($row['account'],'@') || strlen($row['account']) == 11)){
                    $result = array("code" => -1,"msg" => "为保障您的资金安全，暂不支持直接修改结算账号信息，如需修改请联系QQ1277180438");
                }else{
                    $type = 1;
                    $sds = $DB->query("update `pay_user` set `account`='{$account}',`username`='{$username}',`type`='{$type}',`url`='{$url}' where id='{$pid}' limit 1");
                    if($sds >= 0){
                        $result = array("code" => 1,"msg" => "修改收款账号成功！","pid" => $pid,"key" => $key,"type" => $type);
                    }else{
                        $result = array("code" => -1,"msg" => "修改收款账号失败！");
                    }
                }
            }else{
                $result = array("code" => -2,"msg" => "KEY校验失败");
            }
        }else{
            $result = array("code" => -3,"msg" => "PID不存在");
        }
        break;
    case 'settle':
        $pid = intval($_REQUEST['pid']);
        $key = \epay\daddslashes($_REQUEST['key']);
        $limit = $_REQUEST['limit'] ? intval($_REQUEST['limit']) : 10;
        if($limit > 50) $limit = 50;
        $row = $DB->query("SELECT * FROM pay_user WHERE id='{$pid}' limit 1")->fetch();
        if($row){
            if($key == $row['key']){
                $rs = $DB->query("SELECT * FROM pay_settle WHERE pid='{$pid}' order by id desc limit {$limit}");
                while($row = $rs->fetch()){
                    $data[] = $row;
                }
                if($rs){
                    $result = array(
                        "code" => 1,
                        "msg"  => "查询结算记录成功！",
                        "pid"  => $pid,
                        "key"  => $key,
                        "type" => $type,
                        "data" => $data,
                    );
                }else{
                    $result = array("code" => -1,"msg" => "查询结算记录失败！");
                }
            }else{
                $result = array("code" => -2,"msg" => "KEY校验失败");
            }
        }else{
            $result = array("code" => -3,"msg" => "PID不存在");
        }
        break;
    case 'order':
        $out_trade_no = \epay\daddslashes($_REQUEST['out_trade_no']);
        if(empty($out_trade_no) || empty($pid)){
            $result = [
                'code'   => -1,
                'status' => -1,
                'msg'    => "部分参数为空",
            ];
            break;
        }
        $sql = "SELECT * FROM `pay_order` WHERE `pid` = '{$pid}' AND `out_trade_no` = '{$out_trade_no}' limit 1";
        $row = $DB->query($sql)->fetch();
        if($row){
            if($row['status'] == '1'){
                $msg = '支付成功';
                $endtime = $row['endtime'];
            }elseif($row['status'] == '2'){
                $msg = "已关闭";
                $endtime = "";
            }else{
                $msg = "未付款";
                $endtime = "";
            }

            $result = [
                'code'         => $row['status'],
                'status'       => $row['status'],
                'msg'          => $msg,
                'trade_no'     => $row['trade_no'],
                'out_trade_no' => $row['out_trade_no'],
                'type'         => $row['type'],
                'pid'          => $row['pid'],
                'addtime'      => $row['addtime'],
                'endtime'      => $endtime,
                'name'         => $row['name'],
                'money'        => $row['money'],
            ];
        }else{
            $result['code'] = -1;
            $result['msg'] = '商户订单号不存在';
        }
        break;
    case 'orders':
        $pid = intval($_REQUEST['pid']);
        $key = \epay\daddslashes($_REQUEST['key']);
        $limit = $_REQUEST['limit'] ? intval($_REQUEST['limit']) : 10;
        if($limit > 50) $limit = 50;
        $row = $DB->query("SELECT * FROM pay_user WHERE id='{$pid}' limit 1")->fetch();
        if($row){
            if($key == $row['key']){
                $rs = $DB->query("SELECT * FROM pay_order WHERE pid='{$pid}' order by addtime desc limit {$limit}");
                while($row = $rs->fetch()){
                    $data[] = $row;
                }
                if($rs){
                    $result = array("code" => 1,"msg" => "查询订单记录成功！","data" => $data);
                }else{
                    $result = array("code" => -1,"msg" => "查询订单记录失败！");
                }
            }else{
                $result = array("code" => -2,"msg" => "KEY校验失败");
            }
        }else{
            $result = array("code" => -3,"msg" => "PID不存在");
        }
        break;
    default:
        $result = array("code" => -5,"msg" => "No Act!");
        break;
}

//添加回复时间
$result['now'] = date("Y-m-d H:i:s");
//对结果进行签名
$sign = \epay\getSign($result,$key);
$result['sign'] = $sign;
echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>