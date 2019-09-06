<?php
header("Content-type: text/html; charset=utf-8");
//exit();
//require_once __DIR__."/../../../includes/api/debug.php";
if(isset($_REQUEST['money']) && isset($_REQUEST['type']) && isset($_REQUEST['userid'])){
    $data['money'] = $_REQUEST['money'];
    //$data['pay'] = $_REQUEST['pay'];
    $data['type'] = $_REQUEST['type'];
    $data['userid'] = $_REQUEST['userid'];
}else{
    header("Location: topay.php");
    exit();
}
if (!session_id()){
    session_start();
}

if(isset($_REQUEST['trade_no']) && $_REQUEST['trade_no'] != ''){
    $data['mark'] = $_REQUEST['trade_no'];
}else{
    $data['mark'] = date("YmdHis").rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
}

$_SESSION['type'] = $data['type'];
require_once __DIR__.'/../config.php';

//ini_set("display_errors", "off");
ini_set("display_errors", "on");
header("Content-type: text/html; charset=utf-8");


//$money=$_GET['money'];
//$mark=$_GET['mark'];
//$type=$_GET['type'];
//$mark=date("Ymd").time();
//echo $mark;
//getpay($money,$mark,$type);
getpay($data['userid'],$data['money'],$data['mark'],$data['type']);


function getpay($userid,$money,$mark,$type){
    $url=REQUEST_URL.'getpay?money='.$money.'&mark='.$mark.'&type='.$type;
    // echo $url;
    // return;
    $data=getHtml($url,'');
    $de_json =json_decode($data);
    $msg=$de_json->msg;
    if($msg=='获取成功'){
        $payurl=$de_json->payurl;
        $mark=$de_json->mark;
        $money=$de_json->money;
        $type=$de_json->type;
        if($type=="wechat"){
            $type='1';
        }else{
            $type='2';
        }
        gotoPay($userid,$money,$payurl,$mark,$type);
    }else{
        echo $msg;
    }
}

function gotoPay($userid,$money,$pay_url,$trade_no,$type){
    echo "<form style='display:none;' id='form1' name='form1' method='post' action='../pay.php'>
			  <input name='userid' type='text' value='{$userid}' />
			  <input name='money' type='text' value='{$money}' />
			  <input name='pay_url' type='text' value='{$pay_url}'/>
			  <input name='trade_no' type='text' value='{$trade_no}'/>
			  <input name='type' type='text' value='{$type}'/>
			</form>
			<script type='text/javascript'>function load_submit(){document.form1.submit()}load_submit();</script>";
}

function getHtml($url,$data=''){
    $ch = curl_init($url) ;
    $header[]= 'Mozilla/5.0 (Linux; U; Android 7.1.2; zh-cn; GiONEE F100 Build/N2G47E) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
    if(!empty($data)){
        curl_setopt($ch, 47, 1);
        curl_setopt($ch, 10015, $data);
    }
    curl_setopt($ch,10023,$header);
    curl_setopt($ch, 64, FALSE); // 对认证证书来源的检查
    curl_setopt($ch, 81, FALSE); // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, 19913, true) ;
    curl_setopt($ch, 19914, true) ;
    curl_setopt($ch, 52,1);
    curl_setopt($ch, 13, 60);

    //$ch = curl_init();
    //curl_setopt($ch,CURLOPT_URL,$url);
    //curl_setopt($ch, CURLOPT_HEADER, 1);
    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    ob_start();
    @$data = curl_exec($ch);
    ob_end_clean();
    curl_close($ch);
    return $data;
}

