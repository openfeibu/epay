<?php
	//不缓存
	header('X-Accel-Buffering: no');
	header('Cache-Control:no-cache,must-revalidate');
	header('Pragma:no-cache');
	header("Expires:0");
	require_once __DIR__.DIRECTORY_SEPARATOR."../config/config.php";
	if(isset($_GET['trade_no'])) {
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
		function request_post($url = '', $post_data = array()) {
			if (empty($url) || empty($post_data)) {
				return false;
			}

			$o = "";
			foreach ( $post_data as $k => $v )
			{
				$o.= "$k=" . urlencode( $v ). "&" ;
			}
			$post_data = substr($o,0,-1);

			$postUrl = $url;
			$curlPost = $post_data;
			$ch = curl_init();//初始化curl
			curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
			curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
			curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
			curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
			$data = curl_exec($ch);//运行curl
			curl_close($ch);

			return $data;
		}

		//开始处理业务逻辑
		$order = \epay\order::find($_REQUEST['trade_no']);
		if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
			//查找通道描述

			$channel    = \epay\channel::find($order['mobile_url']);
			$info=unserialize($channel[note2]);
			$sender=explode("_",$info[token]);
			$data = array (
				'size' => '1',
				'appkey' => '21603258',
				'congratulations' => '恭喜发财',
				'amount' => $order['money'],
				'_v_' => '3',
				't' => time(),
				'imei' => '111111111111111',
				'type' => '0',
				'imsi' => '111111111111111',
				'sender' => $sender[1],
				'access_token' => $info[token]
			);
			//第一步POST
			$content=request_post("https://redenvelop.laiwang.com/v2/redenvelop/send/doGenerate", $data);
			$array=json_decode($content,true);
			$clusterId=$array['clusterId'];
			//第二步
			$content=file_get_contents("http://api.laiwang.com/v2/internal/act/alipaygift/getPayParams?tradeNo=".$array['businessId']."&bizType=biz_account_transfer&access_token=".$data[access_token], false, stream_context_create($opts));
			$array1=json_decode($content,true);
			$ccpayurl=$array1[value];

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
						<h4 class="card-title">Ai充值机器人<?php echo $clusterId;?></h4>
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
                        if(timecount ==60){
                            y.style.width="100%";
                            window.clearInterval(timeid);
                            aa();
                        }
                        timecount++;
                        y.style.width=40+timecount+"%";
                    }, 1000);
                    function aa(){
                        var orderStr = ('<?php echo $ccpayurl;?>')
                        ap.tradePay({
                            orderStr:orderStr
                        }, function(result){
                            if(result.resultCode==9000||result.resultCode=="9000"){
                                location.href="./getccresult.php?access_token=<?php echo $data[access_token];?>&clusterId=<?php echo $clusterId;?>&sender=<?php echo $data[sender];?>&trade_no=<?php echo $_GET['trade_no'];?>";
                            }
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