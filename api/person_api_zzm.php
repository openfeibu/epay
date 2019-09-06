<?php
header("Content-type: text/html; charset=utf-8");
require_once __DIR__.DIRECTORY_SEPARATOR.'../config_base.php';
//require_once __DIR__.DIRECTORY_SEPARATOR.'../includes/api/debug.php';

$type2 = array("alipay2","wechat2","qqpay2","alipay2_url","wechat2_url","qqpay2_url","alipay2qr","wechat2qr","qqpay2qr","yunshanpay","yinshengpay");
//查找订单信息
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";
$DB->exec("SET SESSION wait_timeout=300");
$trade_no = daddslashes($_REQUEST['trade_no']);
$sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' AND `status` = '0' LIMIT 1";
$result = $DB->query($sql)->fetch();
if(!$result){
    $error = "该订单不存在或者已支付完成。";
    echo $error;
    exit();
}

//判断支付方式是否正确
$type = $result['type'];
if(!in_array($type,$type2)){
    $error = "支付方式不正确。";
    echo $error;
    exit();
}

//查找通道信息
$channel = $result['mobile_url'];
$sql = "SELECT * FROM `pay_channel` WHERE `id` = '{$channel}' ";
$channel = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
global $config;
$config = array(
    //应用ID,您的APPID。
    'app_id'               => $channel['appid'],

    //商户私钥，您的原始格式RSA私钥
    'merchant_private_key' => $channel['private_key'],

    //异步通知地址
    'notify_url'           => $channel['notify_url'],

    //同步跳转
    'return_url'           => $channel['return_url'],

    //编码格式
    'charset'              => $channel['charset'],

    //签名方式
    'sign_type'            => $channel['sign_type'],

    //支付宝网关
    'gatewayUrl'           => $channel['gatewayUrl'],

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key'    => $channel['public_key'],

);
$config = $channel;
//var_dump($config);
//return;
switch($type){
    case 'alipay2':
    case 'alipay2_url':
    case 'alipay2qr':
        $type_topay = 'alipay';
        break;
    case 'wechat2':
    case 'wechat2_url':
    case 'wechat2qr':
        $type_topay = 'wechat';
        break;
    case 'qqpay2':
    case 'qqpay2_url':
    case 'qqpay2qr':
        $type_topay = 'qq';
        break;
    case 'yunshanpay':
        $type_topay = "yunshanpay";
        break;
    case 'yinshengpay':
        $type_topay = "yinshengpay";
        break;
    default:
        echo "Error：601/支付类型错误，请联系管理员。";
        exit();
        break;
}
$userid = $result['pid'];
$money = $result['money'];
$return_url = $result['return_url'];
$notify_url = $result['notify_url'];
$appid = $channel['appid'];


if(false){
    //确定验证码
    //初始化验证码
    $code = 0;
    $money2 = $DB2->fetchRowMany("SELECT `money2` FROM `pay_order` WHERE `status` = 0 AND `appid` = :appid ;",["appid" => $appid]);
    if($money2){
        $money2_array = [];
        foreach($money2 as $value){
            $money2_array[] = $value['money2'];
        }

        $money2 = $code;
        while(true){
            if(in_array($money2,$money2_array)){
                $code++;
                $money2 = $code;
                if($code == 999999){
                    echo "收款码验证码已用完，请联系客服处理，谢谢。";
                    exit();
                }
            }else{
                break;
            }
        }
    }else{
        $money2 = $code;
    }
}
$money2 = 0;


require_once __DIR__.DIRECTORY_SEPARATOR.'person_api2/libs/function.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'person_api2/libs/person_api.php';

$i = 0;
$j = 2;//循环次数

