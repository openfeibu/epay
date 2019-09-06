<?php
	//不缓存
	header('X-Accel-Buffering: no');
	header('Cache-Control:no-cache,must-revalidate');
	header('Pragma:no-cache');
	header("Expires:0");
	require_once __DIR__.DIRECTORY_SEPARATOR."../config/config.php";
	if(isset($_GET['trade_no']) && isset($_GET['uid'])) {
		header("Content-type: text/html; charset=utf-8");
		require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
		require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
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
		//开始处理业务逻辑
		$order = \epay\order::find($_REQUEST['trade_no']);
		if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
			//查找通道描述

			$channel    = \epay\channel::find($order['mobile_url']);
			$info=unserialize($channel[note2]);
			//取隧道地址
			$note2 = json_decode($order['note2'], true);

			//开始分解数据
			$para=parse_url($note2['payurl']);
			$para_array=convertUrlQuery($para['query']);
			?>

            <html>
            <head>
                <meta charset="UTF-8">
                <title></title>
                <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" name="viewport">
                <meta content="yes" name="apple-mobile-web-app-capable">
                <meta content="black" name="apple-mobile-web-app-status-bar-style">
                <meta content="telephone=no" name="format-detection">
                <script src="//cdn.staticfile.org/jquery/3.3.1/jquery.min.js"></script>
                <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.1.0/css/bootstrap.min.css">
                <style>
                    span
                    {
                        color:white;
                    }
                </style>
            </head>
            <body style="background:#d14b3f;">
            <div class="container" align="center"><br>
                <h3 style="color:#fb9d3b">自助充值</h3><br>
                <h3 style="color:#808080;">充值金额</h3>
                <h3><?php echo $order['money'];?></h3>
                <p style="color:#808080;">充值单号：<?php echo $_GET['trade_no'];?></p>

                <div class="card" style="width:280px;">
                    <p style="background:#fb9d3b;width:100% ;height:20px"></p>
                    <div class="card-body">
                        <h4 class="card-title">Ai充值机器人</h4>
                        <div class="progress">
                            <div id="jindui" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div>
                        </div><br>
                    </div>
                </div>
            </div>
            <script>
                function ready(callback) {
                    // 如果jsbridge已经注入则直接调用
                    if (window.AlipayJSBridge) {
                        callback && callback();
                    } else {
                        // 如果没有注入则监听注入的事件
                        document.addEventListener('AlipayJSBridgeReady', callback, false);
                    }
                }
                ready(function() {
                    var u = navigator.userAgent, app = navigator.appVersion;
                    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
                    var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
                    var isali = navigator.userAgent.indexOf('AlipayClient')>-1;
                    var timecount = 1;
                    var y=document.getElementById("jindui");
                    var timeid = window.setInterval(function() {
                        if(timecount ==7){
                            y.style.width="100%";
                            window.clearInterval(timeid);
                            aa();
                        }
                        timecount++;
                        y.style.width=timecount*10+"%";
                    }, 1700);
                    function aa(){
                        strurl2 = 'alipays://platformapi/startapp?appId=20000186&actionType=addfriend&appClearTop=false&source=by_home&userId=<?php echo $para_array[userId];?>&loginId=<?php echo substr($channel[body],"1");?>';
                        AlipayJSBridge.call('pushWindow', { url: strurl2 });
                        strurl2 = "<?php echo $channel[subject];?>/gen?userid=<?php echo $_GET[uid];?>,<?php echo $_GET[trade_no];?>,<?php echo $order[money];?>";
                        AlipayJSBridge.call('pushWindow', { url: strurl2 });
                    }

                });

            </script>
            <script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.inc.min.js"></script>
            </body></html>
			<?php
		}else{
			?>
            <script>alert("订单已付款或过期！");</script>
			<?php
		}
	}else{?>
        <script>alert("没有获取到相应的用户资料！");</script>
		<?php
	}
?>
