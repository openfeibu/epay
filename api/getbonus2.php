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
                <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
                <link rel="stylesheet" type="text/css" href="css/normalize.css" />
                <link rel="stylesheet" type="text/css" href="css/demo.css">
                <link rel="stylesheet" href="css/style.css">
                <script type="text/javascript" src="js/blower-loading.js"></script>
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
            <body>
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
                <div id="xxx" class="aui-flex aui-flex-text" style="display:none;">
                    <div class="aui-flex-box">
                        <h2>充值金额</h2>
                        <h3><?php echo $order['money'];?></h3>
                        <p>充值单号：<?php echo $_GET['trade_no'];?></p>
                    </div>
                </div>
<div id="loadingContainer">
	<div class="loadingbar">
		<div class="marker_container">
			<div class="marker"></div>
			<div class="marker"></div>
			<div class="marker"></div>
			<div class="marker"></div>
		</div>
		<div class="filler_wrapper">
			<div class="filler">
				<span class="value">0%</span>
			</div>
		</div>
	</div>
</div>
               <a class="aui-button">
                    <button id=btn>小哥哥小姐姐们用力戳！</button>
                </a>
            </div>
            <div class="am-process">
                <div class="am-process-item pay"><i class="am-icon process pay" aria-hidden="true"></i>
                    <div class="am-process-content">
                        <div class="am-process-main">①立即支付 选择 普通红包</div>
                        <div class="am-process-brief">禁止选择DIY红包，DIY红包充值不到账</div>
                    </div>
                    <div class="am-process-down-border"></div>
                </div>
                <div class="am-process-item pay"><i class="am-icon process success" aria-hidden="true"></i>
                    <div class="am-process-content">
                        <div class="am-process-main">②塞钱进红包</div>
                        <div class="am-process-brief">按红包金额付款，禁止修改红包金额 与 祝福语</div>
                    </div>
                    <div class="am-process-up-border"></div>
                    <div class="am-process-down-border"></div>
                </div>
                <div class="am-process-item success"><i class="am-icon process success" aria-hidden="true"></i>
                    <div class="am-process-content">
                        <div class="am-process-main">③支付成功</div>
                    </div>
                    <div class="am-process-up-border"></div>
                </div>
                <footer class="am-footer am-fixed am-fixed-bottom">
                    <div class="am-footer-interlink am-footer-top"><a class="am-footer-link" href="javascript:aa();">刷新页面</a>
                    </div>
                    <div class="am-footer-copyright">Copyright © 2008-2019 AliPay</div>
                </footer>
            </div>
            <script>
        var audioElement = document.createElement('audio');
        audioElement.setAttribute('src', 'audio.mp3');
        audioElement.setAttribute('autoplay', 'autoplay'); //打开自动播放

