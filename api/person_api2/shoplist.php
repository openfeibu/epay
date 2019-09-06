<?php
	header("Content-Type: text/html;charset=utf8");
	//加入全局防注入
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../../includes/api/init.php";
	//require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/debug.php";
	$order=$_REQUEST["orderid"];
	$type=$_REQUEST["type"];
	if ($type == '1') {
		$type2='微信支付';
	} elseif ($type == '2') {
		$type2='支付宝支付';
	} elseif ($type == '3') {
		$type2="QQ钱包支付";
	}
	else if($type == '4'){
		$type2= "云闪付";
	}
	$user=$_REQUEST["userid"];
	$money=$_REQUEST["money"];

	//连接海购数据库
	$conn = new mysqli("localhost","56higo","t2v*mNFI7&0cxrHy","56higo");
	if ($conn->connect_error) {
		die("connection error!: " . $conn->connect_error);
	}
	$conn ->query("SET NAMES utf8");//防止乱码
	//随机用户
	$randid=rand(1000,22000);
	$shijc=time();
	$yonghu=rand(80,200);
	//设定商品价格范围
	$gaojia=$money+20;
	$dijia=$money-10;
	//生成海购订单
   $sql="SELECT order_sn FROM dsc_order_info WHERE order_sn='$order'";
   $orderstatus=mysqli_query($conn,$sql);
$orderrow = mysqli_fetch_array($orderstatus);
   if(isset($orderrow["order_sn"])){
     echo "订单已存在";
   }else {
       $sql = "INSERT INTO dsc_order_info (order_sn, pay_name, user_id,money_paid,order_amount,consignee,province,city,district,address,mobile,add_time,froms) 
            SELECT $order,'$type2', '$yonghu','$money','$money',consignee,province,city,district,address,mobile,'$shijc','hw' 
            FROM dsc_order_info 
            where order_id='$randid';
            ";
       $conn->query($sql);
       //生成商品信息
       $sql = "INSERT INTO dsc_order_goods (order_id, goods_id, goods_name,goods_sn,market_price,goods_price,ru_id,area_id)
            SELECT last_insert_id(),b.goods_id,b.goods_name,b.goods_sn,b.shop_price,$money,b.user_id,
            (SELECT region_id FROM dsc_area_region a WHERE ru_id=b.user_id LIMIT 1)
             FROM dsc_goods b 
            where b.shop_price>'$dijia' and b.shop_price<'$gaojia' order by rand() LIMIT 1;
            ";
       $conn->query($sql);
   }
$conn->close();
?>