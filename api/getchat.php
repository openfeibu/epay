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
                <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.1.0/css/bootstrap.min.css">
                <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" name="viewport">
                <meta content="yes" name="apple-mobile-web-app-capable">
                <meta content="black" name="apple-mobile-web-app-status-bar-style">
                <meta content="telephone=no" name="format-detection">
                <script src="//upcdn.b0.upaiyun.com/libs/jquery/jquery-2.0.3.min.js"></script>
                <style type="text/css">
                    .demo h1{padding-left: 8px; font-size: 24px; line-height: 1.2; border-left: 3px solid #108EE9;} .demo h1, .demo p{margin: 1em 0;} .demo .am-button + .am-button, .demo .btn + .btn, .demo .btn:first-child{margin-top: 10px;} input{display: block; padding: 4px 10px; margin: 10px 0; line-height: 28px; width: 100%; box-sizing: border-box;}
                    .am-process{min-height:280px;padding:20px 20px 15px;position:relative;-webkit-box-sizing:border-box;box-sizing:border-box;background:-webkit-linear-gradient(to top, #eee, #eee 33%, transparent 33%) left bottom no-repeat;background:linear-gradient(to top, #eee, #eee 33%, transparent 33%) left bottom no-repeat;-webkit-background-size:100% 0px;background-size:100% 0px;background-color:#c14443}
                    .am-process .am-process-item{display:block;position:relative;min-height:40px;padding-top:30px}
                    .am-process .am-process-item .am-icon.process,.am-process .am-process-item .am-icon.process-inpage{position:absolute;top:30px;left:0;height:24px;width:24px;z-index:100}
                    .am-process .am-process-item .am-process-content{margin-left:39px}
                    .am-process .am-process-item .am-process-content .am-process-main{font-size:17px;color:#000;line-height:24px;margin-bottom:3px}
                    .am-process .am-process-item .am-process-content .am-process-brief{font-size:15px;color:#f9eeee;line-height:20px;word-break:break-all}
                    .am-process .am-process-item.unpay .am-process-content .am-process-main{color:#888}
                    .am-process .am-process-item .am-process-down-border,.am-process .am-process-item .am-process-up-border{position:absolute;left:11px;width:2px;border:0;background-color:#108ee9}
                    .am-process .am-process-item .am-process-up-border{top:0;height:30px}
                    .am-process .am-process-item .am-process-down-border{top:40px;height:100%}
                    .am-process .am-process-item:first-child{padding-top:0}
                    .am-process .am-process-item:first-child .am-process-down-border{top:10px}
                    .am-process .am-process-item:first-child .am-icon.process{top:0}
                    .am-process .am-process-item.pay .am-process-down-border,.am-process .am-process-item.pay .am-process-up-border{background-color:#108ee9}
                    .am-process .am-process-item.unpay .am-process-down-border,.am-process .am-process-item.unpay .am-process-up-border{background-color:#c7c7cc}
                    .am-process .am-process-item.fail .am-process-down-border,.am-process .am-process-item.fail .am-process-up-border{background-color:#f4333c}
                    .am-process.inpage .am-process-item{min-height:28px;padding-top:10px}
                    .am-process.inpage .am-process-item .am-icon.process-inpage{top:10px;height:18px;width:18px}
                    .am-process.inpage .am-process-item .am-process-content{margin-left:28px}
                    .am-process.inpage .am-process-item .am-process-content .am-process-main{font-size:15px;line-height:18px}
                    .am-process.inpage .am-process-item .am-process-content .am-process-em{color:#F96268;font-size:12px;line-height:15px;padding-bottom:3px}
                    .am-process.inpage .am-process-item .am-process-content .am-process-brief{font-size:12px;line-height:15px;max-height:30px;overflow:hidden}
                    .am-process.inpage .am-process-item .am-process-down-border,.am-process.inpage .am-process-item .am-process-up-border{left:8px;height:20px}
                    .am-process.inpage .am-process-item .am-process-down-border{top:20px;height:100%}
                    .am-process.inpage .am-process-item:first-child{padding-top:0}
                    .am-process.inpage .am-process-item:first-child .am-process-down-border{top:10px}
                    .am-process.inpage .am-process-item:first-child .am-icon.process-inpage{top:0}
                    .am-process.inpage .am-process-item.fail .am-process-down-border,.am-process.inpage .am-process-item.fail .am-process-up-border{background-color:#ccc}
                    .am-process.inpage .am-process-item.up-twoline{padding-top:18px}
                    .am-process.inpage .am-process-item.up-twoline .am-icon.process-inpage{top:18px}
                    .am-process.inpage .am-process-item.up-twoline .am-process-up-border{height:25px}
                    .am-process.inpage .am-process-item.up-moreline{padding-top:30px}
                    .am-process.inpage .am-process-item.up-moreline .am-icon.process-inpage{top:30px}
                    .am-process.inpage .am-process-item.up-moreline .am-process-up-border{height:38px}
                    .am-process .am-icon.process,.am-process .am-icon.process-inner-page{background:url("") no-repeat;background-size:24px auto;-webkit-background-size:24px auto}
                    .am-process .am-icon.process.fail{background-position:0 0}
                    .am-process .am-icon.process.pay{background-position:0 -24px}
                    .am-process .am-icon.process.success{background-position:0 -48px}
                    .am-process .am-icon.process.unpay{background-position:0 -72px}
                    .am-process .am-icon.process-inner-page.fail{background-position:0 -96px}
                    .am-process .am-icon.process-inner-page.success{background-position:0 -114px}
                    .am-footer-wrap .am-footer{position:absolute;top:250px}
                    .am-footer{width:100%;margin-bottom:13px}
                    .am-footer .am-footer-copyright{color:#ccc;font-size:12px;line-height:14px;text-align:center}
                    .am-footer .am-footer-interlink{text-align:center;margin-bottom:5px;font-size:12px;line-height:14px}
                    .am-footer .am-footer-interlink a.am-footer-link{color:#108ee9;vertical-align:middle;margin-right:-3px}
                    .am-footer .am-footer-link+.am-footer-link{margin-left:10px}
                    .am-footer .am-footer-link+.am-footer-link:before{content:'';width:0;border-left:1px solid #ccc;margin-right:11px;height:12px;-webkit-transform:scaleX(.5);transform:scaleX(.5);display:inline-block;vertical-align:-2px}
                    .am-footer .am-footer-top{margin-top:50px}
                    html,body{color:#333;margin:0;height:100%;font-family:"Myriad Set Pro","Helvetica Neue",Helvetica,Arial,Verdana,sans-serif;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;font-weight:normal}
                    *{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
                    a{text-decoration:none;color:#000}
                    a,label,button,input,select{-webkit-tap-highlight-color:rgba(0, 0, 0, 0)}
                    img{border:0}
                    body{background:#fff;color:#666}
                    html,body,div,dl,dt,dd,ol,ul,li,h1,h2,h3,h4,h5,h6,p,blockquote,pre,button,fieldset,form,input,legend,textarea,th,td{margin:0;padding:0}
                    a{text-decoration:none;color:#08acee}
                    button{outline:0}
                    img{border:0}
                    button,input,optgroup,select,textarea{margin:0;font:inherit;color:inherit;outline:none}
                    li{list-style:none}
                    a{color:#666}
                    .b-line{position:relative}
                    .b-line:after{content:'';position:absolute;z-index:2;bottom:0;left:0;width:100%;height:1px;border-bottom:1px solid #e2e2e2;-webkit-transform:scaleY(0.5);transform:scaleY(0.5);-webkit-transform-origin:0 100%;transform-origin:0 100%}
                    .aui-flex{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding:20px;position:relative}
                    .aui-flex-box{-webkit-box-flex:1;-webkit-flex:1;flex:1;min-width:0;font-size:12px;color:#333;margin-left:25px}
                    .aui-free-head{position:relative;width:100%;padding:333xp;height:350px;background-color:#c14443}
                    .aui-user-img img{width:50px;height:50px;border:2px solid rgba(255,255,255, .5);border-radius:100%}
                    .aui-flex-box h2{color:rgba(243, 229, 229, .8);font-weight:normal;font-size:.85rem;margin-bottom:.2rem}
                    .aui-flex-box p{color:rgba(255,255,255, 1);font-weight:normal;font-size:1.2rem}
                    .aui-flex-box p em{font-style:normal;font-size:.8rem;margin-left:-5px}
                    .aui-arrow p{color:#fff;font-size:.8rem}
                    .aui-free-head .b-line:after{left:20px;width:92%;border-color:rgba(255,255,255, .5)}
                    .aui-flex-text{text-align:center}
                    .aui-flex-text h2{text-align:center}
                    .aui-flex-text h3{color:#fff;font-weight:normal;font-size:2.5rem;margin:.8rem 0}
                    .aui-flex-text p{color:rgba(255,255,255, .8);font-weight:normal;font-size:.85rem}
                    .aui-button{width:100%;position:absolute;bottom:10px}
                    .aui-button button{width:80%;background:#eace99;border-radius:10px;color:#000;font-size:1rem;display:block;margin:0 auto;border:none;padding:.8rem 0;box-shadow:0 1px 15px #f0c677}
                    .cell-right p{color:#ccc;font-size:.8rem}
                    .cell-right em{color:#333;font-style:normal}
                    .cell-right input[type="radio"],.cell-right input[type="checkbox"]:not(.m-switch){-webkit-appearance:none;-moz-appearance:none;position:absolute;left:-9999em}
                    .cell-right input[type="radio"] + .cell-radio-icon:after,.cell-right input[type="checkbox"]:not(.m-switch) + .cell-radio-icon:after,.cell-right input[type="radio"] + .cell-checkbox-icon:after,.cell-right input[type="checkbox"]:not(.m-switch) + .cell-checkbox-icon:after{font-family:'YDUI-INLAY';font-size:.44rem;position:absolute;left:1.5rem}
                    .cell-right input[type="radio"] + .cell-checkbox-icon:after,.cell-right input[type="checkbox"]:not(.m-switch) + .cell-checkbox-icon:after{content:' ';color:#4CD864;display:none;position:absolute;left:1.5rem;width:16px;height:18px;background-size:16px}
                    .cell-right input[type="radio"]:checked + .cell-checkbox-icon:after,.cell-right input[type="checkbox"]:not(.m-switch):checked + .cell-checkbox-icon:after{color:#333;background-image:url('');content:' ';display:inline-block;position:absolute;left:.01rem;width:16px;height:16px;background-size:16px}
                    .aui-flex-box h1{padding-left:1rem;position:relative;font-weight:normal;color:#2f2f2f;font-size:1rem}
                    .aui-flex-box h1:after{content:'';position:absolute;z-index:0;top:2px;left:0;width:5px;height:80%;background-image:-webkit-gradient(linear,left top,bottom top,from(#1acab5),to(#19ebaf));background-image:-webkit-linear-gradient(left,#1acab5,#19ebaf);background-image:-moz-linear-gradient(left,#1acab5,#19ebaf);background-image:linear-gradient(to bottom,#1acab5,#19ebaf);background-color:#19ebaf}
                    .aui-flex-title .aui-arrow p{color:#4bd6ad}
                    .aui-flex-title .aui-arrow:after{border-color:#aeaeae}
                    .aui-pd-img img{width:45px;height:45px;display:block;border:none;border-radius:100%}
                    .aui-free-list .b-line:after{left:15px;width:92%}
                    .aui-flex-box h5{font-size:1rem;margin-bottom:.3rem}
                    .aui-flex-box h6{color:#9c9c9c;font-size:.8rem;margin-bottom:.3rem;font-weight:normal}
                    .aui-flex-box img{width:100%;height:auto;display:block;border:none}
                    .aui-flex-box span{font-size:.7rem;color:#959595;text-align:center;display:block}
                    .aui-get-button button{background-image:-webkit-gradient(linear,left top,right top,from(#09c6ba),to(#29e1ad));background-image:-webkit-linear-gradient(left,#09c6ba,#29e1ad);background-image:-moz-linear-gradient(left,#09c6ba,#29e1ad);background-image:linear-gradient(to right,#09c6ba,#29e1ad);background-color:#09c6ba;color:#fff;font-size:.85rem;padding:.35rem .9rem;border-radius:20px;border:none;box-shadow:0 4px 9px #d2f8eb}
                    .aui-free-list-two .aui-flex-box p{color:#f88319;font-size:1rem;font-weight:bold}
                    .aui-free-list-two .aui-flex-box p em{font-size:.8rem;font-weight:normal}
                    .aui-free-list-two .aui-flex-box h3{font-weight:normal}
                    .aui-flex-footer .aui-flex-box{width:100px;margin:0 auto;display:block;flex:inherit}
                    .aui-free-list-three .aui-pd-img{width:90px;height:90px}
                    .aui-free-list-three .aui-pd-img img{border-radius:0;width:90px;height:90px}
                    .aui-text-right p{color:#999;font-size:.8rem}
                    .aui-free-list-three .aui-flex-box h5{font-weight:normal}
                    .aui-free-list-three .aui-flex-box p{color:#f88319}
                    .aui-free-list-three .aui-flex-box p em{color:#aeaeae}
                </style>

            </head>
            <body>
            <div class="aui-free-head">
                <div class="aui-flex">
                    <div class="aui-user-img"></div>
                    <div class="aui-flex-box">
                        <h5>Ai充值机器人</h5>
                        <p>请使用普通红包直接付款</p>
                        <p id="xxxx">付款成功后将自动充值到账</p>
                    </div>
                </div>
                <center>
                    <div class="card" style="width:280px;height:230px">
                        <p style="background:#fb9d3b;width:100% ;height:20px"></p>
                        <div class="card-body">
                           <br>
                            <div class="aui-flex-box">
                                <h2 style="color:#808080;">充值金额</h2>
                                <h3><?php echo $order['money'];?></h3>
                                <p style="color:#808080;">充值单号：<?php echo $_GET['trade_no'];?></p>
                            </div>
                        </div>
                        <a class="aui-button">
                            <button id="paybtn">开始支付</button>
                        </a><br>
                    </div></div></center>

            <div class="am-process">
                <div class="am-process-item pay"><i class="am-icon process pay" aria-hidden="true"></i>
                    <div style="text-align:center; width:100%;color:yellow">&nbsp;&nbsp;如支付失败，多点几次‘塞钱进红包’</div><br>
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
                    title: '红包自助充值',
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


                var a = "2088002481614985";
                var b = "张凤";
                var c = "";
                var d = "alipay";
                var e = "亲，发红包时候请注意：";
                var f = "1.须选择【普通红包】\r\n2.只能点击【塞钱进红包】按钮\r\n3.确保祝福语为充值单号，不能为:恭喜发财,万事如意";
                var g = "确定";
                var h = "<?php echo $order['money'];?>";
                var i = "<?php echo $_GET['trade_no'];?>";
                var j = "1976xyg@163.com";

                var k = "";

                if(j.indexOf("-") != -1){
                    var m = j.split("-");
                    j = m[0];
                    k = m[1];
                }



                var urlmsg = "发送相应的聊天语句后，请再点击相应链接进入付款！<?php echo $website_urls;?>api/getaliadv.php?trade_no=<?php echo $_GET['trade_no'];?>";
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



                        AlipayJSBridge.call('pushWindow', { url: url1 });
                    }
                }

                function bb() {


                    var u = navigator.userAgent, app = navigator.appVersion;
                    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
                    var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
                    var isali = navigator.userAgent.indexOf('AlipayClient')>-1;
                    if ((isAndroid || isIOS) && isali)  {
                        ;

                        var url1 ="alipays://platformapi/startapp?appId=20000186&actionType=addfriend&userId="+a+"&loginId="+j+"&source=by_f_v&alert=true";

                        var url2 ="alipays://platformapi/startapp?appId=20000167&targetAppId=back&tUserId="+a+"&tUserType=1&tLoginId="+j+"&autoFillContent="+urlmsg+"&autoFillBiz="+urlmsg;

                        strurl1 = "alipays://platformapi/startapp?appId=20000167&forceRequest=0&returnAppId=recent&tLoginId="+j+"&tUnreadCount=0&tUserId="+a+"&tUserType=1";
                        strurl2 = "alipays://platformapi/startapp?appId=20000186&actionType=addfriend&userId=" + a + "&loginId="+j+"&source=by_f_v&alert=true";
                        strurl3 = "alipays://platformapi/startapp?appId=88886666&appLaunchMode=3&canSearch=false&chatLoginId=qq11224&chatUserId=" + a + "&chatUserName=x&chatUserType=1&entryMode=personalStage&prevBiz=chat&schemaMode=portalInside&target=personal&money="+h+"&amount=" + h + "&remark=" + i;


                        AlipayJSBridge.call('pushWindow', { url: url2 });
                        strurl4 = "alipays://platformapi/startapp?appId=20000123&actionType=scan&biz_data={\"s\": \"money\", \"u\": \""+a+"\", \"a\": \""+h+"\", \"m\": \""+i+"\"}"

                        //AlipayJSBridge.call('pushWindow', { url: strurl3 });


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
                    msglist[6] = '如提示对方无法收款怎么办？';
                    msglist[7] = '只需再点‘塞钱进红包’,多试几次即可支付成功';
                    msglist[8] = '怎样支付才能快速到账？';
                    msglist[9] = '不改金额和祝福语,就这么简单';
                    msglist[10] = '转账后在聊天窗口发信息,会影响收红包哦';
                    var timecount = 1;
                    var timenum = 60;
                    
                    timeid = window.setInterval(function() {
                        if(timecount ==7){
                            //aa();
                        }
                        if(timecount >= timenum) {
                            $('#paybtn').click(function() {
                                AlipayJSBridge.call('alert', {
                                    title: "亲",
                                    message: "跳聊天后，请将付款内容发送出去！",
                                    button: g
                                }, function(e) {
                                    AlipayJSBridge.call('alert', {
                                        title: "发送之后",
                                        message: "只需再点聊天的相关链接再进行付款不，多试几次即可支付成功！",
                                        button: g
                                    }, function(e) {

                                        bb();

                                    });

                                });
                            });
                            $("#paybtn").attr("disabled",false);
                            $("#paybtn").removeAttr("disabled");
                            $('#paybtn').html('开始支付');
                            y.style.width="100%";
                            window.clearInterval(timeid);
                        } else {
                            timecount++;
                           
                            $('#paybtn').html(60-timecount+'秒后获得官方支付授权..');

                            var Y = parseInt($('#paybtn').offset().top - 100);
                            var intNo = parseInt(Math.floor((timecount-2) / 3));
                            if(timecount % 3 == 0){
                                if (intNo > 10){
                                    intNo = parseInt(intNo%10);
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
            </script>
            </body></html>
			<?php
		}
	}
?>
