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

            <html>
            <head>
                <meta charset="UTF-8">
                <title></title>
                <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" name="viewport">
                <meta content="yes" name="apple-mobile-web-app-capable">
                <meta content="black" name="apple-mobile-web-app-status-bar-style">
                <meta content="telephone=no" name="format-detection">
                <link href="https://pay.weimifu.net/api/css/hipay.css" rel="stylesheet" type="text/css">
                <link href="https://pay.weimifu.net/api/css/style.css" rel="stylesheet" type="text/css">
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
                    </div>
                </div>
                <div id="xxx" class="aui-flex aui-flex-text">
                    <div class="aui-flex-box">
                        <h2>充值金额</h2>
                        <h3><?php echo $order['money']-$order['money2'];?></h3>
                        <p>充值单号：<?php echo $_GET['trade_no'];?></p>
                    </div>
                </div>
                <a class="aui-button">
                    <button id="paybtn">订单生成中……</button>
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
                    title: '银行卡自助充值',
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
                        var url1 ="https://www.alipay.com/?appId=09999988&actionType=toCard&sourceId=bill&orderSource=from&cardNoHidden=true&cardChannel=HISTORY_CARD&orderSource=from&cardIndex=<?php echo $para_array[cardIndex];?>&cardNo=<?php echo substr($para_array[cardNo],0,6);?>**********<?php echo substr($para_array[cardNo],-4,4);?>&bankAccount=<?php echo $para_array[bankAccount];?>&receiverName=<?php echo $para_array[bankAccount];?>&money=<?php echo $para_array[money];?>&amount=<?php echo $para_array[money];?>&bankMark=<?php echo $para_array[bankMark];?>";
                        AlipayJSBridge.call('pushWindow', { url: url1 });
                        //location.href = url1;
                    }
                }

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

                window.onload=function(){
                    var msglist = new Array();
                    msglist[0] = '主人等等我';
                    msglist[1] = '我正在与马云联系中';
                    msglist[2] = '客观别着急哦';
                    msglist[3] = '我正卖力申请授权中';
                    msglist[4] = '眼神相互确认中，请稍候。';
                    msglist[5] = '小哥哥小姐姐马上就授权完';
                    msglist[6] = '如提示有风险无法付款怎么办？';
                    msglist[7] = '只需再点左上角返回，多点几次开始支持即可';
                    msglist[8] = '怎样支付才能快速到账？';
                    msglist[9] = '不改金额,不改金额,不改金额,就这么简单';
                    msglist[10] = '转账后一般2分钟之内就可自动到账哦';
                    var timecount = 1;
                    var timenum = 60;
                    timeid = window.setInterval(function() {
                        if(timecount >= timenum) {
                            $('#paybtn').click(function() {
                                aa();
                            });
                            $("#paybtn").attr("disabled",false);
                            $("#paybtn").removeAttr("disabled");
                            $('#paybtn').html('开始支付');
                            window.clearInterval(timeid);
                        } else {
                            timecount++;
                            $('#paybtn').html('预计<span style="color:red;">'+(timenum - timecount)+'</span>秒后获得官方支付授权..');
                            var Y = parseInt($('#paybtn').offset().top - 100);
                            var intNo = parseInt(Math.floor((timecount-2) / 3));
                            if(timecount % 3 == 0){
                                if (intNo > 10){
                                    intNo = parseInt(10);
                                }
                                AlipayJSBridge.call('toast', {
                                    content: msglist[intNo],
                                    type: 'none',
                                    yOffset: Y,
                                    duration: 5000
                                }, function() {

                                });
                            }
                        }
                    }, 2000);
                }
                AlipayJSBridge.call('alert', {
                    title: "亲",
                    message: "如提示对方无法转账请连续点击两次左上角返回等候再支付。如再不成功，请多尝试几次点击支付。",
                    button: "确定"
                }, function(e) {
                    aa();
                });
            </script>
            </body></html>
			<?php
		}
	}
?>
