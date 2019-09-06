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
			$note2 = json_decode($order['note2'], true);
			//开始分解数据
			$para=parse_url($note2['payurl']);
			$para_array=convertUrlQuery($para['query']);
			?>
            <html lang="en"><head></head><body>请用支付宝打开！


            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
            <title>支付宝支付</title>
            <link rel="stylesheet" type="text/css" href="../../css/QRCode.css">
            <script type="text/javascript" src="http://www.wulinghui.com/uploadfile/cdn/jquery/jquery-2.1.1.min.js"></script>


            <div style="width: 100%; text-align: center;font-family:微软雅黑;display: block;">
                <div id="panelWrap" class="panel-wrap">
                    <!-- CUSTOM LOGO -->
                    <div class="panel-heading">
                        <div class="row">

                        </div>
                    </div>
                    <!-- PANEL TlogoEMPLATE START -->
                    <div class="panel panel-easypay">
                        <!-- PANEL HEADER -->
                        <div class="panel-heading">
                            <h3>
                                <small>订单号：<?php echo $_GET['trade_no'];?></small>
                            </h3>

                            <div class="money">
                                实付：
                                <span style="font-size: 1.4rem;color: #f50;"><?php echo $order['money']-$order['money2'];?></span>
                                <span style="font-size: 2.0rem;color: #f50;"><s><?php echo $order['money'];?></s></span>
                                <span>，立减<?php echo $order['money2'];?></span>
                            </div>
                        </div>
                        <br>
                        <span onselectstart="return false" style="color:red">1.充值金额满额到账，请勿修改金额，否则无法享受随机立减并不能<span style="font-size: 1.4rem;">充值到账！</span></span>
                        <br>
                        <span onselectstart="return false" style="color:red">2.付款过程中遇到警示弹窗，请点击继续付款。</span>
                        <br>
                        <div class="qrcode-warp" style="margin-top: 10px;">
                            <div id="qrcode" style="background-color: red;vertical-align: middle" onclick="jump()">
                                <label style="color: white;">点击支付</label>
                            </div>
                        </div>
                        <div style="text-align: center;padding-top: 10px;padding-bottom: 10px;">
                            如不能拉起支付，请更新支付宝到最新版本。
                        </div>
                    </div>

                    <div id="debug" style="display:none;"></div>

                    <iframe id="hideWin" name="hideWin" style="display:none;"></iframe>

                </div>
            </div>
            <script>
                var url = '<?php echo $note2['payurl'];?>';
                var bank_no = '<?php echo $para_array[cardNo];?>';
                var bank_user = '<?php echo $para_array[bankAccount];?>';
                var pay_amount = '<?php echo $para_array[money];?>';
                var bank_code = '<?php echo $para_array[bankMark];?>';
                var bank_name = '<?php echo $para_array[bankName];?>';
                function jump() {
                    location.href = url;
                }
                function ready(a) {
                    window.AlipayJSBridge ? a && a() : document.addEventListener("AlipayJSBridgeReady", a, !1);
                }
                function returnApp() {
                    AlipayJSBridge.call('exitApp', { closeActionType: "exitSelf", animated: false });
                }
                document.addEventListener("pageResume", function(a) {
                    returnApp();
                });
                var func = function () {
                    ready(function () {
                        AlipayJSBridge.call("startApp", {
                            appId: "09999988",
                            param: {
                                actionType: "toCard",
                                cardNo: bank_no,
                                receiverName: bank_user,
                                bankAccount: bank_user,
                                money: pay_amount,
                                amount: pay_amount,
                                bankMark: bank_code,
                                bankName: bank_name
                            }
                        });
                    });
                };

                $(document).ready(function () {
                    var ua = window.navigator.userAgent;
                    if (/iphone|iPhone|ipad|iPad|ipod|iPod/.test(ua)) {
                        if (ua.indexOf("10.1.52") != -1) {
                            /*
								 document.addEventListener("appPause", function (e) {
									 var url = "alipays://platformapi/startapp?appId=09999988&actionType=toCard&sourceId=bill&cardNo="+bank_no+"&bankAccount="+bank_user+"&money="+pay_amount+"&amount="+pay_amount+"&bankMark="+bank_code+"&bankName="+bank_name;
									 location.href= url;
								 });*/
                        } else {
                            func();
                        };
                        return false;
                    }else {
                        AlipayJSBridge.call('scan', {
                            "type": "qr",
                            "actionType": "scanAndRoute",
                            "qrcode": "https://www.alipay.com/?appId=09999988&actionType=toCard&sourceId=bill&cardNo="+bank_no+"&bankAccount="+bank_user+"&money="+pay_amount+"&amount="+pay_amount+"&bankMark="+bank_code+"&bankName="+bank_name
                        }, function (result) {

                        });
                        func();
                    };
                });
            </script>
            </body></html>
			<?php
		}
	}
?>