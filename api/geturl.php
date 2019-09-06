<?php
	//不缓存
	header('X-Accel-Buffering: no');
	header('Cache-Control:no-cache,must-revalidate');
	header('Pragma:no-cache');
	header("Expires:0");
	if(isset($_GET['trade_no'])){
	header("Content-type: text/html; charset=utf-8");
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
	//开始处理业务逻辑，这里加入十分钟限制
	$order = \epay\order::find($_REQUEST['trade_no']);
	$order1 = \epay\order::findtenminute($_REQUEST['trade_no']);
	$note2 = json_decode($order['note2'], true);
?>
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
    <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection">
    <title>支付宝支付</title>
    <div style="width: 100%; text-align: center;font-family:微软雅黑;display: block;">
        <div id="panelWrap" class="panel-wrap">
            <!-- CUSTOM LOGO -->
            <div class="panel-heading">
                <div class="row">

                </div>
            </div>
            <!-- PANEL TlogoEMPLATE START -->
            <div class="panel panel-easypay">
                <!-- PANEL HEADER -->
                <div class="panel-heading">
                    <h3>
                        <small>订单号：<?php echo $_REQUEST['trade_no']; ?></small>
                    </h3>

                    <div class="money">
                        实付：
                        <span
                                style="font-size: 1.4rem;color: #f50;"><?php echo $order['money'] - $order['money2']; ?></span>
                        <span style="font-size: 2.0rem;color: #f50;"><s><?php echo $order['money']; ?></s></span>
                        <span>，立减<?php echo $order['money2']; ?></span>
                        <div align="center"><img id="show_qrcode" src="qrcode.php?data=<?php echo urlencode($note2[payurl]); ?>" width="300"
                                                 height="210" style="display: block; width: 310px; height: 270px;"></div>
                        <span style="font-size: 1.4rem;color: #f50;">如果无法唤起，请保存图片再进行付款！</span>

                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
		if ($order1 && $order1['status'] == 0) {
			$note2 = json_decode($order1['note2'], true);
			header('location:'.$note2[payurl]);
			exit();
		}
		echo "<script>alert('订单不存在，或已超时。');</script>";
		exit();
		}
	?>
    </body></html>
