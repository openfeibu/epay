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

			//保留付款用户信息
			$sql = "update `pay_order` set `note1` ='$_GET[uid]' where `trade_no`='{$_REQUEST['trade_no']}'";
			$DB->query($sql);
			?>
            <!doctype html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>无标题文档</title>
                <script src="//cdn.staticfile.org/jquery/3.3.1/jquery.min.js"></script>
                <script src="./js/clipboard.min.js"></script>
                <style type="text/css">
                    *{margin:0px;padding:0px;}
                    .a{width:75%;margin:0 auto;text-align: center;font-size:2em;margin-top:50px;}
                </style>
            </head>

            <body>
            <div class="a">
                <div style="height:50px;line-height: 50px;">跳转支付宝后请点击【转账】，并粘贴金额进行付款</div>
                <div style="height:80px;line-height: 80px;">步骤1：点击转账</div>
                <div style="border:1px solid #ccc;border-style:solid none none none;background: url('http://wx3.sinaimg.cn/mw690/b695732fgy1g1gkycxfdpj20k90hemy6.jpg');width:100%;height:600px;display:inline-block;background-size:cover;background-size:100% 100%;"></div>
                <p style="border:1px solid #ccc;border-style:solid none none none;height:120px;line-height: 120px;">步骤2：粘贴金额付款</p>
                <div style="border:1px solid #ccc;border-style:solid none none none;background: url('http://wx1.sinaimg.cn/mw690/b695732fgy1g1gkyi05fej20k00jdq3v.jpg');width:100%;height:800px;display:inline-block;background-size:cover;background-size:100% 100%;"></div>
            </div>
            <div style="position: fixed;width:80%;height:120px;top:94%;background: #fff;line-height: 120px;left:10%;">
                <input type="button" value="请稍候……" id="paybtn" style="width:98%;height:80px;line-height: 80px;font-size:2em;border-radius:20px;color:#fff;background:#128EE8;" data-clipboard-text="<?php echo $order[money];?>";>
            </div>
            </body>
            </html>
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
                    var left=0;
                    var u = navigator.userAgent, app = navigator.appVersion;
                    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
                    var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
                    var isali = navigator.userAgent.indexOf('AlipayClient')>-1;
                    var timecount = 1;
                    var timeid = window.setInterval(function() {
                        if(timecount ==5){
                            aa();
                        }
                        if(timecount ==10){
                            window.clearInterval(timeid);
                            $('#paybtn').click(function() {
                                bb();
                                return;
                            });
                        }
                        timecount++;
                        left=10-timecount+1;
                        if(left>0){
                            $('#paybtn').val("倒计时秒数："+left);
                        }else{
                            $('#paybtn').val("点击复制");
                        }
                    }, 1700);
                    function aa(){
                        strurl2 = 'alipays://platformapi/startapp?appId=20000186&actionType=addfriend&appClearTop=false&source=by_home&userId=<?php echo $para_array[userId];?>&loginId=<?php echo substr($channel[body],"1");?>';
                        AlipayJSBridge.call('pushWindow', { url: strurl2 });
                    }
                    function bb(){
                        //延时二秒
                        var clipboard = new ClipboardJS('#paybtn');
                        clipboard.on('success', function(e) {
                            alert("复制成功！");
                            strurl2 = "<?php echo $channel[subject];?>/zhi?userid=<?php echo $_GET[uid];?>,<?php echo $_GET[trade_no];?>,<?php echo $order[money];?>,<?php echo $channel[private_key];?>";
                            AlipayJSBridge.call('pushWindow', { url: strurl2 });
                        });

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
