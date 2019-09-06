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
    function http_post_data($url, $data_string, $header="") {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        if(empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data_string))
            );
        }else{
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    $header,
                    'Content-Length: ' . strlen($data_string))
            );
        }
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        //echo $return_content."<br>";
        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //  return array($return_code, $return_content);
        return  $return_content;
    }

    //开始处理业务逻辑
    $order = \epay\order::find($_REQUEST['trade_no']);
    if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
        if(!empty($order['buyer'])){
            echo "<script>alert('请不要重复扫码或付款。');</script>";
            exit();
        }
        //查找通道描述
        $channel    = \epay\channel::find($order['mobile_url']);
        $priv=convertUrlQuery($channel['private_key']);
        $pubic=convertUrlQuery($channel['public_key']);
        //1.拼多多登陆
        $data=$cache->get('access_token_'.$channel['id']);
        //if (empty($cache) || empty($data)){
        $postjosn='{"app_id":12,"access_token":"'.$priv['access_token'].'","open_id":"'.$priv['open_id'].'"}';
        $re=http_post_data("https://api.yangkeduo.com/login",$postjosn);
        $data=json_decode($re,true);
        //将接受到的结果保存到缓存
        //$cache->put('access_token_'.$channel['id'], $data);
        //}
        //2.拼多多选择商品店铺和分组购买
        $url="https://api.yangkeduo.com/api/morgan/confirm/render?pdduid=".$data['uid'];
        //算出要购买的数量
        $number=intval($order['money']/100);
        $postjosn='{"award_type":0,"biz_type":0,"front_env":3,"front_version":5,"goods_id":"'.$pubic['goods_id'].'","goods_number":'.$number.',"group_id":"'.$pubic['group_id'].'","is_app":1,"last_pay_app_id":"8","page_from":"0","refresh":false,"sku_id":"'.$pubic['sku_id'].'","type":0}';
        $re=http_post_data($url,$postjosn,"AccessToken:".$data['access_token']);
        $result=json_decode($re,true);
        $address_id=$result[address_vo][address_id];

        //保存相应订单信息
        $create_order_token=$result['extend_map']['create_order_token'];
        $uuid=$result['extend_map']['PTRACER-TRACE-UUID'];
        //3.然后提交订单获取支付的链接
        $url="https://api.yangkeduo.com/order?pdduid=".$data['uid'];
        if(empty($address_id)){
            $postjosn='{"goods":[{"sku_id":'.$pubic['sku_id'].',"sku_number":'.$number.',"goods_id":"'.$pubic['goods_id'].'"}],"group_id":"'.$pubic['group_id'].'","anti_content":"","page_from":0,"duoduo_type":0,"biz_type":0,"attribute_fields":{"create_order_token":"'.$create_order_token.'","order_amount":'.$order['money'].',"original_front_env":0,"PTRACER-TRACE-UUID":"'.$uuid.'"},"source_channel":"0","source_type":0,"pay_app_id":6,"is_app":"1","version":1,"page_id":""}';
            $re=http_post_data($url,$postjosn,"AccessToken:".$data['access_token']);
        }else{
            $postjosn='{"address_id":'.$address_id.',"goods":[{"sku_id":'.$pubic['sku_id'].',"sku_number":'.$number.',"goods_id":"'.$pubic['goods_id'].'"}],"group_id":"'.$pubic['group_id'].'","anti_content":"","page_from":0,"duoduo_type":0,"biz_type":0,"attribute_fields":{"create_order_token":"'.$create_order_token.'","order_amount":'.$order['money'].',"original_front_env":0,"PTRACER-TRACE-UUID":"'.$uuid.'"},"source_channel":"0","source_type":0,"pay_app_id":6,"is_app":"1","version":1,"page_id":""}';
            $re=http_post_data($url,$postjosn,"AccessToken:".$data['access_token']);
        }
        $re=http_post_data($url,$postjosn,"AccessToken:".$data['access_token']);
        $result=json_decode($re,true);
        $order_sn=$result[order_sn];
        $sql = "update `pay_order` set `buyer` ='{$order_sn}' where `trade_no` = '".$_REQUEST['trade_no']."'";
        $DB->query($sql);
        //4.然后提交订单获取支付的链接
        $url="https://api.pinduoduo.com/order/prepay?pdduid=".$data['uid'];
        $postjosn='{"order_sn":"'.$order_sn.'","version":3,"attribute_fields":{"paid_times":0,"forbid_contractcode":"1","forbid_pappay":"1"},"return_url":"","app_id":6}';
        $re=http_post_data($url,$postjosn,"AccessToken:".$data['access_token']);
        $result=json_decode($re,true);
        $order_info=$result[order_info];
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
                    //if(timecount ==60){
                    y.style.width="100%";
                    window.clearInterval(timeid);
                    aa();
                    //}
                    timecount++;
                    y.style.width=40+timecount+"%";
                }, 1000);
                function aa(){
                    var orderStr = ('<?php echo $order_info;?>')
                    ap.tradePay({
                        orderStr:orderStr
                    }, function(result){
                        if(result.resultCode==9000||result.resultCode=="9000"){
                            location.href="/api/getpddresult.php?trade_no=<?php echo $_REQUEST['trade_no'];?>&pdd_no=<?php echo $order_sn;?>";
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