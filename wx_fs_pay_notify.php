<?php
exit();
/**
 * 通用通知接口demo
 * ====================================================
 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
 * 商户接收回调信息后，根据需要设定相应的处理流程。
 *
 * 这里举例使用log文件形式记录回调信息。
 */
//include_once("./log_.php");
//include_once("../WxPayPubHelper/WxPayPubHelper_forjsapi_wgh_7d0b8e0421ae.php");

require_once('./includes/common.php');
$DB->query("update `ims_ewei_shop_article` set `resp_desc` ='ccc' where `id`=21");

require_once('./includes/wxpay/WxPayPubHelper_forjsapi_wgh_7d0b8e0421ae.php');


//使用通用通知接口
$notify = new Notify_pub();

//存储微信的回调
$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
if($xml == '') return '';
libxml_disable_entity_loader(true);
$arr = json_decode(json_encode(simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA)),true);

$DB->query("update `ims_ewei_shop_article` set `resp_desc` ='".$xml."' where `id`=21");
// $arr = $this->XmlToArr($xml);
// $text='<?php $rows='.var_export($arr,true).';';

//验证签名，并回应微信。
//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
//尽可能提高通知的成功率，但微信不保证通知最终能成功。
$tmpreturn = "";
//if($notify->checkSign() == FALSE){
//	$notify->setReturnParameter("return_code","FAIL");//返回状态码
//	$notify->setReturnParameter("return_msg","签名失败");//返回信息
//	$tmpreturn="return_code FAIL";
//}else{
$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
$tmpreturn = "return_code SUCCESS";
//}
//$notify->setReturnParameter("return_code","SUCCESS");
$returnXml = $notify->returnXml();
echo $returnXml;
// file_put_contents('./log.txt', $GLOBALS['HTTP_RAW_POST_DATA']);
//==商户根据实际情况设置相应的处理流程，此处仅作举例=======


//if($notify->checkSign() == TRUE)
//if(1==1)
if($tmpreturn == "return_code SUCCESS"){

    //商户自行增加处理流程,
    //例如：更新订单状态


    $out_trade_no = $arr["out_trade_no"];
    //QQ钱包订单号
    $transaction_id = $arr["transaction_id"];

    $money = $arr["total_fee"] / 100;

    // $date=date('Y-m-d H:i:s');
    //------------------------------
    //处理业务开始
    //------------------------------
    $srow = $DB->query("SELECT * FROM pay_order WHERE out_trade_no='{$out_trade_no}' limit 1")->fetch();

    $key = $DB->query("SELECT * FROM pay_user WHERE id='{$srow['pid']}' limit 1")->fetch();
    if($srow['status'] == 0){
        $DB->query("update `pay_order` set `status` ='1',`endtime` ='$date' where `out_trade_no`='$out_trade_no' and `trade_no` =(SELECT trade_no FROM (SELECT trade_no FROM  pay_order WHERE out_trade_no='$out_trade_no' order by addtime DESC LIMIT 1) pay_order)");
    }

    $url = creat_callback($srow);
    if(!$DB->query("update `ims_ewei_shop_article` set  `resp_desc`='".$url['notify']."' where `id`=19")) exit('创建订单失败，请返回重试！');
    curl_get($url['notify']);

    /*
    $sign=md5($srow['pid'].$key['key']);

    $_arr=array(
        'money' =>$money,
        'name' =>$srow['name'],
        'out_trade_no' =>$out_trade_no,
        'pid' =>$srow['pid'],
        'trade_no' =>$transaction_id,
        'trade_status' =>$arr['result_code'],
        'type' =>$srow['type'],
        'sign' =>$sign,
        'sign_type' =>'MD5',
    );

    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $srow['notify_url']);
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $_arr);
    $return = curl_exec ( $ch );
    curl_close ( $ch );

    file_put_contents('./logs.txt', var_export($_arr,true));
*/
    // $text='<?php $rows='.var_export($_arr,true).'out_trade_no='.$out_trade_no.';';

    // if($srow['status']==0){
    // $DB->query("update `pay_order` set `status` ='1',`endtime` ='$date' where `out_trade_no`='$out_trade_no'");
    // $addmoney=round($srow['money']*$conf['money_rate']/100,2);
    // $DB->query("update pay_user set money=money+{$addmoney} where id='{$srow['pid']}'");

    // $url=creat_callback($srow);
    // if(!$DB->query("update `ims_ewei_shop_article` set  `resp_desc`='".$url['notify']."' where `id`=17"))exit('创建订单失败，请返回重试！');
    // curl_get($url['notify']);
    // //proxy_get($url['notify']);
    //    $urlbak = "http://47.94.11.190:8080/insertdbfrompid.php?pid=".$srow['pid']."&trade_no=".$transaction_id."&out_trade_no=".$out_trade_no."&money=".$srow['money']."&from=%E5%BE%AE%E4%BF%A1%E6%94%AF%E4%BB%98&username=WXuser";
    //    //curl_get($urlbak);

    // if(!$DB->query("update `ims_ewei_shop_article` set  `resp_desc`='".$urlbak."' where `id`=18"))exit('创建订单失败，请返回重试！');

    // }
    //例如：数据库操作
    //例如：推送支付完成信息.3


}

function XmlToArr($xml){
    if($xml == '') return '';
    libxml_disable_entity_loader(true);
    $arr = json_decode(json_encode(simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA)),true);
    return $arr;
}

function send_notify($url,$content){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$content);
    $return = curl_exec($ch);
    curl_close($ch);
}


function https_post($url){
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($curl,CURLOPT_POST,0);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    $result = curl_exec($curl);
    if(curl_errno($curl)){
        return 'Errno'.curl_error($curl);
    }
    curl_close($curl);
    return $result;
}

?>

