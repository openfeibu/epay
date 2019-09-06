<?php
defined('IN_WMF') or exit('Access Denied');
error_reporting(0);
if (!isset($DB)) {
    exit();
}
function getIP()
{
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');

    } elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$ipbj = getIP();
//file_put_contents("../config/cache/IPjilu.txt",$ipbj."\r\n", FILE_APPEND);
if ($whitemd == true) {
    $sql = "SELECT * FROM `pay_user` WHERE `url` = '{$ipbj}' limit 1";
    $userip = $DB->query($sql)->fetch();
    if (!$userip) {
        exit("您的服务器IP不在白名单范围内。");
    }
}
$result = array();
$order = array();

//先用$GLOBALS['HTTP_RAW_POST_DATA']来接收JSON
$msg = $GLOBALS['HTTP_RAW_POST_DATA'];
//如果不行的话，再尝试用php://input接收JSON参数
if (!$msg) $msg = file_get_contents("php://input");
if ($msg) {    //将接收到的数据转成数组
    $resultcode = json_decode($msg, true);
    foreach ($resultcode as $k => $v) {
        //为避免覆盖变量，只确定相应参数正常表单不存在才传递
        if (!isset($_REQUEST[$k])) $_REQUEST[$k] = $v;
    }
}

//确定支付类型
if (isset($_REQUEST['type']) && $_REQUEST['type'] != '') {
    $type = daddslashes($_REQUEST['type']);
    $type = strtolower($type);
} else {
    @header('Content-Type: application/json; charset=UTF-8');
    $json = true;
    $result['code'] = "0";
    $result['msg'] = "支付类型不存在";
    \epay\output($result, $json, true);
}

//根据type类型，确定返回类型
switch ($type) {
    case 'alipay2qr':
    case 'alipay2_url':
    case 'wechat2qr':
    case 'wechat2_url':
    case 'qqpay2qr':
    case 'qqpay2_url':
    case 'alipayh5_url':
    case 'yimei_url':
    case 'ousmd_url':
        $json = true;
        @header('Content-Type: application/json; charset=UTF-8');
        break;
    default:
        $json = false;
        @header('Content-Type: text/html; charset=UTF-8');
}
//根据format来判断返回类型，如果有format数据，以format为准
if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'json') {
    $json = true;
    @header('Content-Type: application/json; charset=UTF-8');
}


//require_once __DIR__."/../includes/api/debug.php";
//@header('Content-Type: text/html; charset=UTF-8');

//日志,改成分天
$file_date = date('Ymd');
$log_file = __DIR__ . DIRECTORY_SEPARATOR . "../etc/log/submit.log." . $file_date . ".php";
//判断日志文件存在与否
if (!file_exists($log_file)) {
    //不存在就生成
    file_put_contents($log_file, "<?php exit;?>" . PHP_EOL, FILE_APPEND);
}
$data = array();
$data['is_mobile'] = checkmobile2();
$data['mobile_style'] = $_G ? $_G['mobile'] : false;
$data['ip'] = real_ip();
$log = $_REQUEST;
$log = array_merge($log, $data);
\epay\log::writeLog($log_file, $log);


if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
    $pid = daddslashes($_REQUEST['pid']);
    $pid = intval($pid);
} else {
    $result['code'] = "0";
    $result['msg'] = "PID不存在";
    \epay\output($result, $json, true);
}

//判断sign是否存在
if (isset($_REQUEST['sign'])) {
    $sign = daddslashes($_REQUEST['sign']);
} else {
    $result['code'] = "0";
    $result['msg'] = "sign不存在";
    \epay\output($result, $json, true);
}
$sql = "SELECT * FROM `pay_user` WHERE `id` = '{$pid}' limit 1";
$userrow = $DB->query($sql)->fetch();
if (!$userrow) {
    //找不到商户
    $result['code'] = 0;
    $result['msg'] = "无此商户！600";
    \epay\output($result, $json, true);
}

