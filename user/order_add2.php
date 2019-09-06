<!DOCTYPE html>
<html>
<head>
    <title>跳转中</title>
    <meta charset="UTF-8">
    <meta content="text/html">
</head>
<body>
<?php
/**
 * 充值订单
 **/
require '../includes/common.php';
//require_once '../includes/api/debug.php';
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '充值订单';
// include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";

//var_dump($_REQUEST);
//var_dump($_SESSION);
$trade_no = '';//支付宝交易号,创建充值交易时此处为空
$userid = $_SESSION['userid'];
$date = date("Y-m-d H:i:s");


if(isset($_REQUEST['out_trade_no']) && isset($_REQUEST['total_amount']) && isset($_REQUEST['type'])){
    $out_trade_no = daddslashes($_REQUEST['out_trade_no']);
    $money = daddslashes($_REQUEST['total_amount']);
    $type = daddslashes($_REQUEST['type']);
}else{
    echo "参数不完整。";
    exit();
}
//判断参数是否正确


//$sql = "insert into `pay_recharge_record`(`trade_no`,`out_trade_no`,`notify_url`,`return_url`,`type`,`pid`,`addtime`,`name`,`money`,`status`)
// values('{$trade_no}','{$no}','{$config['notify_url']}','{$config['return_url']}','alipay','{$p_id}','{$date}','支付宝扫码充值','{$money}','0')";
//$sql = "INSERT INTO `pay_recharge_record` (`id`, `trade_no`, `out_trade_no`, `notify_url`, `return_url`, `type`, `pid`, `addtime`, `update`, `name`, `money`, `status`)
// VALUES (NULL, '{$trade_no}', '{$no}', 'notify_url', 'return_url', 'alipay', '{$p_id}', '2018-07-02 00:00:00', CURRENT_TIMESTAMP, '支付宝网上支付', '{$money}', '0', '0', '0', '0', '0', '0', '0', '0.00);";
switch($type){
    case 'alipay':
    case 'alipay2':
        $name = "支付宝在线支付";
        break;
    case 'wechat':
    case 'wechat2':
        $name = "微信扫码支付";
        break;
    default:
        $name = "";
        break;
}
$agentuuid =$_SESSION['uuid'];//代理商uuid
//创建充值订单
$sql = "INSERT INTO `pay_recharge_record`(`id`,`trade_no`,`out_trade_no`,`agentuuid`,`type`,`pid`,`addtime`,`name`,`money`) VALUES (NULL,'{$trade_no}','{$out_trade_no}','{$agentuuid}','{$type}','{$userid}','{$date}','{$name}','{$money}')";
//var_dump($sql);
$result = $DB->query($sql);
if(!$result){
    echo "创建充值订单失败，请重试。";
    exit();
}
$agentuuid = isset($_SESSION["agentuuid"])?$_SESSION["agentuuid"]:0;

switch($type){
    case 'alipay':
        require_once __DIR__.'/../includes/alipay2/config_1.php';
        //发送订单到支付宝网关
        print <<< EOF
<form action="../includes/alipay2/pagepay/pagepay.php" method="post" target="_self" style="display: block" id="myForm2">
    <table class="p_ta">
        <tr class="p_tr1">
            <td>充值金额：</td>
            <td><input type="text" name="total_amount" id="total_amount" value="{$money}" readonly></td>
        </tr>
        <tr class="p_tr3">
            <td></td>
            <td>
                <input type="hidden" name="out_trade_no" id="out_trade_no" value="{$out_trade_no}">
                <input type="hidden" name="subject" value="在线充值">
                <input type="hidden" name="return_url" value="{$website_urls}includes/alipay2/return_url2.php">
                <input type="hidden" name="notify_url" value="{$website_urls}includes/alipay2/notify_url2.php">
                <input type="hidden" name="body" value="在线充值">
                <input type="hidden" name="agentuuid" value="{$agentuuid}">
                
                <input type="hidden" name="type" id="type" value="alipay">
                <input class="p_d4" type="submit" value="支付宝支付">
            </td>
        </tr>
    </table>
</form>
<!--<script type="text/javascript">-->
<!--window.onload = function() {-->

<!--}-->
<!--</script>-->

EOF;
        break;
    case 'wechat':
        require_once __DIR__."/../person_api/function.php";
        $url = $website_urls."includes/wechatpay/wechatpay.php";
        print <<< EOF
        <script src="../assets/js/jquery.min.js"></script>
EOF;
        $response = \epay\curl_request($url,$_REQUEST);
        echo $response;
        break;
    case 'qqpay':
        break;
    case 'alipay2':
    case 'wechat2':
        require_once __DIR__.'/../includes/epay/config.php';
        require_once __DIR__.DIRECTORY_SEPARATOR."../includes/epay/libs/epay.php";
        require_once __DIR__.DIRECTORY_SEPARATOR."../includes/epay/libs/function.php";
        //发送订单到支付网关
        //构建提交数据
        //var_dump($_REQUEST);
        $aop = new \epay\epay2($config);
        $aop->out_trade_no = $out_trade_no;
        $aop->type = $type;
        $aop->sign_type = "MD5";

        $aop->return_url = "{$website_urls}includes/epay/return_url.php";
        $aop->notify_url = "{$website_urls}includes/epay/notify_url.php";

        $aop->name = $name;
        $aop->sitename = $website_name;
        $aop->money = $money;

//生成签名
        $response = $aop->submit();
        $url = $response['url'];
        $data = $response['data'];

        print <<< EOF
        跳转中，请稍候......
        <script>
        window.onload = function (ev) {
            document.getElementById('auto').submit();
        }
    </script>
<form action="{$url}" method="post" id="auto" style="display: none;" target="_self">
		商户号：<input type="hidden" name="pid" value="{$data['pid']}"><br />
		支付类型：<input  type="hidden" name="type" value="{$data['type']}"><br />
		商户订单号：<input type="hidden" name="out_trade_no" value="{$data['out_trade_no']}"><br />
		异步地址：<input type="hidden" name="notify_url" value="{$data['notify_url']}"><br />
		同步地址：<input type="hidden" name="return_url" value="{$data['return_url']}"><br />
		商品名称：<input type="hidden" name="name" value="{$data['name']}"><br />
		金额：<input type="hidden" name="money" value="{$data['money']}"><br />
        网站名称：<input type="hidden" name="sitename" value="{$data['sitename']}"> <br />
		签名：<input type="hidden" name="sign" value="{$data['sign']}"><br />
        签名类型：<input type="hidden" name="sign_type" value="MD5"><br />
		<input type="submit" value="提交">
	</form>
EOF;
        break;
    case 'qqpay2':
        break;
    default:
        echo "通道关闭，请联系管理员。";
        exit();
        break;
}
?>

<!--<script>-->
<!--    window.onload= function(){-->
<!--        document.getElementById('myForm').submit();-->
<!--    }-->
<!--    // window.onload = function () {-->
<!--    //     document.myF-->
<!--    // }-->
<!--</script>-->
</body>
</html>
