<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<head>
    <title>测试支付</title>
</head>
<body>
<?php
if(isset($_REQUEST['type']) && isset($_REQUEST['out_trade_no']) && isset($_REQUEST['name']) && isset($_REQUEST['money'])){
    $type = $_REQUEST['type'];
    $out_trade_no = $_REQUEST['out_trade_no'];
    $name = $_REQUEST['name'];
    $money = $_REQUEST['money'];

    //可选参数
    if(isset($_REQUEST['sitename'])){
        $sitename = $_REQUEST['sitename'];
    }else{
        $sitename = '';
    }
}else{
    echo "参数不完整。";
    exit();
}
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";
require_once __DIR__.DIRECTORY_SEPARATOR."libs/epay.php";
require_once __DIR__.DIRECTORY_SEPARATOR."libs/function.php";


//构建提交数据
$aop = new \epay2\epay2($config);
$aop->out_trade_no = $out_trade_no;
$aop->type = $type;
$aop->sign_type = "MD5";
$aop->return_url = "http://你的域名/demo/return_url.php";
$aop->notify_url = "http://你的域名/demo/notify_url.php";

$aop->return_url = "https://dev.yykayou.com:444/demo/demo/return_url.php";
$aop->notify_url = "https://dev.yykayou.com:444/demo/demo/notify_url.php";

$aop->name = $name;
$aop->sitename = $sitename;
$aop->money = $money;

//生成签名
$response = $aop->submit();
//var_dump($response);
$url = $response['url'];
$data = $response['data'];

print <<< EOF
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

?>

</body>
</html>