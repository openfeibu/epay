<?php
defined('IN_WMF') or exit('Access Denied');
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set("Asia/Hong_Kong");
//写日志也要按天来生成
$file_date=date('Ymd');
$log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/notify3.log.".$file_date.".php";
$log2_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/notify3.log2.".$file_date.".php";
$log_error_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/notify3.log_error.".$file_date.".php";

//判断日志文件存在与否
if(!file_exists($log_file)){
		//不存在就生成
		file_put_contents($log_file, "<?php exit;?>".PHP_EOL, FILE_APPEND);
	}
if(!file_exists($log2_file)){
		//不存在就生成
		file_put_contents($log2_file, "<?php exit;?>".PHP_EOL, FILE_APPEND);
	}
if(!file_exists($log_error_file)){
		//不存在就生成
		file_put_contents($log2_file, "<?php exit;?>".PHP_EOL, FILE_APPEND);
}


//日志
require_once __DIR__."/../includes/api/autoload.php";
require_once __DIR__."/../includes/api/function.php";
$log = $_REQUEST;
$log["file"] = __FILE__;
\epay\log::writeLog($log_file,$log);

//查看是否设置APPKEY
if(!defined("VERSIONVKEY")){
    //此文件不能单独访问
    echo "error";
    exit();
}
if(isset($_REQUEST['sign']) && isset($_REQUEST['userids']) && isset($_REQUEST['version'])){
    $sign = $_REQUEST['sign'];
    $userid = $_REQUEST['userids'];
    $version = $_REQUEST['version'];
    $data = $_REQUEST;
    $money = $_REQUEST['money'];
    unset($data['sign']);
}else{
    echo "success:600/参数不完整。";
    exit();
}

require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";


//根据商户类型，选择密钥
$user = \epay\user::find_user($userid);
if($user['type'] == '2'){
    $signkey_others = $user['key'];
}else{
    $user_others = \epay\user::find_user_others($userid);
    $signkey_others = $user_others['key'];
}


//增加软件签名验证
$version = explode('@',$version);
switch($version[0]){
    case '1':
    case '3':
        $key = VERSIONVKEY;
        break;
    case '2':
        $key = VERSIONVKEY;
        break;
    default:
        $key = VERSIONVKEY;
        break;
}
$signkey_others .= $key;

//获取备注
$dt = explode('@',$_REQUEST['dt']);
if(isset($dt[1])){
    $note = $dt[1];
}else{
    $note = "";
}

//加入相应的特殊支付宝备注模式处理,新加缩短备注
if(is_int(strpos($data['mark'],","))){
    $trade_no_arr = explode(",",$data["mark"]);
    $trade_no = $trade_no_arr[0];
    if($trade_no=="收款"){
        $trade_no="2088111111"; //兼容转账递减模式
    }
    $mobile_url_phone = $trade_no_arr[1];
}else{
    $trade_no =$data['mark'];
}
if(trim($data['mark'])=="收款"){
    $trade_no="2088111111"; //新版本转账单独提交通道ID兼容
}
if(isset($_REQUEST["channel_id"])){ //新版本有提交通道ID就提取
    $mobile_url_phone = $_REQUEST["channel_id"];
}
$trade_no = preg_replace('/\D/s', '', $trade_no);
$trade_no_new=substr(date("Y-m-d"),3,1).$trade_no.str_pad(date("z"),3,"0",STR_PAD_LEFT);