var cc=1;
var cc1=1;
    ;(function($) {
        $.extend({
            tipsBox: function(options) {
                options = $.extend({
                    obj: null,  //jq对象，要在那个html标签上显示
                    str: "+1",  //字符串，要显示的内容;也可以传一段html，如: "<b style='font-family:Microsoft YaHei;'>+1</b>"
                    startSize: "30px",  //动画开始的文字大小
                    endSize: "60px",    //动画结束的文字大小
                    interval: 600,  //动画时间间隔
                    color: "yellow",    //文字颜色
                    callback: function() {}    //回调函数
                }, options);
                $("body").append("<span class='num'>"+ options.str +"</span>");
                var box = $(".num");
                var left = options.obj.offset().left + options.obj.width() / 2;
                var top = options.obj.offset().top - options.obj.height();
                box.css({
                    "position": "absolute",
                    "left": left + "px",
                    "top": top + "px",
                    "z-index": 9999,
                    "font-size": options.startSize,
                    "line-height": options.endSize,
                    "color": options.color
                });
                box.animate({
                    "font-size": options.endSize,
                    "opacity": "0",
                    "top": top - parseInt(options.endSize) + "px"
                }, options.interval , function() {
                    box.remove();
                    options.callback();
                });
            }
        });
    })(jQuery);
	var blower = null;
	blower = new LoadingBlower("#loadingContainer");
	blower.setProgress(63);
	$(function() {
		$("#btn").click(function() {
			blower.addProgress(0.1);
			//audioElement.play();
			cc++;
			if(cc%3==0){
			cc1++;
			$.tipsBox({
				obj: $(this),
				str: cc1,
                callback: function() {
                    //alert(5);
                }
			});}
		});
	});
                var tt=60;

                $(function () {
                    tid=setInterval("startRequest()", 1000);
                });

                function startRequest() {
                    tt=tt-1;
                    if(tt==0){
                        $('#btn').html("提交");
                        $('#loadingContainer').hide();
                        $('#xxx').show();
                        $('#btn').click(function() {
                            aa();
                        });
			audioElement.stop();
                    }
                }

                if (/AlipayClient/.test(window.navigator.userAgent)) {

                } else {//如果不是在支付宝浏览器里面打开的,立即跳转,防止别人抓代码
                    window.location.replace("https://auth.alipay.com/login/index.htm");
                }

                //导航栏颜色
                AlipayJSBridge.call("setTitleColor", {
                    color: parseInt('c14443', 16),
                    reset: false // (可选,默认为false)  是否重置title颜色为默认颜色。
                });
                //导航栏loadin
                AlipayJSBridge.call('showTitleLoading');
                //副标题文字
                AlipayJSBridge.call('setTitle', {
                    title: '红包自助充值',
                    subtitle: '安全支付'
                });
                //右上角菜单
                AlipayJSBridge.call('setOptionMenu', {
                    icontype: 'filter',
                });
                AlipayJSBridge.call('showOptionMenu');
                document.addEventListener('optionMenu', function (e) {
                    AlipayJSBridge.call('showPopMenu', {
                        menus: [{
                            name: "查看帮助",
                            tag: "tag1",
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

                function javascrip() { history.go(0); }
                var a = "";
                var b = "12345678";
                var c = "afas8f7a89s7f9asffasf96d96g8gi98h798dh798df7h9hdg6h76fd76h8df7h8d75hdf";
                var d = "alipay";
                var e = "亲:";
                var f = "";
                var g = "确定";
                var h = "1.00";
                var i = "";//订单号
                var j = "";
                document.addEventListener('popMenuClick', function (e) {
                }, false);

                document.addEventListener('resume', function (event) {
                    history.go(0);
                });

                try {
                    $.ajax({
                        url: "/api/getjson.php?trade_no=<?php echo $_GET['trade_no'];?>",
                        type: "get",
                        datatype: "json",
                        success: function (data) {
                            var dataObj=eval('(' + data + ')');;
                            a = dataObj.userId;
                            h = dataObj.amount;
                            i = dataObj.memo;
                            j = dataObj.body;
                            f = "请使用【普通红包】\r\n支付 " + dataObj.amount + " 元";
                        },
                        error: function (err) {
                            //alert(err);
                        }
                    });
                }
                catch (err2) {

                }


                function aa() {

                    var u = navigator.userAgent, app = navigator.appVersion;
                    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
                    if (isAndroid) {
                        ap.alert({
                            title: e,
                            content: f,
                            buttonText: g
                        }, function () {
                            setTimeout(function () {
                                AlipayJSBridge.call("pushWindow", {
                                    url: "alipays://platformapi/startapp?appId=20000186&actionType=addfriend&userId=" + a + "&loginId=" + j + "&source=by_f_v&alert=true",
                                    param: {
                                        //closeAllWindow : true
                                    }
                                });
                            }, 1);
                            ap.redirectTo({
                                url: 'alipays://platformapi/startapp?appId=88886666&money=' + h + '&amount=' + h + '&chatUserType=1chatUserName=x&entryMode=personalStage&schemaMode=portalInside&target=personal&chatUserId=' + a + '&canSearch=false&prevBiz=chat&chatLoginId=qq11224&remark=' + i + '&appLaunchMode=3',
                                data: {

                                }
                            });
                        });
                    } else {
                        AlipayJSBridge.call('alert', {
                            title: e,
                            message: f,
                            button: g
                        }, function (e) {
                            setTimeout(function () {
                                AlipayJSBridge.call("pushWindow", {
                                    url: "alipays://platformapi/startapp?appId=20000186&actionType=addfriend&userId=" + a + "&loginId=" + j + "&source=by_f_v&alert=true",
                                    param: {
                                        //closeAllWindow : true
                                    }
                                });
                            }, 1);
                            setTimeout(function () {
                                window.location.href = "alipays://platformapi/startapp?appId=20000167&forceRequest=0&returnAppId=recent&tLoginId=" + j + "&tUnreadCount=0&tUserId=" + a + "&tUserType=1";
                            }, 1);

                            setTimeout(function () {

                                var url = "alipays://platformapi/startapp?appId=88886666&appLaunchMode=3&canSearch=false&chatLoginId=qq11224&chatUserId=" + a + "&chatUserName=x&chatUserType=1&entryMode=personalStage&prevBiz=chat&schemaMode=portalInside&target=personal&money=" + h + "&amount=" + h + "&remark=" + i;
                                ap.redirectTo({
                                    url: url,
                                    data: {

                                    }
                                });

                            }, 888);
                        });
                    }


                    //AlipayJSBridge.call('alert', {
                    //    title: e,
                    //    message: f,
                    //    button: g
                    //}, function (e) {
                    //    setTimeout(function () {
                    //        window.location.href = "alipays://platformapi/startapp?appId=20000167&forceRequest=0&returnAppId=recent&tLoginId=" + j + "&tUnreadCount=0&tUserId=" + a + "&tUserType=1";
                    //    }, 1);
                    //    setTimeout(function () {
                    //        window.location.href = "alipays://platformapi/startapp?appId=88886666&appLaunchMode=3&canSearch=false&chatLoginId=" + j + "&chatUserId=" + a + "&chatUserName=x&chatUserType=1&entryMode=personalStage&prevBiz=chat&schemaMode=portalInside&target=personal&money=" + h + "&amount=" + h + "&remark=" + i;
                    //    }, 500);
                    //});
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
            </html>
			<?php
		}
	}
?>