//验证签名
$prestr = createLinkstring(argSort(paraFilter($_REQUEST)));
if (!md5Verify($prestr, strtolower($sign), $userrow['key'])) {
    $result['code'] = 0;
    if (!$json) {
        //$prestr = htmlentities($prestr,ENT_QUOTES,"UTF-8");
        $prestr = <<< EOF
<div id="prestr">
<script>
// 将HTML转义为实体
function escape(html){
    var elem = document.createElement('div')
    var txt = document.createTextNode(html)
    elem.appendChild(txt)
    return elem.innerHTML;
}
// 将实体转回为HTML
function unescape(str) {
    var elem = document.createElement('div')
    elem.innerHTML = str
    return elem.innerText || elem.textContent
}
window.onload = function() {
  var str = escape('md5({$prestr}****)');
  document.getElementById('prestr').innerHTML = str;
}
</script>
<pre>{$prestr}</pre>
</div>
EOF;
        $result['msg'] = "签名MD5校验失败，请返回重试！<br><br>签名方法应该是：<br>{$prestr}，其中****为你的商户密钥。<br>网站收到的sign值为：{$_REQUEST['sign']}";
    } else {
        $result['msg'] = "签名MD5校验失败，请返回重试！签名方法应该是：md5({$prestr}****)，其中****为你的商户密钥。网站收到的sign值为：{$_REQUEST['sign']}";
    }

    //签名失败
    \epay\output($result, $json, true);
}

if ($userrow['active'] == 0) {
    //商户已封禁
    $result['code'] = 0;
    $result['msg'] = "商户已封禁，无法支付！601";
    \epay\output($result, $json, true);
}

//检查必填参数是否完整
if (isset($_REQUEST['out_trade_no']) && isset($_REQUEST['notify_url']) && isset($_REQUEST['return_url']) && isset($_REQUEST['name']) && isset($_REQUEST['money'])) {
    $out_trade_no = daddslashes($_REQUEST['out_trade_no']);
    $notify_url = daddslashes($_REQUEST['notify_url']);
    $return_url = daddslashes($_REQUEST['return_url']);
    $name = daddslashes($_REQUEST['name']);
    $money = daddslashes($_REQUEST['money']);
} else {
    $result['code'] = "0";
    $result['msg'] = "参数不完整！";
    \epay\output($result, $json, true);
}

//检查可选参数
$sitename = isset($_REQUEST['sitename']) ? daddslashes($_REQUEST['sitename']) : "";
$attach = isset($_REQUEST['attach']) ? daddslashes($_REQUEST['attach']) : "";
$format = isset($_REQUEST['format']) ? daddslashes($_REQUEST['format']) : "";
$data['format'] = $format;


$result['code'] = 1;
if (empty($out_trade_no)) {
    $result['code'] = 0;
    $result['msg'] = "订单号(out_trade_no)不能为空";
}
if (empty($notify_url)) {
    $result['code'] = 0;
    $result['msg'] = "通知地址(notify_url)不能为空";
    sysmsg('');
}
if (empty($return_url)) {
    $result['code'] = 0;
    $result['msg'] = "回调地址(return_url)不能为空";
}
if (empty($name)) {
    $result['code'] = 0;
    $result['msg'] = "商品名称(name)不能为空";
}
if (empty($money)) {
    $result['code'] = 0;
    $result['msg'] = "金额(money)不能为空";
}
if ($money <= 0) {
    $result['code'] = 0;
    $result['msg'] = "金额不合法";
}
$cash_level = $userrow['cash_level'];
$cash_level = json_decode($cash_level, true);
if (!empty($cash_level)) {
    $min = $cash_level['min'];
    $max = $cash_level['max'];
    if ($min != 0 && $money < $min) {
        $result['code'] = 0;
        $result['msg'] = "金额不能小于{$min}元";
    }
    if ($max != 0 && $money > $max) {
        $result['code'] = 0;
        $result['msg'] = "金额不能大于{$max}元";
    }
}

if(strstr($money,'.')){
    //这里还要排除下，为0.00
    if(str_replace(".00","",$money)!=intval($money)){
        $result['code'] = 0;
        $result['msg'] = "金额带小数";
    }
}
if(substr($money,-1)=="0"){
    $result['code'] = 0;
    $result['msg'] = "金额个位数不能为0";
}

if ($result['code'] == 0) {
    \epay\output($result, $json, true);
}

$sql = "SELECT * FROM `pay_order` WHERE `pid` = '{$pid}' and `out_trade_no` = '{$out_trade_no}' limit 1";
$row = $DB->query($sql)->fetch();
$time = time();
$now = date("Y-m-d H:i:s", $time);
$now2 = date("YmdHis", $time);

