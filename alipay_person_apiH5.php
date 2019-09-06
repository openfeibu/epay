<?php
exit();

require './includes/common.php';

@header('Content-Type: text/html; charset=UTF-8');

$trade_no = daddslashes($_GET['out_trade_no']);
$sitename = base64_decode(daddslashes($_GET['sitename']));
$row = $DB->query("SELECT * FROM pay_order WHERE out_trade_no='{$trade_no}' limit 1")->fetch();
//echo "SELECT * FROM pay_order WHERE out_trade_no='{$trade_no}' limit 1";

if(!$row) exit('该订单号不存在，请返回来源地重新发起请求！');

$row2 = $DB->query("SELECT * FROM pay_person_qr WHERE nowho=1 and money=".$row['money']." order by id limit 1")->fetch();

$row3 = $DB->query("SELECT * FROM pay_person_qr WHERE status=0 and money=".$row['money']." order by id")->fetchAll();
$nowid = 0;
$firstrecid = 0;
$readynextok = 0;
$isyouid = 0;
$meis = 0;
foreach($row3 as $res){
    if($nowid == 0){
        $firstrecid = $res['id'];
    }
    if($readynextok == 1){
        $isyouid = $res['id'];
        $readynextok = 0;
    }
    $nowid = $nowid + 1;
    if($res['id'] == $row2['id']){
        $readynextok = 1;
        $meis = $nowid;
    }
}
if($meis == $nowid){
    //我是最后一个，下一个将跳到第一个
    $isyouid = $firstrecid;
}

$DB->query("update `pay_person_qr` set `status`=1,`nowho`=0 where `id`=".$row2['id']);
$DB->query("update `pay_person_qr` set `nowho`=1 where `id`=".$isyouid);

if(isset($_GET['type'])) $DB->query("update `pay_order` set `buyer`='".$row2['names']."',`type` ='personpay',`addtime` ='$date' where `out_trade_no`='$trade_no'");


?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta http-equiv="Content-Language" content="zh-cn">
    <meta name="renderer" content="webkit">
    <title>支付宝安全支付 - <?php echo $sitename ?></title>
    <link href="http://71cn.com/easypay/assets/css/alipay_pay.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="body">
    <h1 class="mod-title">
        <span class="ico-wechat"></span><span class="text">支付宝支付</span>
    </h1>
    <div class="mod-ct">
        <div class="order">
        </div>
        <div class="amount">￥<?php echo $row['money'] ?></div>
        <div class="qr-image" id="qrcode">
        </div>

        <div class="detail" id="orderDetail">
            <dl class="detail-ct" style="display: none;">
                <dt>商家</dt>
                <dd id="storeName"><?php echo $sitename ?></dd>
                <dt>购买物品</dt>
                <dd id="productName"><?php echo $row['name'] ?></dd>
                <dt>商户订单号</dt>
                <dd id="billId"><?php echo $row['out_trade_no'] ?></dd>
                <dt>创建时间</dt>
                <dd id="createTime"><?php echo $row['addtime'] ?></dd>
            </dl>
            <a href="javascript:void(0)" class="arrow"><i class="ico-arrow"></i></a>
        </div>
        <div class="tip">
            <span class="dec dec-left"></span>
            <span class="dec dec-right"></span>
            <div class="ico-scan"></div>
            <div class="tip-text">
                <p>请使用手机支付宝扫一扫</p>
                <p>扫描二维码完成支付</p>
            </div>
        </div>
        <div class="tip-text">
        </div>
    </div>
    <div class="foot">
        <div class="inner">

        </div>
    </div>
</div>
<script src="http://71cn.com/easypay/assets/js/qrcode.min.js"></script>
<script src="http://71cn.com/easypay/assets/js/qcloud_util.js"></script>
<script>
    location.href = "<?php echo $row2['QR']?>";
</script>
</body>
</html>