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
        //取延时时间,不设定默认为7
        $wait_time=isset($info[wait_time])?$info[wait_time]:7;
        //计算加好友时间
        $add_friend_time=$wait_time-2>0?$wait_time-2:0;
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
            <title>群红包</title>
            <script src="//cdn.staticfile.org/jquery/3.3.1/jquery.min.js"></script>
            <script src="./js/clipboard.min.js"></script>
            <style type="text/css">
                *{margin:0px;padding:0px;}
                .a{width:85%;margin:0 auto;text-align: center;font-size:2.8em;margin-top:50px;}
            </style>
        </head>

        <body>
        <div class="a">
            <h2>支付教程</h2><br>
            <div style="position: fixed;width:80%;height:160px;top:85%;background: #fff;line-height: 180px;left:10%;font-size:0.8em;">
                <input type="button" value="请稍候……" id="paybtn" style="width:98%;height:150px;line-height: 150px;font-size:2em;border-radius:20px;color:#fff;background:#128EE8;" data-clipboard-text="<?php echo $order[money];?>";>
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
                    if(timecount ==<?php echo $add_friend_time;?>){
                        aa();
                    }
                    if(timecount ==<?php echo $wait_time;?>){
                        window.clearInterval(timeid);
                        $('#paybtn').click(function() {
                            bb();
                            return;
                        });
                    }
                    timecount++;
                    left=<?php echo $wait_time;?>-timecount+1;
                    if(left>0){
                        $('#paybtn').val("倒计时秒数："+left);
                    }else{
                        bb();
                    }
                }, 1700);
                function aa(){
                    strurl2 = 'alipays://platformapi/startapp?appId=20000186&actionType=addfriend&appClearTop=false&source=by_home&userId=<?php echo $para_array[userId];?>&loginId=<?php echo substr($channel[body],"1");?>';
                    AlipayJSBridge.call('pushWindow', { url: strurl2 });
                }
                function bb(){
                    //延时
                    strurl2 = "<?php echo $channel[subject];?>/qun?userid=<?php echo $_GET[uid];?>,<?php echo $_GET[trade_no];?>,<?php echo number_format($order[money],2);?>";
                    AlipayJSBridge.call('pushWindow', { url: strurl2 });
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