$i = 0;
while (true) {
    if (substr($type, 0, 7) == 'alipay2' || substr($type, 0, 10) == 'yunshanpay' || $type == "yinshengpay") {
        //为了分布式，新的订单号加锁，使用数据库
        $trade_no = 0;
        $DB->beginTransaction();//开启事务
        $res = $DB->query("select `no` from `pay_storage` where id=1 for UPDATE ")->fetch();//利用for update 开启行锁
        //开始查找有没有相应的记录。
        if ($res) {
            for ($i = 1; $i < 1000; $i++) {
                //循环取一千个数字进行尝试
                $trade_no = $res['no'] + $i;
                $result = $DB->query("select trade_no from `pay_order` where `trade_no` = $trade_no")->fetch();
                if (empty($result)) {
                    //为空说明此订单数字可用，跳出循环
                    $sql = "update `pay_storage` set `no`='$trade_no' WHERE id=1";
                    if ($DB->query($sql)) {
                        $DB->commit();//提交事务
                    } else {
                        $pdo->rollBack();//回滚
                    }
                    break;
                }
                continue;
            }
        }
        if (empty($trade_no)) {
            //千次循环仍旧没有拿到合适的订单，那就直接报错
            $result['code'] = 0;
            $result['msg'] = 'no available trade number!';
            \epay\output($result, $json, true);
        }
    } elseif (substr($type, 0, 6) == "qqpay2") {
        //QQ备注长度不能超过12位！
        //9105600*****
        $trade_no = "9" . date("His") . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
    } else {
        //默认订单号
        $trade_no = $now2 . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
    }

    //=================================创建订单==================================

    $order["trade_no"] = $trade_no;
    $order['out_trade_no'] = $out_trade_no;
    $order['notify_url'] = $notify_url;
    $order['return_url'] = $return_url;
    $order['type'] = $type;
    $order['pid'] = $pid;
    $order['uuid'] = $userrow['uuid'];
    $order['agentuuid'] = $userrow['agentuuid'];
    $order['uid'] = $userrow['uid'];
    $order['now'] = $now;
    $order['name'] = $name;
    $order['money'] = $money;
    $order['data'] = json_encode($data, JSON_UNESCAPED_UNICODE);
    $order['attach'] = $attach;

    $order_result = \epay\order::create2($order);
    if ($order_result == 1) {
        $result['code'] = 0;
        $result['msg'] = "订单重复";
        \epay\output($result, $json, true);
    }
    if ($order_result['code'] == 0) {
        $result['code'] = 0;
        $result['msg'] = $order_result['msg'];
        if ($order_result['error'][1] == 1062 && !(strpos($order_result['msg'], "pid_out_trade_no") !== false)) {
            //主键重复，重新更换主键
            if ($i > 100 && false) {
                \epay\output($result, $json, true);
                break;
            }
            $i++;
            continue;
        } else {
            //其他错误，直接输出。
            \epay\output($result, $json, true);
        }
    } else {
        break;
    }
//=================================创建订单==================================

}

//转化支付类型
switch ($type) {
    case 'alipay':
    case 'alipay_url':
        $type2 = 'alipay';
        break;
    case 'yimei':
    case 'yimei_url':
        $type2 = 'yimei';
        break;
    case 'alipay_py1':
    case 'alipay_py2':
    case 'alipay_py3':
        $type2 = 'paiyi';
        break;
    case 'alipay_yw2':
        $type2 = 'ousmd';
        break;
    case 'alipay_mch5':
        $type2 = 'maicheng';
        break;
    case 'alipayh5':
    case 'alipayh5_url':
        $type2 = 'alipayh5';
        break;
    case 'alipay2'://个人版支付宝
    case 'alipay2qr'://个人版支付宝
    case 'alipay2_url':
        $type2 = "person_api";
        $type2 = "alipay2";
        if ($pid == '111fdsafdsa1') {
            $type2 = "alipay2";
        }
        break;
    case 'wechat2'://个人版微信
    case 'wechat2_url':
        $type2 = "person_api";
        $type2 = "wechat2";
        break;
    case 'qqpay2'://个人QQ钱包
    case 'qqpay2_url':
        $type2 = "person_api";
        break;
    case 'yunshanpay':
        $type2 = "yunshanpay";
        break;
    case 'yinshengpay':
        $type2 = "yinshengpay";
        break;
}

