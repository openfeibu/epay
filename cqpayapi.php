<?php
exit();
require './includes/common.php';
$out_trade_no = daddslashes($_GET['out_trade_no']);
$sitename = daddslashes($_GET['sitename']);
$row = $DB->query("SELECT * FROM pay_order WHERE out_trade_no='{$out_trade_no}' limit 1")->fetch();
require_once(SYSTEM_ROOT."cspay/epay.config.php");
require_once(SYSTEM_ROOT."cspay/epay_submit.class.php");

/**************************请求参数**************************/


//构造要请求的参数数组，无需改动
$parameter = array(
    "mch_id"       => trim($alipay_config['partner']),
    "service"      => 'XFZF_JD_H5',
    "body"         => $row['name'],
    "sign_type"    => 'MD5',
    "out_trade_no" => $out_trade_no,
    "total_fee"    => $row['money'] * 100,
    "notify_url"   => 'http://'.$_SERVER['HTTP_HOST'].'/easypay/cspay_notify.php',
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestCurl($parameter);
echo $html_text."<br/>";
print_r($parameter);
print_r($html_text);
exit;

//print_r($data);	

//echo "QR is ".$html_text["payinfo"];	
?>


<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Language" content="zh-cn">
    <meta name="renderer" content="webkit">
    <title>微信支付 - <?php echo $sitename ?></title>
    <link href="http://71cn.com/easypay/assets/css/wechat_pay.css?fdsaf=432423" rel="stylesheet" media="screen">
</head>
<body>
<div class="body">
    <h1 class="mod-title">
        <span class="ico-alipay"></span><span class="ico-wechat"></span><span class="text">微信支付/支付宝支付</span>
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
                <p>请使用支付宝或者微信扫一扫</p>
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
    var qrcode = new QRCode("qrcode", {
        text: "<?php echo $html_text["payinfo"]?>",
        width: 230,
        height: 230,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    // 订单详情
    $('#orderDetail .arrow').click(function (event) {
        if ($('#orderDetail').hasClass('detail-open')) {
            $('#orderDetail .detail-ct').slideUp(500, function () {
                $('#orderDetail').removeClass('detail-open');
            });
        } else {
            $('#orderDetail .detail-ct').slideDown(500, function () {
                $('#orderDetail').addClass('detail-open');
            });
        }
    });

    // 检查是否支付完成
    function loadmsg() {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "getshop.php",
            timeout: 10000, //ajax请求超时时间10s
            data: {type: "wxpay", trade_no: "<?php echo $row['trade_no']?>"}, //post数据
            success: function (data, textStatus) {
                //从服务器得到数据，显示数据并继续查询
                if (data.code == 1) {
                    if (confirm("您已支付完成，需要跳转到用户中心吗？")) {
                        window.location.href = data.backurl;
                    } else {
                        // 用户取消
                    }
                } else {
                    setTimeout("loadmsg()", 4000);
                }
            },
            //Ajax请求超时，继续查询
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (textStatus == "timeout") {
                    setTimeout("loadmsg()", 1000);
                } else { //异常
                    alert('创建连接失败！');
                }
            }
        });
    }

    window.onload = loadmsg();
</script>
</body>
</html>