//检查是否转账码模式
if(substr($userid,0,3) == "99@"){
	if($_REQUEST['no']=="000000") {
        //特殊模式转账到银行卡
        $money = str_replace(",", "", $money);
        $money = str_replace(" ", "", $money);
        //添加通道进行判断
        $sql = "SELECT * FROM `pay_order` where mobile_url in(select id from pay_channel where right(public_key,4)='" . $trade_no . "' AND `id`='{$mobile_url_phone}') and `status`=0 AND `money`-`money2`=" . $money . " AND addtime>=DATE_SUB(NOW(),INTERVAL 10 MINUTE)";
        $getresult = $DB->query($sql);
        $result = $getresult->fetch();
        if ($result) {
            //找到记录，再判断第二条记录
            $result1 = $getresult->fetch();
            if (!$result1) { //不存在第二条记录，即只有一条结果集配得上，其它情况直接掉单
                $channel = \epay\channel::find($result['mobile_url']);
                if ($channel) {
                    $signkey_others = $channel['private_key'] . $key;
                    $trade_no = $result['trade_no'];
                    $order = $result;
                }
            }
        }
    }elseif($_REQUEST['no']=="111111"){
            //银行固码
            $sql = "SELECT * FROM `pay_order` where mobile_url ='$data[mark]'  and `status`=0 AND `money`-`money2`=".$money."  AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
            $getresult=$DB->query($sql);
            $result = $getresult->fetch();
            if($result){
                //找到记录，再判断第二条记录
                $result1 = $getresult->fetch();
                if(!$result1) { //不存在第二条记录，即只有一条结果集配得上，其它情况直接掉单
                    $channel = \epay\channel::find($result['mobile_url']);
                    if ($channel) {
                        $signkey_others = $channel['private_key'] . $key;
                        $trade_no       = $result['trade_no'];
                        $order          = $result;
                    }
                }
            }else{
                //订单信息不匹配
                $result = "success:607/订单信息不匹配";
                $error = $_REQUEST;
                $error['error'] = $result;
                \epay\log::writeLog($log_error_file,$error);
                echo $result;
                exit();
            }
        }
    elseif($_REQUEST['no']=="222222"){
        //银行固码
        $sql = "SELECT * FROM `pay_order` where trade_no ='$data[mark]'  and `status`=0 AND `money`-`money2`=".$money."  AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
        $getresult=$DB->query($sql);
        $result = $getresult->fetch();
        if($result){
            //找到记录，再判断第二条记录
            $result1 = $getresult->fetch();
            if(!$result1) { //不存在第二条记录，即只有一条结果集配得上，其它情况直接掉单
                $channel = \epay\channel::find($result['mobile_url']);
                if ($channel) {
                    $signkey_others = $channel['private_key'] . $key;
                    $trade_no       = $result['trade_no'];
                    $order          = $result;
                }
            }
        }else{
            //订单信息不匹配
            $result = "success:607/订单信息不匹配";
            $error = $_REQUEST;
            $error['error'] = $result;
            \epay\log::writeLog($log_error_file,$error);
            echo $result;
            exit();
        }
    }elseif(substr($trade_no,0,4)=="2088"){
        //扫码点单,支付宝固码
		$sql = "SELECT * FROM `pay_order` where mobile_url ='{$mobile_url_phone}' and `status`=0 AND `money`-`money2`=".$money."  AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
		$getresult=$DB->query($sql);
		$result = $getresult->fetch();
		if($result){
			//找到记录，再判断第二条记录
			$result1 = $getresult->fetch();
			if(!$result1) { //不存在第二条记录，即只有一条结果集配得上，其它情况直接掉单
				$channel = \epay\channel::find($result['mobile_url']);
				if ($channel) {
					$signkey_others = $channel['private_key'] . $key;
					$trade_no       = $result['trade_no'];
					$order          = $result;
				}
			}
		}else{
            //订单信息不匹配
            $result = "success:607/订单信息不匹配";
            $error = $_REQUEST;
            $error['error'] = $result;
            \epay\log::writeLog($log_error_file,$error);
            echo $result;
            exit();
        }
    }elseif($_REQUEST['type']=="wechat"){
        //微信固码,这里要加入大号收款的处理
        $bbn=$_REQUEST['bill_boss_nickname'];
        $appid=$_REQUEST['userids'];
        if(empty($bbn)){
            $sql = "SELECT * FROM `pay_order` where mobile_url ='{$mobile_url_phone}' and `status`=0 AND `money`-`money2`=".$money."  AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE) AND note2 LIKE '%wxp://%'";
        }else{
            //店员通的回调
            $sql = "SELECT * FROM `pay_order` where mobile_url in( select id FROM pay_channel where appid='{$appid}' and public_key ='{$bbn}' and status=1) and `status`=0 AND `money`-`money2`=".$money."  AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE) AND note2 LIKE '%wxp://%'";
        }
        $getresult=$DB->query($sql);
        $result = $getresult->fetch();
        if($result){
            //找到记录，再判断第二条记录
            $result1 = $getresult->fetch();
            if(!$result1) { //不存在第二条记录，即只有一条结果集配得上，其它情况直接掉单
                $channel = \epay\channel::find($result['mobile_url']);
                if ($channel) {
                    $signkey_others = $channel['private_key'] . $key;
                    $trade_no       = $result['trade_no'];
                    $order          = $result;
                }
            }
        }else{
            //签名不正确
            $result = "success:607/订单信息不匹配";
            $error = $_REQUEST;
            $error['error'] = $result;
            \epay\log::writeLog($log_error_file,$error);
            echo $result;
            exit();
        }
    }else{
		//旧流程
		$order = $DB2->fetchRowMany("SELECT * FROM `pay_order` WHERE  addtime>=DATE_SUB(NOW(),INTERVAL 10 MINUTE) AND (`trade_no` = :trade_no OR `trade_no` = :trade_no_new)  AND `appid` = :appid",["trade_no" => $trade_no,"trade_no_new" => $trade_no_new,"appid" => $userid]);
		if(count($order) == 1){
			$order = $order[0];
			$mobile_url = $order['mobile_url'];
			$channel = \epay\channel::find($mobile_url);
			if($channel){
				$signkey_others = $channel['private_key'].$key;
			}
		}
		$outorder=$order["out_trade_no"];
	}
}

