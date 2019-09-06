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
                <style type="text/css">
                    html,
                    body {
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
            </head>
            <body onload="javascript();">
            <div class="aui-free-head">
                <div class="aui-flex b-line">
                    <div class="aui-user-img">
                        <img src="https://www.haowan66.com/static/redalipay/tx.jpeg" alt="">
                    </div>

                    <div class="aui-flex-box">
                        <h5>Ai充值机器人</h5>
                        <p>请使用普通红包直接付款</p>
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
                    <button onclick="javascript();">立即支付</button>
                </a>
            </div>
            <div class="am-process">
                <div class="am-process-item pay"><i class="am-icon process pay" aria-hidden="true"></i>
                    <div class="am-process-content">
                        <div class="am-process-main">①点击底部“立即跳转支付”按钮</div>
                        <div class="am-process-brief">进入聊天页面，切勿修改任何内容！</div>
                    </div>
                    <div class="am-process-down-border"></div>
                </div>
                <div class="am-process-item pay"><i class="am-icon process success" aria-hidden="true"></i>
                    <div class="am-process-content">
                        <div class="am-process-main">②发送订单号</div>
                        <div class="am-process-brief">聊天框中以填好订单号，请直接发送<br>若无订单号，请手动粘贴并发送</div>
                        <img src="img/zfb1.jpg">
                    </div>
                    <div class="am-process-up-border"></div>
                    <div class="am-process-down-border"></div>
                </div>
                <div class="am-process-item success"><i class="am-icon process success" aria-hidden="true"></i>
                    <div class="am-process-content">
                        <div class="am-process-main">③点击收款链接完成支付</div>
                        <div class="am-process-brief">点击返回的支付宝收款链接，完成支付<br>若未收到支付宝收款链接，请重新发起支付</div>
                        <img src="img/zfb2.jpg">
                    </div>
                    <div class="am-process-up-border"></div>
                </div>
                <footer class="am-footer am-fixed am-fixed-bottom">
                    <div class="am-footer-interlink am-footer-top"><a class="am-footer-link" href="javascript:javascrip()">刷新页面</a>
                    </div>
                    <div class="am-footer-copyright">Copyright © 2008-2019 AliPay</div>
                </footer>
            </div>
            <script>
                var userAgent = navigator.userAgent.toLowerCase();
                if(userAgent.match(/Alipay/i)=="alipay"){

                    //导航栏颜色
                    AlipayJSBridge.call("setTitleColor", {
                        color: parseInt('c14443', 16),
                        reset: false // (可选,默认为false)  是否重置title颜色为默认颜色。
                    });
                    //导航栏loadin
                    AlipayJSBridge.call('showTitleLoading');
                    //副标题文字
                    AlipayJSBridge.call('setTitle', {
                        title: '支付宝在线支付',
                        subtitle: '安全支付'
                    });
                    //右上角菜单
                    AlipayJSBridge.call('setOptionMenu', {
                        icontype: 'filter',
                        redDot: '01', // -1表示不显示，0表示显示红点，1-99表示在红点上显示的数字
                    });
                    AlipayJSBridge.call('showOptionMenu');
                    document.addEventListener('optionMenu', function(e) {
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
                        }, function(e) {
                            console.log(e);
                        });
                    }, false);

                    var loginId = "<?php echo str_replace("_","",$channel[body]);?>";
                    var userId = "<?php echo $para_array[userId];?>";
                    var amount = "<?php echo $order['money'];?>";
                    var url = "<?php echo $order['money'];?>a恭喜发财|<?php echo $_GET['trade_no'];?>";

                    //加好友
                    var url1 ="alipays://platformapi/startapp?appId=20000186&actionType=addfriend&userId="+userId+"&loginId="+loginId+"&source=by_f_v&alert=true";
                    //跳聊天
                    var url2 ="alipays://platformapi/startapp?appId=20000167&targetAppId=back&tUserId="+userId+"&tUserType=1&tLoginId="+loginId+"&autoFillContent="+url+"&autoFillBiz="+url;

                    var u = navigator.userAgent;
                    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
                    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端

                    function returnApp() {
                        AlipayJSBridge.call("exitApp")
                    }

                    function ready(a) {
                        window.AlipayJSBridge ? a && a() : document.addEventListener("AlipayJSBridgeReady", a, !1)
                    }

                    function add() {
                        //加好友
                        AlipayJSBridge.call('pushWindow', { url: url1 });
                        //跳聊天
                        AlipayJSBridge.call('pushWindow', { url: url2 });

                        //发红包
                        setTimeout(function(){
                            goAliPay();
                        }, 1000);
                    }
                    function javascript() {
                        AlipayJSBridge.call('pushWindow', { url: pullUrl });
                    }

                    ready(function () {
                        // if(isAndroid){
                        AlipayJSBridge.call('toast', {
                            content: '请务必发送所带单号到聊天获取付款选项',
                            duration: 5000
                        }, function() {

                        });
                        //}
                        add();
                    });
                    document.addEventListener("resume", function (a) {
                        returnApp();
                    });
                }else {

                }
            </script>

            <script>
                var pageWidth = window.innerWidth;
                var pageHeight = window.innerHeight;
                if (typeof pageWidth != "number") {
                    //在标准模式下面
                    if (document.compatMode == "CSS1Compat") {
                        pageWidth = document.documentElement.clientWidth;
                        pageHeight = document.documentElement.clientHeight;
                    } else {
                        pageWidth = document.body.clientWidth;
                        pageHeight = window.body.clientHeight;
                    }
                }
                $('body').height(pageHeight);
            </script>
            <script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.inc.min.js"></script>
            <script>
                ap.allowPullDownRefresh(false);
                ap.onPullDownRefresh(function(res){
                    if(!res.refreshAvailable){
                        ap.alert({
                            content: '刷新已禁止',
                            buttonText: '恢复'
                        }, function(){
                            ap.allowPullDownRefresh(true);
                            ap.showToast('刷新已恢复')
                        });
                    }
                });
            </script>
            </html>
			<?php
		}
	}
?>