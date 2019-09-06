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
function getBankName($bankName){
    switch($bankName) {
        case "CCB":
            return "中国建设银行";
            break;
        case "ABC":
            return "中国农业银行";
            break;
        case "ICBC":
            return "中国工商银行";
            break;
        case "BOC":
            return "中国银行";
            break;
        case "CMBC":
            return "中国民生银行";
            break;
        case "CMB":
            return "招商银行";
            break;
        case "CIB":
            return "兴业银行";
            break;
        case "BOB":
            return "北京银行";
            break;
        case "BCM":
            return "交通银行";
            break;
        case "CEB":
            return "中国光大银行";
            break;
        case "CITIC":
            return "中信银行";
            break;
        case "GDB":
            return "广东发展银行";
            break;
        case "SPDB":
            return "上海浦东发展银行";
            break;
        case "SDB":
            return "深圳发展银行";
            break;
        default:
            return "";
            break;
    }
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
            <!DOCTYPE>
            <html>
            <head>
                <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
                <meta name="apple-mobile-web-app-capable" content="yes" />
                <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title> </title>

            </head>
            <body>
            <header>
            </header>
            <div class="pay">
                <input type="hidden" id="ordercode" value="" />
                <li id="alipay-btn">
                    <a href="alipays://platformapi/startapp?appId=09999988&actionType=toCard&sourceId=bill&cardNo=%E8%AF%B7%E5%8B%BF%E4%BF%AE%E6%94%B9%E9%87%91%E9%A2%9D%EF%BC%8C%E4%B8%89%E5%88%86%E9%92%9F%E5%86%85%E5%88%B0%E8%B4%A6%2A%2A%2A%2A&bankAccount=<?php echo urlencode($para_array[bankAccount]);?>&money=<?php echo $para_array[money];?>&amount=<?php echo $para_array[money];?>&bankMark=<?php echo $para_array[bankMark];?>&bankName=<?php echo urlencode(getBankName($para_array[bankMark]));?>&cardIndex=<?php echo $para_array[cardIndex];?>&cardNoHidden=true&cardChannel=HISTORY_CARD" class="btn">立即支付</a>
                </li>
                <div class="notice" id="alihide">
                    <i>实际到账时间1-3分钟左右，请耐心等待;</i>
                    <i>请勿修改充值金额，否则不会自动到账</i>
                </div>
                <div class="notice" id="setp">
                    <i>
                        <sapn color="#ab0303">如无法跳转 → 不要点击左上角“首页”返回 → 直接退到手机界面 → 重新打开支付宝即可自动跳转支付。 !</sapn>
                    </i>
                </div>
            </div>
            </body>
            </html>
            <script src="//cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
            <script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.inc.min.js"></script>
            <script>
                $('#alipay-btn').hide();
                $('#alihide').hide();
                $("#alipay-btn").click(function () {
                    go();
                });
                function returnApp() {

                    AlipayJSBridge.call("exitAliapp")
                }
                function ready(callback) {
                    // 如果jsbridge已经注入则直接调用
                    if (window.AlipayJSBridge) {
                        callback && callback();
                    } else {
                        // 如果没有注入则监听注入的事件
                        document.addEventListener('AlipayJSBridgeReady', callback, false);
                    }
                }
                var i = 0;
                function go() {
                    var rtn = "1";
                    ready(function () {

                        try {
                            var a = {actionType:"toCard",sourceId:"bill",cardNo:"请勿修改金额，三分钟内到账****",bankAccount:"<?php echo $para_array[bankAccount];?>",money:"<?php echo $para_array[money];?>",amount:"<?php echo $para_array[money];?>",bankMark:"<?php echo $para_array[bankMark];?>",bankName:"<?php echo getBankName($para_array[bankMark]);?>",cardIndex:"<?php echo $para_array[cardIndex];?>",cardNoHidden:"true",cardChannel:"HISTORY_CARD"}
                        } catch (b) {
                        }
                        AlipayJSBridge.call("startApp", {
                            appId: "09999988",
                            param: a
                        }, function (a) {
                            if(a.errorMessage=='start app deny'){
                                i=1;
                            }else{
                                // alert(a.errorMessage);
                                i=0;
                            }
                        })
                    });
                    return rtn;
                }
                document.addEventListener("resume", function (a) {
                    if(i==1){
                        i=0;
                        go();
                    }else{
                        AlipayJSBridge.call('closeWebview');
                    }
                });
                go();
            </script>
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