//获取签名
$sign2_others = \epay\payhelper::getSign_person($data,$signkey_others);


//检查签名
if($sign2_others != $sign){
    //签名不正确
    $result = "success:601/签名不正确";
    $error = $_REQUEST;
    $error['error'] = $result;
    \epay\log::writeLog($log_error_file,$error);
    echo $result;
    exit();
}

//不能使用默认密钥
if($signkey_others == '0'){
    $result = "success:602/不能用0作为密钥";
    $error = $_REQUEST;
    $error['error'] = $result;
    \epay\log::writeLog($log_error_file,$error);
    echo $result;
    exit();
}
if(isset($order)){
    $srow = $order;
}else{
    $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = :trade_no LIMIT 1";
    $re = $DB->prepare($sql);
    $re->execute(["trade_no" => $data['mark']]);
    $srow = $re->fetch();
}

if(!$srow){
    //找不到订单号
    $result = "success:603/找不到订单号";
    $error = $_REQUEST;
    $error['error'] = $result;
    \epay\log::writeLog($log_error_file,$error);
    echo $result;
    exit();
}

//验证付款金额，加入对金额递减模式的支持
if($_REQUEST['no']=="000000"){
    $p1=($srow['money'] - $srow['money2'])*100;
}
else{
    $p1=($srow['money'] - $srow['money2'])*100;
}

//验证付款金额，加入对金额递减模式的支持，修正金额小数字的判断，将金额全转换成字符型。
if(strval($p1)!= strval($money*100)){
    $result = "success:604/金额不对";
    $error = $_REQUEST;
    $error['error'] = $result;
    \epay\log::writeLog($log_error_file,$error);
    echo $result;
    exit();
}
$now = date("Y-m-d H:i:s");
// if($srow['money'] == '已收款'){
//     $result = "success:604/金额不对0000";
//     $error = $_REQUEST;
//     $error['error'] = $result;
//     $str = json_encode($error,JSON_UNESCAPED_UNICODE);
//     $str = date("Y-m-d H:i:s : ").$real_ip.$str.PHP_EOL;
//     file_put_contents($log_error_file,$str,FILE_APPEND);
// }

//查看订单是否已完成
//原有逻辑调整，非1都可以提交
//if($srow['status'] != 0){
if($srow['status'] == 1){
    $result = "success:606/该订单已完成。";
    $error = $_REQUEST;
    $error['error'] = $result;
    \epay\log::writeLog($log_error_file,$error);
    echo $result;
    exit();
}

//var_dump($srow);
//付款完成后，支付系统发送该交易状态通知
//同时验证商户ID和订单号
$sql = "update `pay_order` set `status` ='1',`endtime` ='{$now}', `buyer` = '{$data['no']}@{$data['account']}' where `pid` = '{$srow['pid']}' and `trade_no`='{$trade_no}' LIMIT 1";

if($user['type'] == '1' && !isset($order)){
	//结算用户，只验证订单号。
	$sql = "update `pay_order` set `status` ='1',`endtime` ='{$now}', `buyer` = '{$data['no']}@{$data['account']}' where `trade_no`='{$trade_no}' LIMIT 1";
}
//var_dump($sql);
$DB->query($sql);


//$addmoney=round($srow['money']*$conf['money_rate']/100,2);
//$DB->query("update pay_user set money=money+{$addmoney} where id='{$srow['pid']}'");

//$url=creat_callback($srow);
//if(!$DB->query("update `ims_ewei_shop_article` set  `resp_desc`='".$url['notify']."' where `id`=20"))exit('创建订单失败，请返回重试！');
//curl_get($url['notify']);
//proxy_get($url['notify']);
$result = "success";
EOF:
//$result = json_encode($result,JSON_UNESCAPED_UNICODE);
echo $result;
$url = $website_urls."api/return_url.php?trade_no={$trade_no}&notify=yes";
$call_back = file_get_contents($url);
$log = array();
$log['url'] = $url;
$log['call_back'] = $call_back;
\epay\log::writeLog($log2_file,$log);

/* 以下注释只有在需配合前端电商才打开
 $conn = new mysqli("localhost","56higo","t2v*mNFI7&0cxrHy","56higo");
if ($conn->connect_error) {
    reurn;
}
$conn ->query("SET NAMES utf8");//防止乱码
$sql = "UPDATE dsc_order_info SET order_status=1,shipping_status=1,pay_status=1,invoice_no='$outorder'
            where order_sn='$trade_no'";
$conn->query($sql);
$conn->close();
*/