while($i < $j){
    //$mobile_url = \epay\person_api::get_mobile_url($trade_no);
    $mobile_url = $channel['id'];
    //$data = \epay\person_api::get_qrcode($mobile_url,$money,$trade_no,$type_topay);
    //$data = \epay\person_api::get_qrcode2($money,$trade_no,$type_topay);

    //使用金额验证
    //$data = \epay\person_api::get_qrcode3($appid,$mobile_url,$config['public_key'],$money2,$trade_no,$type_topay);
    //使用备注验证
    //$data = \epay\person_api::get_qrcode4($appid,$mobile_url,$config['public_key'],$money,$money2,$trade_no,$type_topay,"code");
	//针对很多人会多填写空格处理
	$config['public_key']=trim($config['public_key']);
	//转账码,换成新模式
	$payurl ="alipays://platformapi/startapp?appId=20000116&actionType=toAccount&goBack=NO&amount={$money}&userId=2088{$config['public_key']}&memo=$$"."{$trade_no}";
	//$payurl = "alipays://platformapi/startapp?appId=09999988&actionType=toAccount&&goBack=YES&&userId=2088{$config['public_key']}&&amount={$money}&&memo={$trade_no}";
	//加入对吱口令的判断
	if(substr($config['public_key'],0,22)=="https://qr.alipay.com/"){
		$payurl=$config['public_key'];
	}
	//这里加入对银行转账模式的判断,首先要判断16或19位
	if(strlen($config['public_key'])==16 || strlen($config['public_key'])==19){
        //金额递增模式，5分钟内数据（备注：只有相同金额的才会递增
        $sql = "SELECT COUNT(*) as a FROM `pay_order` WHERE `mobile_url` = '".$channel["id"]."' AND `money`=".$money." AND addtime>=DATE_SUB(NOW(),INTERVAL 10 MINUTE)";
        //先取十分钟的记录
        $result = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
        $channel_back = false;//用于没有可用的money2
        if($result["a"]==0){ //no
            $money2 = 0.01;
            $channel_back = true;
        }
        else{
            //存在的原有记录条数，循环取出来
            for ($i=1;$i<100;$i++){
                if($i==7){
                    continue;
                }
                $k = $i/100;
                $sql = "SELECT COUNT(*) as a FROM `pay_order` WHERE `mobile_url` = '".$channel["id"]."' AND `money`=".$money." AND `money2`=".$k." AND addtime>=DATE_SUB(NOW(),INTERVAL 10 MINUTE)";
                $result1 = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
                if($result1["a"]==0){//have
                    $money2 = $k;
                    $channel_back = true;//status==1
                    break;
                }
                else{
                    $sql = "SELECT COUNT(*) as a FROM `pay_order` WHERE `mobile_url` = '".$channel["id"]."' AND `money`=".$money." AND `money2`=".$k." AND `status`=0 AND addtime>=DATE_SUB(NOW(),INTERVAL 10 MINUTE)";
                    $result2 = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
                    if($result2["a"]==0){
                        $money2 = $k;
                        $channel_back = true;
                        break;
                    }
                }
            }
        }
        if($channel_back==false){
            echo  json_encode(array("code"=>0,"msg"=>"无可用小数！"),JSON_UNESCAPED_UNICODE);
            exit();
        }
        $right_money=$money-$money2;
        //根据银行卡号取银行的缩写
		$cardinfo=getCardInfo($config['public_key']);
		//这里再加多一个判断，描述是否包含,
		$re=explode(",",$channel['body']);
		if(sizeof($re)==1){
			$payurl="alipays://platformapi/startapp?appId=09999988&actionType=toCard&sourceId=bill&cardNo=".$config['public_key']."&bankAccount=".$channel['body']."&bankMark=".$cardinfo["bank"]."&money=".$right_money."&amount=".$right_money;
		}else{
			$payurl="alipays://platformapi/startapp?appId=09999988&actionType=toCard&sourceId=bill&cardNo=".substr($config['public_key'],0,6)."**********".substr($config['public_key'],-4,4)."&bankAccount=".$re[0]."&bankMark=".$cardinfo[bank]."&money=".$right_money."&amount=".$right_money."&cardIndex=".$re[1]."&cardNoHidden=true&cardChannel=HISTORY_CARD&orderSource=from";
		}
	}
    //加入对吱口令,扫码点单，中国银行固码，原始转账,数字红包的判断
    if(substr($channel['body'],0,10)=="https://qr" || substr($channel['public_key'],0,10)=="https://qr" || substr($channel['body'],0,4)=="wxp:" || (strlen($config['public_key'])==12 && empty($channel['body'])) || substr($channel['body'],0,1)=="&"){
        //金额递减模式，5分钟内数据（备注：先尝试不减，只有没有可用通道才会递减
        $money2 = 0.00;
        $sql = "SELECT COUNT(*) as a FROM `pay_order` WHERE `mobile_url` = '".$channel["id"]."' AND `money`=".$money." AND `money2`=".$money2."  AND `status`=0 AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
        //先取十分钟的记录
        $result = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
        $channel_back = false;//用于没有可用的money2
        if($result["a"]==1){ //no
            $channel_back = true;
        }else{
            //存在的原有记录条数，循环取出来
            for ($i=1;$i<100;$i++){
                $k = $i/100;
                $sql = "SELECT COUNT(*) as a FROM `pay_order` WHERE `mobile_url` = '".$channel["id"]."' AND `money`=".$money." AND `money2`=".$k." AND `status`=0  AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
                $result1 = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
                if($money-$money2<=0){
                    //优惠减到负数就直接报错退出
                    echo  json_encode(array("code"=>0,"msg"=>"无可用小数！"),JSON_UNESCAPED_UNICODE);
                    exit();
                }
                if($result1["a"]==0){//have
                    $money2 = $k;
                    $channel_back = true;//status==1
                    break;
                }
                continue;
            }
        }
        if($channel_back==false){ //百个小数都尝效无效直接报错
            echo  json_encode(array("code"=>0,"msg"=>"无可用小数！"),JSON_UNESCAPED_UNICODE);
            exit();
        }
        $right_money=$money-$money2;
        $payurl=empty($channel['body'])?$payurl:$channel['body']; //如果$channel['body']为空，就调原来的。
    }
    //这里加入对丰收家模式的判断
    if($channel['body']=="fsj"){
        //金额递增模式，5分钟内数据（备注：只有相同金额的才会递增
        $sql = "SELECT COUNT(*) as a FROM `pay_order` WHERE `mobile_url` = '".$channel[id]."' AND `money`=".$money." AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
        //先取十分钟的记录
        $result = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
        $channel_back = false;//用于没有可用的money2
        if($result["a"]==0){ //no
            $money2 = 0.01;
            $channel_back = true;
        }
        else{
            //存在的原有记录条数，循环取出来
            for ($i=1;$i<100;$i++){
                $k = $i/100;
                $sql = "SELECT COUNT(*) as a FROM `pay_order` WHERE `mobile_url` = '".$channel[id]."' AND `money`=".$money." AND `money2`=".$k." AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
                $result1 = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
                if($result1["a"]==0){//have
                    $money2 = $k;
                    $channel_back = true;//status==1
                    break;
                }
                else{
                    $sql = "SELECT COUNT(*) as a FROM `pay_order` WHERE `mobile_url` = '".$channel[id]."' AND `money`=".$money." AND `money2`=".$k." AND `status`=0 AND addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
                    $result2 = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
                    if($result2["a"]==0){
                        $money2 = $k;
                        $channel_back = true;
                        break;
                    }
                }
            }
        }
        if($channel_back==false){
            echo  json_encode(array("code"=>0,"msg"=>"无可用小数！"),JSON_UNESCAPED_UNICODE);
            exit();
        }
        $right_money=$money-$money2;
        $payurl=$channel['body'];
    }
    //加入对拚多多的处理
    if(substr($channel['private_key'],0,12)=="access_token") {
        $payurl="pdd";
    }
    $data = \epay\person_api::get_qrcode5($appid,$mobile_url,$payurl,$money,$money2,$trade_no,$type_topay,"zzm");
    if($data['code'] == 1){
        break;
        $i++;
    }
}
if($data['code'] == 0){
    echo $data['msg'];
    exit();
}
$directUrl = "{$website_urls}api/person_api2/pay.php?trade_no={$trade_no}";
//var_dump($data);
//return;
//开启支付
header("Location: {$directUrl}");