if (isset($type2)) {
    if ($type2 != "person_api") {

        //判断是不是支付宝支付，是则走带有钉钉的通道
        if ($type2 == "alipay2") {
            //查看是否有专用通道，有则使用专用通道，否则使用管理员通道，这里加入金额的判断
            $channels = $DB2->fetchRowMany("SELECT * FROM `pay_channel` WHERE (`total_amount`=0 OR `total_amount`=:total_amount) AND `uuid` = :uuid AND (`type` = :type OR `type`='ddpay') AND `status` = 1", ["total_amount" => $money, "uuid" => $userrow['uuid'], "type" => $type2]);
            if (!$channels) {
                if ($userrow['type'] == 1) {
                    $channels = $DB2->fetchRowMany("SELECT * FROM `pay_channel` WHERE (`total_amount`=0 OR `total_amount`=:total_amount) AND `uuid` = :uuid AND (`type` = :type OR `type`='ddpay') AND `status` = 1", ["total_amount" => $money, "uuid" => $userrow['uuid'], "type" => $type2]);
                }
            }
        } //判断是不是微信支付，是则走带有无忧支付的通道
        else if ($type2 == "wechat2") {
            //查看是否有专用通道，有则使用专用通道，否则使用管理员通道，这里加入金额的判断
            $channels = $DB2->fetchRowMany("SELECT * FROM `pay_channel` WHERE (`total_amount`=0 OR `total_amount`=:total_amount) AND `uuid` = :uuid AND (`type` = :type OR `type`='wuyoupay' OR `type`='cntpay') AND `status` = 1", ["total_amount" => $money, "uuid" => $userrow['uuid'], "type" => $type2]);
            if (!$channels) {
                if ($userrow['type'] == 1) {
                    $channels = $DB2->fetchRowMany("SELECT * FROM `pay_channel` WHERE (`total_amount`=0 OR `total_amount`=:total_amount) AND `uuid` = :uuid AND (`type` = :type OR `type`='wuyoupay' OR `type`='cntpay') AND `status` = 1", ["total_amount" => $money, "uuid" => $userrow['uuid'], "type" => $type2]);
                }
            }
        } else {
            //查看是否有专用通道，有则使用专用通道，否则使用管理员通道，这里加入金额的判断
            $channels = $DB2->fetchRowMany("SELECT * FROM `pay_channel` WHERE (`total_amount`=0 OR `total_amount`=:total_amount) AND `uuid` = :uuid AND `type` = :type AND `status` = 1", ["total_amount" => $money, "uuid" => $userrow['uuid'], "type" => $type2]);
            if (!$channels) {
                if ($userrow['type'] == 1) {
                    $channels = $DB2->fetchRowMany("SELECT * FROM `pay_channel` WHERE (`total_amount`=0 OR `total_amount`=:total_amount) AND `uuid` = :uuid AND `type` = :type AND `status` = 1", ["total_amount" => $money, "uuid" => $userrow['uuid'], "type" => $type2]);
                }
            }
        }

        if (!$channels) {
            $result['code'] = 0;
            $result['msg'] = "通道关闭！600";
            \epay\output($result, $json, true);
        }

        //查找可用通道
        $count = count($channels);

        //随机选择通道，这里要轮流来处理
        //$channel = rand(0,$count - 1);
        $channel = $cache->get($order['uuid'] . '_channelid');
        if ($channel >= $count) {//如果通道ID大于等于通道总数，重置为0
            $channel = 1;
        } else {
            $channel = $channel + 1; //通道ID加1

        }

        $cache->put($order['uuid'] . '_channelid', $channel); //将通道ID保存
        $channel_type = $channels[$channel - 1]['type'];//拿取通道的type
        $channel = $channels[$channel - 1]['id'];


        //查找重复金额
        $sql = "SELECT count(*) as c FROM `pay_order` where mobile_url ='{$channel}' and `status`=0 AND `money`=" . $money . "  AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
        $row = $DB->query($sql)->fetch();
        if ($row[c] > 0) { //有记录说明金额不可用
            $result['code'] = 0;
            $result['msg'] = "此金额不可用，请重试。";
            $sql = "delete from `pay_order` WHERE `trade_no` = '{$trade_no}';"; //订单做废
            $row = $DB->query($sql);
            \epay\output($result, $json, true);
        }

        //将通道信息录入订单
        $sql = "UPDATE `pay_order` SET `mobile_url` = '{$channel}' WHERE `trade_no` = '{$trade_no}';";
        $row = $DB->query($sql);
        //将云闪付订单信息录入
        if ($type2 == "yunshanpay") {
            $ys_time = date("Y-m-d H:i:s");
            $sql_ys = "INSERT INTO `pay_yspay`(`trade_no`,`mobile_url`,`money`,`addtime`) VALUES('{$trade_no}','{$channel}','{$money}','{$ys_time}');";
            $DB->query($sql_ys);
        }
        if ($type2 == "yinshengpay") {
            $ys_time = date("Y-m-d H:i:s");
            $sql_ys = "INSERT INTO `pay_yspay`(`trade_no`,`mobile_url`,`money`,`addtime`) VALUES('{$trade_no}','{$channel}','{$money}','{$ys_time}');";
            $DB->query($sql_ys);
        }
        if (!$row) {
            $result['code'] = 0;
            $result['msg'] = "录入通道信息失败，请重试！";
            \epay\output($result, $json, true);
        }
    }
    if ($type2 == "alipay2") {
        $type2 = "person_api2";
    }
    if ($type2 == "yunshanpay") {
        $type2 = "person_api2";
    }
    if ($type2 == "yinshengpay") {
        $type2 = "person_api2";
    }
    $url = "{$website_urls}api/{$type2}.php?trade_no={$trade_no}";
    if ($channel_type == "wuyoupay") {
        $url = "{$website_urls}includes/wuyoupay/submit.php?trade_no={$trade_no}&money={$money}&return_url={$return_url}";
    }
    if ($channel_type == "cntpay") {
        $url = "{$website_urls}includes/cntpay/submit.php?trade_no={$trade_no}&money={$money}&return_url={$return_url}";
    }
    //var_dump($url);
    //return;
    if ($json) {
        $result['code'] = 1;
        $result['msg'] = "获取成功";
        $result['payurl'] = $url;
        $result['mark'] = $trade_no;
        $result['trade_no'] = $trade_no;
        $result['type'] = $type;
        $result['now'] = date("Y-m-d H:i:s");
        $result['sign'] = \epay\getSign($result, $userrow['key']);
        \epay\output($result, $json, true);
    } else {
        $result['msg'] = "<script>window.location.href='{$url}'</script>";
    }
} else {
    switch ($type) {
        case 'cspay'://第4方支付宝
            echo "<script>window.location.href='cspayapi.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
            break;
        case 'personpay'://个人支付宝
            echo "<script>window.location.href='alipay_person_api.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
            break;
        case 'personpay_wx'://个人微信PC免签版
            echo "<script>window.location.href='weixin_person_api.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
            break;
        case 'personpayH5'://个人支付宝
            echo "<script>window.location.href='alipay_person_apiH5.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
            break;
        case 'cqpay'://第4方微信支付
            echo "<script>window.location.href='cqpayapi.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
            break;
        case 'wxpay'://微信
            //sysmsg('此支付方式暂时维护，请选择其他方式进行支付！');
            echo "<script>window.location.href='wxpay.php?out_trade_no={$out_trade_no}&sitename={$sitename}';</script>";//第三方
            //echo "<script>window.location.href='epayapi.php?out_trade_no={$out_trade_no}&type={$type}';</script>";//对接其他支付
            break;
        case 'qqpay'://QQ
            //sysmsg('此支付方式暂时维护，请选择其他方式进行支付！');
            echo "<script>window.location.href='qqpay.php?out_trade_no={$out_trade_no}&sitename={$sitename}';</script>";//第三方
            //echo "<script>window.location.href='epayapi.php?out_trade_no={$out_trade_no}&type={$type}';</script>";//对接其他支付
            break;
        case 'kuaikuai'://快快微信H5
            //sysmsg('此支付方式暂时维护，请选择其他方式进行支付！');
            echo "<script>window.location.href='./kuaikuai/index.php?pid={$pid}&tradeno={$trade_no}&money={$money}';</script>";//第三方
            break;
        case 'tonglian2'://通联当面付
            //sysmsg('此支付方式暂时维护，请选择其他方式进行支付！');
            echo "<script>window.location.href='../demo/demo5/dangm.php?pid={$pid}&tradeno={$trade_no}&money={$money}';</script>";//第三方
            break;
        case 'tonglian3'://通联支付宝扫码
            //sysmsg('此支付方式暂时维护，请选择其他方式进行支付！');
            echo "<script>window.location.href='../demo/demo5/alipay.php?pid={$pid}&tradeno={$trade_no}&money={$money}';</script>";//第三方
            break;
        case 'ddqr'://钉钉 支付宝扫码
            echo "<script>window.location.href='./dingding/p/pay.php?pid={$pid}&tradeno={$trade_no}&money={$money}&return={$return_url}';</script>";//第三方
            break;
        case 'bank'://网银 输入卡号密码支付
            echo "<script>window.location.href='./bank/pay.php?pid={$pid}&tradeno={$trade_no}&money={$money}&return={$return_url}';</script>";//第三方
            break;
        case 'alipay2qr'://个人版支付宝（只提供二维码）
        case 'wechat2qr'://个人版微信（只提供二维码）
        case 'qqpay2qr'://个人版QQ钱包（只提供二维码）
            //require_once __DIR__.DIRECTORY_SEPARATOR."person_api/lunxun.php";
            require_once __DIR__ . DIRECTORY_SEPARATOR . "person_api/config.php";

            if ($type == "alipay2qr") {
                $type_topay = "alipay";
            } elseif ($type == "wechat2qr") {
                $type_topay = "wechat";
            } elseif ($type == "qqpay2qr") {
                $type_topay = "qq";
            } else {
                $type_topay = "";
            }
            //获取二维码
            $i = 0;
            $j = 2;//循环次数
            while ($i < $j) {
                $mobile_url = \epay\person_api::get_mobile_url($trade_no);
                $data = \epay\person_api::get_qrcode($mobile_url, $money, $trade_no, $type_topay);
                if ($data['code'] == 1) {
                    break;
                    $i++;
                }
            }
            if ($data['code'] == 0) {
                //获取超时
                $result['code'] = 0;
                $result['msg'] = $data['msg'];
                $result['payurl'] = "";
                $result['mark'] = $trade_no;
                $result['trade_no'] = $trade_no;
                $result['type'] = $type;
                $result['now'] = date("Y-m-d H:i:s");
                $result['sign'] = \epay\getSign($result, $userrow['key']);
                \epay\output($result, $json, true);
                exit();
            }


            $result['code'] = 1;
            $result['msg'] = "获取成功";
            $result['payurl'] = $data['payurl'];
            $result['mark'] = $trade_no;
            $result['trade_no'] = $trade_no;
            $result['money'] = $data['money'];
            $result['account'] = $data['account'];
            $result['type'] = $type;
            $result['now'] = date("Y-m-d H:i:s");
            $result['sign'] = \epay\getSign($result, $userrow['key']);
            \epay\output($result, $json, true);
            //getpay($mobile_url,$money,$trade_no,$type_request);
            exit();
            break;
        default:
            $result['msg'] = "支付类型错误，请联系商家解决。";//echo "<script>window.location.href='default.php?out_trade_no={$out_trade_no}&sitename={$sitename}';</script>";
            ;
    }
}


isset($result['msg']) ? $msg = $result['msg'] : $msg = "";
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>正在为您跳转到支付页面，请稍候...</title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
        }

        p {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 330px;
            height: 30px;
            margin: -35px 0 0 -160px;
            padding: 20px;
            font: bold 14px/30px "宋体", Arial;
            background: #f9fafc url(../images/loading.gif) no-repeat 20px 26px;
            text-indent: 22px;
            border: 1px solid #c5d0dc;
        }

        html, body {
            height: 100%;
        }

        .main {
            height: 100%;
            width: 100%;
            display: table;
        }

        .wrapper {
            display: table-cell;
            height: 100%;
            vertical-align: middle;
            text-align: center;
        }
    </style>
    <script>
        function open_without_referrer(link) {
            document.body.appendChild(document.createElement('iframe')).src = 'javascript:"<script>top.location.replace(\'' + link + '\')<\/script>"';
        }
    </script>
</head>
<body>
<div class="main">
    <div class="wrapper" style="font-weight: bold;font-size: 20px;background: #f9fafc">
        正在为您跳转到支付页面，请稍候...<?php echo $msg; ?>
    </div>
</div>
</body>
</html>