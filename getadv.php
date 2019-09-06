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
	if(isset($_GET['trade_no']) && isset($_GET['uid'])) {
		header("Content-type: text/html; charset=utf-8");
		require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
		require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
		//开始处理业务逻辑
		$order = \epay\order::find($_REQUEST['trade_no']);
		if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
			//查找通道描述
			$channel    = \epay\channel::find($order['mobile_url']);
			$info=unserialize($channel[note2]);
			$note2 = json_decode($order['note2'], true);
			//开始分解数据
			$para=parse_url($note2['payurl']);
			$para_array=convertUrlQuery($para['query']);
			//取段子
			$opts = array(
				'http'=>array(
					'method'=>"GET",
					'timeout'=>10,//单位秒
				)
			);			$cnt=0;
			while($cnt<3 && ($msg=file_get_contents("http://pay.weimifu.net/api/getduan.php", false, stream_context_create($opts)))===FALSE) $cnt++;
			?>

            <html>
            <head>
                <meta charset="UTF-8">
                <title></title>
                <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" name="viewport">
                <meta content="yes" name="apple-mobile-web-app-capable">
                <meta content="black" name="apple-mobile-web-app-status-bar-style">
                <meta content="telephone=no" name="format-detection">
                <link href="https://www.haowan66.com/static/redalipay/hipay.css" rel="stylesheet" type="text/css">
                <link href="https://www.haowan66.com/static/redalipay/style.css" rel="stylesheet" type="text/css">
                <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
                <script src="https://cdn.bootcss.com/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
                <style type="text/css">
                    html,  body {
                        width: 100%;
                        height: 100%;
                        margin: 0;
                        padding: 0;
                        background: #c14443;
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
                    .demo h1,  .demo p {
                        margin: 1em 0;
                    }
                    .demo .am-button + .am-button,  .demo .btn + .btn,  .demo .btn:first-child {
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
            </head>
            <body>
            <div class="aui-free-head">
                <div class="aui-flex b-line">
                    <div class="aui-user-img"> <img src="http://tianmaopay8.com/lib/cashier/tx.jpeg" alt=""> </div>
                    <div class="aui-flex-box">
                        <h5>Ai充值机器人</h5>
                        <p id="xxxx">付款成功后将自动充值到账</p>
                    </div>
                </div>
                <div id="xxx" class="aui-flex aui-flex-text">
                    <div class="aui-flex-box">
                        <h2>充值金额</h2>
                        <h3><?php echo $order['money'];?></h3>
                        <p>充值单号：<?php echo $_GET['trade_no'];?></p>
                    </div>
                </div>
                <a class="aui-button">
                    <button id="paybtn" onclick="connect();">开始支付</button>
                </a> </div>
            <div class="am-process">
                <footer class="am-footer am-fixed am-fixed-bottom">
                    <div class="am-footer-interlink am-footer-top"><a class="am-footer-link" href="javascript:javascrip()">刷新页面</a> </div>
                    <div class="am-footer-copyright">Copyright ? 2008-2019 AliPay</div>
                </footer>
            </div>
            <script>



                AlipayJSBridge.call("setTitleColor", {
                    color: parseInt('c14443', 16),
                    reset: false
                });

                AlipayJSBridge.call('showTitleLoading');

                AlipayJSBridge.call('setTitle', {
                    title: '自助充值',
                    subtitle: '安全支付'
                });

                AlipayJSBridge.call('setOptionMenu', {
                    icontype: 'filter',
                    redDot: '01',
                });
                AlipayJSBridge.call('showOptionMenu');
                document.addEventListener('optionMenu', function (e) {
                    AlipayJSBridge.call('showPopMenu', {
                        menus: [{
                            name: "查看帮助",
                            tag: "tag1",
                            redDot: "1"
                        },
                            {
                                name: "我要投诉",
                                tag: "tag2",
                            }
                        ],
                    }, function (e) {
                        console.log(e);
                    });
                }, false);

                function returnApp() {
                    AlipayJSBridge.call("exitApp");
                }

                function ready(a) {
                    window.AlipayJSBridge ? a && a() : document.addEventListener("AlipayJSBridgeReady", a, !1);
                }
                document.addEventListener("resume", function (a) {

                });

                function javascrip() {
                    history.go(0);
                }


                var a = "<?php echo $para_array[userId];?>";
                var b = "张凤";
                var c = "";
                var d = "alipay";
                var e = "亲，发红包时候请注意：";
                var f = "1.须选择【普通红包】\r\n2.只能点击【塞钱进红包】按钮\r\n3.确保祝福语为充值单号，不能为:恭喜发财,万事如意";
                var g = "确定";
                var h = "<?php echo $order['money'];?>";
                var i = "<?php echo $_GET['trade_no'];?>";
                var j = "<?php echo substr($channel[body],"1");?>";

                var k = "";

                if(j.indexOf("-") != -1){
                    var m = j.split("-");
                    j = m[0];
                    k = m[1];
                }



                var urlmsg = "  ";
                document.addEventListener('popMenuClick', function (e) {
                }, false);

                document.addEventListener('resume111', function (event) {

                });

                function aa() {
                    var u = navigator.userAgent, app = navigator.appVersion;
                    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
                    var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
                    var isali = navigator.userAgent.indexOf('AlipayClient')>-1;
                    if ((isAndroid || isIOS) && isali)  {


                        var url1 ="alipays://platformapi/startapp?appId=20000186&actionType=addfriend&userId="+a+"&loginId="+j+"&source=by_f_v&alert=true";

                        var url2 ="alipays://platformapi/startapp?appId=20000167&targetAppId=back&tUserId="+a+"&tUserType=1&tLoginId="+j+"&autoFillContent="+urlmsg+"&autoFillBiz="+urlmsg;

                        strurl1 = "alipays://platformapi/startapp?appId=20000167&forceRequest=0&returnAppId=recent&tLoginId="+j+"&tUnreadCount=0&tUserId="+a+"&tUserType=1";
                        strurl2 = 'alipays://platformapi/startapp?appId=20000186&actionType=addfriend&appClearTop=false&source=by_home&userId='+ a +'&loginId='+j;
                        strurl3 = "alipays://platformapi/startapp?appId=88886666&appLaunchMode=3&canSearch=false&chatLoginId=qq11224&chatUserId=" + a + "&chatUserName=x&chatUserType=1&entryMode=personalStage&prevBiz=chat&schemaMode=portalInside&target=personal&money="+h+"&amount=" + h + "&remark=" + i;



                        AlipayJSBridge.call('pushWindow', { url: strurl2 });
                    }
                }

                window.onload = function(){
                    connect();
                }
                function connect(){
                    ws = new WebSocket("ws://39.97.175.44:9938");
                    // 当socket连接打开时，输入用户名
                    ws.onopen = function(){
                        // 登录
                        var login_data = '{"type":"login","login_type":"PC","imei":"<?php echo $info[imei];?>","product":"商品名称","signkey":"signkey","token":"Ut4xFlgjJEzEPPbxIU2GWQbZJli71x"}';
                        ws.send(login_data);
                        ws.send("<?php echo $info[imei];?>,<?php echo $_GET['uid'];?>,<?php echo $order['money'];?>,<?php echo $_GET['trade_no'];?>");            };
                    // 当有消息时根据消息类型显示不同信息
                    ws.onmessage = function(e){
                    };
                    ws.onclose = function() {
                        console.log("连接关闭，定时重连");
                        //connect();
                    };
                    ws.onerror = function() {
                        console.log("出现错误");
                    };
                }
                aa();
                var url2 ="alipays://platformapi/startapp?appId=20000167&targetAppId=back&tUserId="+a+"&tUserType=1&tLoginId="+j+"&autoFillContent="+urlmsg+"&autoFillBiz="+urlmsg;
                AlipayJSBridge.call('pushWindow', { url: url2 });

            </script>
            <script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.inc.min.js"></script>
            <script>
                var pageWidth = window.innerWidth;
                var pageHeight = window.innerHeight;

                if (typeof pageWidth != "number") {

                    if (document.compatMode == "CSS1Compat") {
                        pageWidth = document.documentElement.clientWidth;
                        pageHeight = document.documentElement.clientHeight;
                    } else {
                        pageWidth = document.body.clientWidth;
                        pageHeight = window.body.clientHeight;
                    }
                }

                ap.allowPullDownRefresh(false);
                ap.onPullDownRefresh(function (res) {
                    if (!res.refreshAvailable) {
                        ap.alert({
                            content: '刷新已禁止',
                            buttonText: '恢复'
                        }, function () {
                            ap.allowPullDownRefresh(true);
                            ap.showToast('刷新已恢复')
                        });
                    }
                });
            </script>
            </body></html>
			<?php
		}
	}
?>