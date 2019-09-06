	<html xmlns="http://www.w3.org/1999/xhtml">
	<body>
	<?PHP
	header("Content-type:text/html;charset=utf-8");
	
	//页面编码要与参数inputCharset一致，否则服务器收到参数值中的汉字为乱码而导致验证签名失败。	
	
	$pidx=$_GET["pid"];
	
	$orderid=$_GET["orderid"];
	$trxamt=$_GET["money"];
	$goodsid=$_GET["name"];
	$returl=$_GET["returl"];
	
	$wangzhi="http://168.tihou.com/demo/demo5/post.php?pid=".$pidx."&orderid=".$orderid."&money=".$trxamt."&name=".$goodsid."&returl=".$returl;
	$wangzhi=urlencode($wangzhi);
	$wangzhi2="http://168.tihou.com/api/qrcode.php?data=".$wangzhi;
	?>
	<img src="<?php echo $wangzhi2; ?>" width="200px" height="200px"><br>
    <h3>请使用微信扫一扫支付</h3>
    </body>
    </html>