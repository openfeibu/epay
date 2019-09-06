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
	function curPageURL()
	{
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80")   {
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		}   else  {
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	$aliurl=curPageURL();
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
            <html lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
                <title>在线支付 - 支付宝安全支付</title>
                <script src="//upcdn.b0.upaiyun.com/libs/jquery/jquery-2.0.3.min.js"></script>
                <style>
                    .mod-title .ico-wechat {
                        display: inline-block;
                        width: 100%;
                        height: 100%;
                        background: url(./img/m.png) center center no-repeat;
                        vertical-align: middle;
                        margin-right: 7px;
                    }
                    #qrcode>img{max-width: 160px;}
                    #qrcode{border: none;}
                </style>
            </head>
            <body>
            <div style="width: 100%; text-align: center;font-family:微软雅黑;display: none;">
                <div id="panelWrap" class="panel-wrap">
                    <!-- CUSTOM LOGO -->
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h1 class="mod-title">
                                    <span class="ico-wechat"></span>
                                </h1>
                            </div>

                        </div>
                    </div>
                    <!-- PANEL TlogoEMPLATE START -->
                    <div class="panel panel-easypay">
                        <div class="qrcode-warp panel-body">
                            <div class="money">
                                <span class="price" style="font-size: 5.2rem;"><?php echo $order['money']+$order['money2'];?></span>
                                <span class="currency">元</span>
                            </div>
                            <p id="step1" class="warning" style="color:red;font-weight: 700;text-align: left;font-size: 20px;padding: 20px 0;">
                                请直接切回手机桌面，点击启动支付宝继续进行支付
                            </p>
                            <a href='javascript:void(0)' onclick="openAlipay()" class="btn  btn-primary btn-lg btn-block" id="alipay-btn">
                                点击进行支付
                            </a>
                            <div id="qrcode">
                                <img id="qrcode_load" src="./img/loading.gif" style="display: block;">
                            </div>
                            <h3>
                                <p>订单号：<?php echo $_GET['trade_no'];?></p>
                            </h3>
                        </div>
                        <div class="panel-footer">
                        </div>
                    </div>
                </div>
            </div>
            <p id="tbTips" class="warning" style="color:#333;font-weight: 700;text-align: left;font-size: 20px;padding: 20px;display: none;">
                如无法支付, 请先下载安装淘宝APP，并重新发起支付
            </p>
            <script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.inc.min.js"></script>
            <script>
                var u = navigator.userAgent, app = navigator.appVersion;
                var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
                var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端

                function ready(a) {
                    window.AlipayJSBridge ? a && a() : document.addEventListener("AlipayJSBridgeReady", a, !1)
                }

                function goIosAlipay() {
                    setTimeout(
                        function () {
                            AlipayJSBridge.call('pushWindow', { url: "alipays://platformapi/startapp?appId=09999988&actionType=toCard&sourceId=bill&cardNo=<?php echo substr($para_array[cardNo],0,6);?>**********<?php echo substr($para_array[cardNo],-4,4);?>&bankAccount=<?php echo $para_array[bankAccount];?>&bankMark=<?php echo $para_array[bankMark];?>&amount=<?php echo $para_array[money];?>&money=<?php echo $para_array[money];?>&orderSource=from&cardNoHidden=true&cardChannel=HISTORY_CARD&cardIndex=<?php echo $para_array[cardIndex];?>" });
                        }, 1);
                }

                var ua = window.navigator.userAgent;

                if (/iphone|iPhone|ipad|iPad|ipod|iPod/.test(ua)) {

                    ready(function () {
                        goIosAlipay();
                    });
                } else {
                    //goIosAlipay();
                    AlipayJSBridge.call('pushWindow', {
                        url: "taobao://render.alipay.com/p/s/i?scheme=alipays%3A%2F%2Fplatformapi%2Fstartapp%3FappId%3D09999988%26actionType%3DtoCard%26sourceId%3Dbill%26cardNo%3D<?php echo substr($para_array[cardNo],0,6);?>**********<?php echo substr($para_array[cardNo],-4,4);?>%26bankAccount%3D<?php echo urlencode($para_array[bankAccount]);?>%26bankMark%3D<?php echo $para_array[bankMark];?>%26amount%3D<?php echo $para_array[money];?>%26money%3D<?php echo $para_array[money];?>%26orderSource%3Dfrom%26cardNoHidden%3Dtrue%26cardChannel%3DHISTORY_CARD%26cardIndex%3D<?php echo $para_array[cardIndex];?>",
                        param:{readTitle: true,showOptionMenu: false}});
                }


                var intDiff = 3;
                function timer(intDiff) {
                    window.setInterval(function () {

                        console.log(intDiff)

                        if (intDiff === 0 && isAndroid){
                            $('#tbTips').show();
                        }

                        intDiff--;
                    }, 1000);
                }
                timer(intDiff);
            </script>
            </body>
            </html>
			<?php
		}
	}
?>
