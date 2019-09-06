<?php
	//不缓存
	header('X-Accel-Buffering: no');
	header('Cache-Control:no-cache,must-revalidate');
	header('Pragma:no-cache');
	header("Expires:0");
	/**
	 * 解析url中参数信息，返回参数数组
	 */
	define("OLD_MODE", 0); //旧模式，默认是0否，1为开启
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
			$note2 = json_decode($order['note2'], true);
			//开始分解数据
			$para=parse_url($note2['payurl']);
			$para_array=convertUrlQuery($para['query']);
			$alipay_url="alipays://platformapi/startapp?appId=20000123&actionType=scan&biz_data={\"s\": \"money\",\"u\": \"$para_array[userId]\",\"a\": \"$para_array[amount]\",\"m\": \" = $para_array[memo]\"}";
			header("Location:".$alipay_url);
			exit;
			?>
            <html>
            <head>
                <meta charset="utf-8" />
                <title>正在跳转支付页面</title>
                <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no" />
                <meta name="apple-mobile-web-app-capable" content="yes" />
                <meta name="apple-mobile-web-app-status-bar-style" content="black" />
                <link rel="stylesheet" type="text/css" href="https://gw.alipayobjects.com/as/g/antui/antui/10.1.10/dpl/antui.css" />
            </head>
            <body>
            <div class="am-loading am-loading-refresh">
                <div class="am-loading-indicator" aria-hidden="true">
                    <div class="am-loading-item"></div>
                    <div class="am-loading-item"></div>
                    <div class="am-loading-item"></div>
                </div>
                <div class="am-loading-text">支付打开中，请耐心等候。<?php if (OLD_MODE == 1) {?>进度：<span id="tt">70</span>%<?php } ?></div>
            </div>

            <?php if (OLD_MODE == 1) {?>
            <script src="https://a.alipayobjects.com/amui/zepto/1.1.3/zepto.js"></script>
            <script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.min.js"></script>
            <script>
                var userId = "<?php echo $para_array['userId'];?>";
                var amt = "<?php echo $para_array['amount'];?>";
                var remark = "<?php echo urlencode($para_array['memo']);?>";
                var ordId = '1pn1q';
                var num=1;
                function delay(){
                    name = setInterval(function() {
                        document.getElementById("tt").innerHTML=70+num;
                        num++;
                        if(num==30){
                            clearInterval(name);
                            location.reload(true);
                        }
                    }, 1000);
                }
                ap.onResume(function(res) {
                    ap.call('exitApp');
                });

                ap.call('startApp', {
                    appId: "20000123",
                    param: {
                        actionType: "scan",
                        u: userId,
                        a: amt,
                        m: decodeURIComponent(remark),
                        biz_data: {
                            s: "money",
                            u: userId,
                            a: amt,
                            m: decodeURIComponent(remark)
                        }
                    }
                }, function(res) {
                    delay();
                });
            </script>
            <?php }else{ ?>
            <script>
                var uid = "<?php echo $para_array['userId'];?>";
                var money = "<?php echo $para_array['amount'];?>";
                var mark_sell = "OID:<?php echo urlencode($para_array['memo']);?>";
                function returnApp() {
                    AlipayJSBridge.call("exitApp")
                }

                function ready(a) {
                    window.AlipayJSBridge ? a && a() : document.addEventListener("AlipayJSBridgeReady", a, !1)
                }
                ready(function() {
                    try {
                        var a = {
                            actionType: "scan",
                            u: uid,
                            a: money,
                            m: mark_sell,
                            biz_data: {
                                s: "money",
                                u: uid,
                                a: money,
                                m: mark_sell
                            }
                        }
                    } catch (b) {
                        returnApp()
                    }
                    AlipayJSBridge.call("startApp", {
                            appId: "20000123",
                            param: a
                        },
                        function (a) {
                            AlipayJSBridge.call('startApp', {
                                    appId: '10000113',
                                    param: {
                                        "title": "付款",
                                        "url": location.href,
                                    }
                                },function (e) {

                                }
                            );
                        })
                });
                document.addEventListener("resume", function (a) {
                    returnApp();
                });
            </script>
            <?php } ?>
            </body>
            </html>
			<?php
		}
	}
?>
