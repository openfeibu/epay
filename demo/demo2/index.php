<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<head>
    <title>测试支付</title>
    <style>
        body{
            margin: 20px;
        }
        .pay{
            font-size: 20px;
            line-height: 30px;
        }
    </style>
</head>
<body>
<?php
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";
$out_trade_no = date("YmdHis").rand(0,9).rand(0,9);
$money=mt_rand(1,100)/100; //随机金钱
    print <<< EOF
<div class="pay">
    <form action="pay.php" method="post">
        商户号：<input type="text" name="pid" value=""><br />
        支付类型：
        <select name="type">
            <option value="alipay2">支付宝（个人）</option>
            <option value="alipayh5">支付宝（H5）</option>
            <option value="wechat2">微信（个人）</option>
            <option value="yunshanpay">云闪付（个人）</option>
            <option value="bank">网银（pc网银快捷）</option>
            <option value="yinshengpay">银盛通（个人）</option>
            <option value="qqpay2">QQ钱包（个人）</option>
            <option value="alipay2qr">支付宝（只提供二维码json数据）</option>
            <option value="wechat2qr">微信（只提供二维码json数据）</option>
            <option value="qqpay2qr">QQ钱包（只提供二维码json数据）</option>
            <option value="alipay">支付宝（企业）</option>
            <option value="kuaikuai">快快（微信H5）</option>
            <option value="ddqr">钉钉（支付宝扫码）</option>
            <option value="aaaabbbb">无此类型(测试签名)</option>
        </select><br />
        商户订单号：<input type="text" name="out_trade_no" value="{$out_trade_no}"><br />
        回调地址：<input type="text" name="notify_url" value="{$website_urls}callback/notify.php"><br />
        同步地址：<input type="text" name="return_url" value="{$website_urls}" cols="50"><br />
        商品名称：<input type="text" name="name" value="测试支付"><br />
        附加数据：<input type="text" name="attach" value="说明"><br />
        金额：<input type="text" name="money" value="$money"><br />
        网站名称：<input type="text" name="sitename" value="新网站"> <br />
        返回格式：<input type="text" name="format" value=""> <br />
        密钥：<input type="text" name="key" value=""><br>
        签名：<input type="text" name="sign" value=""><br>
        签名类型：<input type="text" name="sign_type" value="MD5"> <br />
        <input type="submit" value="提交">
    </form>
</div>
EOF;

?>
</body>
</html>