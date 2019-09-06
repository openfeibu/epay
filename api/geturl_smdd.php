<?php
if(isset($_GET['trade_no'])){
	header("Content-type: text/html; charset=utf-8");
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
	//开始处理业务逻辑，这里加入十分钟限制
	$order = \epay\order::find($_REQUEST['trade_no']);
	$order1 = \epay\order::findtenminute1($_REQUEST['trade_no']);
?>
<html lang="en">
<head></head>
<body>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
<title>支付宝支付</title>
<script src="https://cdn.bootcss.com/clipboard.js/2.0.4/clipboard.min.js"></script>
<script type="text/javascript">
    var clipboard = new ClipboardJS('.cpbtn');
    clipboard.on('success', function(e) {
        alert("复制成功！");
    });
</script>
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
					<span style="font-size: 2.8rem;color: red;">￥<?php echo $order['money'] - $order['money2']; ?></span>
                    <button id='cpbtn' class='cpbtn btn btn-primary' style='width:auto;margin-top:-10px;margin-left:10px;' data-clipboard-text='<?php echo $order['money'] - $order['money2']; ?>';>一键复制金额</button>
					<br>
					<span style="color:green">点击“<span style="font-size: 2.0rem;color:red;">直接买单</span>”输入<span style="font-size: 2.0rem;color:red;">红色金额</span>进行支付</span>
				</div>
			</div>
			<br>
            <?php
				if ($order1 && $order1['status'] == 0) {
					$note2 = json_decode($order1['note2'], true);}
			?>
            <img width="90%" height="50%" src="http://pay.weimifu.net/ke/lib/zhiyin.jpg"><br><br>
			<div style="display:none;"><span onselectstart="return false" style="color:red">1.充值金额满额到账，请勿修改金额，否则无法享受随机立减并不能<span
					style="font-size: 1.4rem;">充值到账！</span></span>
			<br>
			<span onselectstart="return false" style="color:red">2.付款过程中遇到<span style="font-size: 1.4rem;">警示弹窗</span>，请点击继续付款。</span>
			<br>
			<span onselectstart="return false" style="color:red">3.请不要<span style="font-size: 1.4rem;">保存图片</span>，重复付款。</span>
			<br>
			<span onselectstart="return false" style="color:red">4.订单提示<span style="font-size: 1.4rem;">超时或不存在</span>，请重新创建订单。</span>
            <br>
            <span onselectstart="return false" style="color:red">5.订单创建了，<span style="font-size: 1.4rem;">请尽快付款，超过三分钟将不上分!</span></span></div>
			<span><a href="<?php echo $note2['payurl']; ?>"><input type="text" onclick="open()" value="我已看过，立即付款" style="width:80%;height:7%;text-align:center;font-size:1.5em;background:#03a7f5;color:#fff;border:0px;border-radius:10px;"></a></span>
		</div>
	</div>
</div>
<?php
	if ($order1 && $order1['status'] == 0) {
		//$note2 = json_decode($order1['note2'], true);
		echo "<script>setTimeout(function(){ location.href='" . $note2['payurl'] . "'; }, 60000);
		</script>";
		echo "<script>function open(){
			location.href='" . $note2['payurl'] . "';
		}</script>";
		exit();
	}
	echo "<script>alert('订单不存在，或已超时。');</script>";
	exit();
	}
?>
</body></html>
