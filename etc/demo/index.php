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
<div class="pay">
    <form action="pay.php" method="post" target="_blank">
        支付类型：
        <select name="type">
            <option value="alipay2">支付宝（个人）</option>
            <option value="wechat2">微信（个人）</option>
            <option value="qqpay2">QQ钱包（个人）</option>
            <option value="alipay2qr">支付宝（只提供二维码json数据）</option>
            <option value="wechat2qr">微信（只提供二维码json数据）</option>
            <option value="qqpay2qr">QQ钱包（只提供二维码json数据）</option>
            <option value="aaaabbbb">无此类型(测试签名)</option>
        </select><br />
        商户订单号：<input type="text" name="out_trade_no" value="<?php echo date("YmdHis").rand(0,9).rand(0,9); ?>"><br />
        商品名称：<input type="text" name="name" value="测试支付"><br />
        金额：<input type="text" name="money" value="1.00"><br />
        网站名称：<input type="text" name="sitename" value="新网站"> <br />
        <input type="submit" value="提交">
    </form>
</div>
</body>
</html>