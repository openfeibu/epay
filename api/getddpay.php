<?php
	//不缓存
	header('X-Accel-Buffering: no');
	header('Cache-Control:no-cache,must-revalidate');
	header('Pragma:no-cache');
	header("Expires:0");
	/**
	 * 解析url中参数信息，返回参数数组
	 */
	function convertUrlQuery($query)
	{
		$queryParts = explode('&', $query);
		$params = array();
		foreach ($queryParts as $param) {
			$item = explode('=', $param);
			$params[$item[0]] = $item[1];
		}
		return $params;
	}
	if(isset($_GET['trade_no'])) {
		header("Content-type: text/html; charset=utf-8");
		require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
		require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
		//开始处理业务逻辑
		$order = \epay\order::find($_REQUEST['trade_no']);
		if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
			//查找通道描述
			$channel    = \epay\channel::find($order['mobile_url']);
			$note2 = json_decode($order['note2'], true);
			//开始分解数据
			$para=parse_url($note2['payurl']);
			$para_array=convertUrlQuery($para['query']);
			?>


			<!DOCTYPE html>
			<html lang="en" dir="ltr" xmlns:article="http://ogp.me/ns/article#" xmlns:book="http://ogp.me/ns/book#" xmlns:product="http://ogp.me/ns/product#" xmlns:profile="http://ogp.me/ns/profile#" xmlns:video="http://ogp.me/ns/video#" prefix="content: http://purl.org/rss/1.0/modules/content/  dc: http://purl.org/dc/terms/  foaf: http://xmlns.com/foaf/0.1/  og: http://ogp.me/ns#  rdfs: http://www.w3.org/2000/01/rdf-schema#  schema: http://schema.org/  sioc: http://rdfs.org/sioc/ns#  sioct: http://rdfs.org/sioc/types#  skos: http://www.w3.org/2004/02/skos/core#  xsd: http://www.w3.org/2001/XMLSchema# ">
		<head>
			<meta charset="UTF-8">
			<title></title>
			<meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" name="viewport">
			<meta content="yes" name="apple-mobile-web-app-capable">
			<meta content="black" name="apple-mobile-web-app-status-bar-style">
			<meta content="telephone=no" name="format-detection">
            <link href="https://pay.weimifu.net/api/css/hipay.css" rel="stylesheet" type="text/css">
            <link href="https://pay.weimifu.net/api/css/style.css" rel="stylesheet" type="text/css">
			<style type="text/css">
				html,
				body {
					width: 100%;
					height: 100%;
					margin: 0;
					padding: 0;
					overflow: hidden;
				}
			</style>
			<style>
				.demo {
					margin: 1em 0;
					padding: 1em 1em 2em;
					background: #fff;
				}

				.demo h1 {
					padding-left: 8px;
					font-size: 24px;
					line-height: 1.2;
					border-left: 3px solid #108EE9;
				}

				.demo h1,
				.demo p {
					margin: 1em 0;
				}

				.demo .am-button + .am-button,
				.demo .btn + .btn,
				.demo .btn:first-child {
					margin-top: 10px;
				}

				.fn-hide {
					display: none !important;
				}

				input {
					display: block;
					padding: 4px 10px;
					margin: 10px 0;
					line-height: 28px;
					width: 100%;
					box-sizing: border-box;
				}
			</style>
			<script>
                function ready(callback) {
                    if (window.AlipayJSBridge) {
                        callback && callback();
                    } else {
                        document.addEventListener('AlipayJSBridgeReady', callback, false);
                    }
                }
                ready(function(){
                    //副标题文字
                    AlipayJSBridge.call('setTitle', {
                        title: '自助充值'
                    });
                    AlipayJSBridge.call('showLoading',{
                        text: '加载中,请稍候。'
                    });
                    AlipayJSBridge.call('setOptionMenu', {
                        icontype: 'filter',
                    });
                    AlipayJSBridge.call('showOptionMenu');
                    document.addEventListener('optionMenu', function(e) {
                        AlipayJSBridge.call('showPopMenu', {
                            menus: [],
                        }, function(e) {
                            console.log(e);
                        });
                    }, false);

                    pay("<?php echo $note2['ddpay'];?>");

                });

                function pay(orderStr){
                    AlipayJSBridge.call('hideLoading');
                    AlipayJSBridge.call("tradePay", {
                        orderStr:decodeURIComponent(orderStr)
                    }, function(result){
                        if(result.resultCode==9000||result.resultCode=="9000"){
                            document.getElementById("xxx").style.display="none";
                            document.getElementById("yyy").style.display="none";
                            document.getElementById("zzz").innerHTML="支付已完成！";
                            document.getElementById("zzz").style.color="red";
                            document.getElementById("zzz").style.fontSize="2rem";
                            setTimeout(function(){
                                alert("支付已完成");
                                AlipayJSBridge.call('exitApp');
                            }, 500);
                        }
                    });
                }

                function repay(){
                    window.location.reload();
                }
			</script>
		</head>
		<body>
		<div class="aui-free-head 333333" style="background:#fff">
			<div class="aui-flex b-line" style="padding-bottom:0;">
				<div class="aui-user-img">
					<img src="./person_api2/Images/logo_alipay.png" style="width:80px;height:80px;">
				</div>
				<div class="aui-flex-box">
					<h5 id="zzz" style="font-size:1.2rem;">支付成功、自动到账。</h5>
				</div>
			</div>
			<div id="xxx" class="aui-flex aui-flex-text" style="padding-top:0;">
				<div class="aui-flex-box" style="margin-left:0;">
					<h2 style="font-size:2rem;color:#333">充值金额</h2>
					<h3 style="color:red;"><?php echo $order['money'];?></h3>
					<p style="color:#333;font-size:1rem;">订单号：<?php echo $_GET['trade_no'];?></p>
				</div>
			</div>
			<div id="yyy">
				<button style="width:80%;background: #eace99;border-radius:10px;color:#000;font-size:1.9rem;display:block;margin: 0 auto;border: none;padding:0.8rem 0;box-shadow:0 1px 15px #f0c677;" onclick="repay()">立即支付</button>
			</div>
		</div>
		</body>
			<?php
		}
	}
?>