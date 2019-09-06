<?php
require_once __DIR__."/../../includes/config.php";

$vars['ssl'] = "http";
if(isset($_REQUEST['WIDout_trade_no']) && isset($_REQUEST['WIDsubject']) && isset($_REQUEST['WIDtotal_amount'])){
    $vars['invoiceid'] = $_REQUEST['WIDout_trade_no'];
    $vars['description'] = $_REQUEST['WIDsubject'];
    $vars['amount'] = $_REQUEST['WIDtotal_amount'];
}else{
    echo "Error";
}
if(isset($_REQUEST['WIDbody'])){
    $vars['WIDbody'] = $_REQUEST['WIDbody'];
}

$vars['notify_url'] = $website_urls."includes/wechatpay/notify.php";
$vars['systemurl'] = $website_urls;
$params = array_merge($vars);
$code = weixinpay_link($vars);
echo $code;

//微信支付配置部分
function weixinpay_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"微信支付 v6.0.5"),
     "APPID" => array("FriendlyName" => "公众账号APPID", "Type" => "text", "Size" => "32","Description" => "这里填开户邮件中的（公众账号APPID或者应用APPID）", ),
     "MCHID" => array("FriendlyName" => "商户号", "Type" => "text", "Size" => "25","Description" => "这里填开户邮件中的商户号", ),
     "KEY" => array("FriendlyName" => "API密钥", "Type" => "text", "Size" => "50", "Description" => "这里请使用商户平台登录账户和密码登录http://pay.weixin.qq.com 平台设置的“API密钥”，为了安全，请设置为32字符串。",),
     "ssl" => array("FriendlyName" => "支持SSL", "Type" => "yesno",  "Description" => "你的网站是否支持SSL,如果工作不正常.请取消勾选", ),
    );
	return $configarray;
}

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
function weixinpay_link($params) {
    //微信支付初始化
    ini_set('date.timezone','Asia/Shanghai');
	//error_reporting(E_ERROR);


    require_once __DIR__."/wechatpay/lib/WxPay.Api.php";
    require_once __DIR__."/wechatpay/WxPay.NativePay.php";

    //require_once 'weixinpay/log.php';  禁用此文件，否则会报错。

	#微信接口配置

	//$weixinpay_config['transport']      = $params['ssl'] ? "https" :"http";
	//$weixinpay_config['APPID']        = $params['APPID'];
    //$weixinpay_config['MCHID']        = $params['MCHID'];
	//$weixinpay_config['KEY']            = $params['KEY'];


	#系统变量
	$invoiceid = $params['invoiceid'];
	$description = $params["description"];
    //$description = "测试支付"; 测试使用
	$amount = $params['amount']; # Format: ##.##
    $amount = $amount*100; # 微信支付使用分作单位
	//$currency = $params['currency']; # Currency Code
	//$companyname = $params['companyname'];
	$systemurl = $params['systemurl'];
	//$currency = $params['currency'];

    //$notify_url = $systemurl."/modules/gateways/weixinpay/weixinpay_notify.php";
    $notify_url = $params['notify_url'];


	//模式一
	/**
	 * 流程：
	 * 1、组装包含支付信息的url，生成二维码
	 * 2、用户扫描二维码，进行支付
	 * 3、确定支付之后，微信服务器会回调预先配置的回调地址，在【微信开放平台-微信支付-支付配置】中进行配置
	 * 4、在接到回调通知之后，用户进行统一下单支付，并返回支付信息以完成支付（见：native_notify.php）
	 * 5、支付完成之后，微信服务器会通知支付成功
	 * 6、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
	 */
	$notify = new NativePay();
	//$url1 = $notify->GetPrePayUrl("123456789");

    //模式二
	/**
	 * 流程：
	 * 1、调用统一下单，取得code_url，生成二维码
	 * 2、用户扫描二维码，进行支付
	 * 3、支付完成之后，微信服务器会通知支付成功
	 * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
	 */

	$input = new WxPayUnifiedOrder();
	$input->SetBody("$description");
	$input->SetAttach("$invoiceid"); //附加数据，可选，原样返回，此次传送账单号，与微信支付回掉接口对账。
	$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
	$input->SetTotal_fee("$amount");
	//$input->SetTime_start(date("YmdHis")); 开始时间，可选
	//$input->SetTime_expire(date("YmdHis", time() + 600)); 结束时间，可选，默认2个小时
	//$input->SetGoods_tag("test"); 商品标记，可选
	$input->SetNotify_url("$notify_url");
	$input->SetTrade_type("NATIVE");
	$input->SetProduct_id("$invoiceid"); //商品编号，此为账单号
	$result = $notify->GetPayUrl($input);
	$url2 = $result["code_url"];
    $url2_code = urlencode($url2);

    $code = "
<script src='jquery.min.js'></script>
<div><div style='width: 210px; margin: 0 auto;'>
<div id=\"weixinpayDiv\" style=\"\"><div style=\"text-align: center;\">欢迎使用收款精灵充值系统</div></div>
    <img alt='模式二扫码支付' src='{$systemurl}includes/wechatpay/wechatpay/qrcode.php?data={$url2_code}' style='width:100%;'/><br />
    <div id=\"weixinpayDiv\" style=\"\"><div style=\"text-align: center;\">请用微信扫一扫支付</div></div>
</div></div>";
	$code_ajax = '<!--微信支付ajax跳转-->
	<script>
    //设置每隔1000毫秒执行一次load() 方法
    setInterval(function(){load()},1500);
    function load(){
        var xmlhttp;
        if (window.XMLHttpRequest){
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{
            // code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                trade_state=xmlhttp.responseText;
                if(trade_state=="SUCCESS"){
                    document.getElementById("weixinpayDiv").innerHTML="支付成功";
                    //document.getElementById("green").style.background="green";
                    //alert(transaction_id);
                    //延迟3000毫秒执行tz() 方法
                    setTimeout(function(){tz()},3000);
                    function tz(){
                        //window.location.href="/clientarea.php";
                        window.location.href="'.$systemurl.'admin/recharge_record.php?'.$invoiceid.'";
                    }
                }else if(trade_state=="REFUND"){
                    document.getElementById("weixinpayDiv").innerHTML="转入退款";
                }else if(trade_state=="NOTPAY"){
                    document.getElementById("weixinpayDiv").innerHTML="请扫码支付";

                }else if(trade_state=="CLOSED"){
                    document.getElementById("weixinpayDiv").innerHTML="已关闭";
                }else if(trade_state=="REVOKED"){
                    document.getElementById("weixinpayDiv").innerHTML="已撤销";
                }else if(trade_state=="USERPAYING"){
                    document.getElementById("weixinpayDiv").innerHTML="用户支付中";
                }else if(trade_state=="PAYERROR"){
                    document.getElementById("weixinpayDiv").innerHTML="支付失败";
                }
            }
        }
        //invoice_status.php 文件返回订单状态，通过订单状态确定支付状态
        xmlhttp.open("get","'.$systemurl.'includes/wechatpay/wechatpay/invoice_status.php?invoiceid='.$invoiceid.'",true);
        //下面这句话必须有
        //把标签/值对添加到要发送的头文件。
        //xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        //xmlhttp.send("out_trade_no=002111");
        xmlhttp.send();
    }
</script>';
	$code = $code.$code_ajax;
    return $code;
}
