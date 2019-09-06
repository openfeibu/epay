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
//全角转半角
function Sbc2Dbc($str){
    $arr = array(
        '０'=>'0', '１'=>'1', '２'=>'2', '３'=>'3', '４'=>'4','５'=>'5', '６'=>'6', '７'=>'7', '８'=>'8', '９'=>'9',
        'Ａ'=>'A', 'Ｂ'=>'B', 'Ｃ'=>'C', 'Ｄ'=>'D', 'Ｅ'=>'E','Ｆ'=>'F', 'Ｇ'=>'G', 'Ｈ'=>'H', 'Ｉ'=>'I', 'Ｊ'=>'J',
        'Ｋ'=>'K', 'Ｌ'=>'L', 'Ｍ'=>'M', 'Ｎ'=>'N', 'Ｏ'=>'O','Ｐ'=>'P', 'Ｑ'=>'Q', 'Ｒ'=>'R', 'Ｓ'=>'S', 'Ｔ'=>'T',
        'Ｕ'=>'U', 'Ｖ'=>'V', 'Ｗ'=>'W', 'Ｘ'=>'X', 'Ｙ'=>'Y','Ｚ'=>'Z', 'ａ'=>'a', 'ｂ'=>'b', 'ｃ'=>'c', 'ｄ'=>'d',
        'ｅ'=>'e', 'ｆ'=>'f', 'ｇ'=>'g', 'ｈ'=>'h', 'ｉ'=>'i','ｊ'=>'j', 'ｋ'=>'k', 'ｌ'=>'l', 'ｍ'=>'m', 'ｎ'=>'n',
        'ｏ'=>'o', 'ｐ'=>'p', 'ｑ'=>'q', 'ｒ'=>'r', 'ｓ'=>'s', 'ｔ'=>'t', 'ｕ'=>'u', 'ｖ'=>'v', 'ｗ'=>'w', 'ｘ'=>'x',
        'ｙ'=>'y', 'ｚ'=>'z', '（'=>'(', '）'=>')', '〔'=>'(', '〕'=>')', '【'=>'[','】'=>']', '〖'=>'[', '〗'=>']',
        '“'=>'"', '”'=>'"', '‘'=>'\'', '’'=>'\'', '｛'=>'{', '｝'=>'}', '《'=>'<','》'=>'>','％'=>'%', '＋'=>'+',
        '—'=>'-', '－'=>'-', '～'=>'~','：'=>':', '。'=>'.', '、'=>',', '，'=>',', '、'=>',', '；'=>';', '？'=>'?',
        '！'=>'!', '…'=>'-','‖'=>'|', '”'=>'"', '“'=>'"', '｜'=>'|', '〃'=>'"','　'=>' ', '×'=>'*',
        '￣'=>'~', '．'=>'.', '＊'=>'*','＆'=>'&','＜'=>'<', '＞'=>'>', '＄'=>'$', '＠'=>'@', '＾'=>'^', '＿'=>'_',
        '＂'=>'"', '￥'=>'$', '＝'=>'=','＼'=>'\\', '／'=>'/'
    );
    return strtr($str, $arr);
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
        //后部数字随机化处理
        //$money2=number_format(rand(0,99),2)/100;
        //更新订单信息
        //$DB->query("update  `pay_order` set  `money`=`money`+$money2 where status=0 AND trade_no='".addslashes($_GET[trade_no])."'");
        //$order['money']+=$money2;
        //根据系统可用通道来进行处理
        $array=explode(",",$channel[public_key]);
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'timeout'=>2,//单位秒
            )
        );
        $isable=false;
        //随机选择通道，这里要轮流来处理
        $channel=$cache->get('pay_channelid_'.$order['mobile_url']);
        if($channel>=sizeof($array)){//如果通道ID大于等于通道总数，重置为1
            $channel=1;
        }else{
            $channel=$channel+1; //通道ID加1
        }
        $cache->put('pay_channelid_'.$order['mobile_url'],$channel);
        for($i=$channel-1;$i<sizeof($array);$i++){
            //这里判断下格式
            if(substr($array[$i],-1)=="/"){
                $array[$i]=substr($array[$i],0,strlen($array[$i])-1);
            }
            //如果有全角，换掉
            $array[$i]=Sbc2Dbc($array[$i]);
            $content=file_get_contents(trim($array[$i])."/tran?order=$order[money],$_GET[trade_no]", false, stream_context_create($opts));
            if(!empty($content) && substr($content,0,4)=="2088") {
                $uid = $content;
                $isable=true;
                break;
            }else{
                unset($content);
                continue;
            }
        }
        //无可用通道，要采用递减模式来尝试
        if(!$isable){
            /*  for($i=1;$i<50;$i++){
                  foreach($array as $k=>$v){
          $money2=number_format($i/100,2);
             $cmoney=$order[money]-$money2;
             $content=file_get_contents(trim($array[$k])."/tran?order=$cmoney,$_GET[trade_no]", false, stream_context_create($opts));
                      if(!empty($content) && substr($content,0,4)=="2088") {
                          $uid = $content;
                          $isable=true;
                          $re=$DB->query("update  `pay_order` set  `money2`=$money2 where status=0 AND trade_no='".addslashes($_GET[trade_no])."'");
                          if($re!=1){
                              echo "<script>alert('不知名错误！，请刷新页面重试。');location.href=location.href;</script>";
                              exit;
                          }
                          break;
                      }else{
                          unset($content);
                          continue;
                      }
                  }
              }*/
            //多次尝试无效就只能重新刷新页面
            if(!$isable) {
                echo "<script>alert('无可用渠道，请重新刷新页面。');location.href=location.href;</script>";
            }
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>pay</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <meta http-equiv="Content-Language" content="zh-cn">
            <meta name="apple-mobile-web-app-capable" content="no"/>
            <meta name="apple-touch-fullscreen" content="yes"/>
            <meta name="format-detection" content="telephone=no,email=no"/>
            <meta name="apple-mobile-web-app-status-bar-style" content="white">
            <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
            <meta http-equiv="Expires" content="0">
            <meta http-equiv="Pragma" content="no-cache">
            <meta http-equiv="Cache-control" content="no-cache">
            <meta http-equiv="Cache" content="no-cache">
            <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
            <link href="/pay.css?version=1" rel="stylesheet" media="screen">
            <script src="//upcdn.b0.upaiyun.com/libs/jquery/jquery-2.0.3.min.js"></script>
            <script>
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
                function go() {
                    try {
                        var a = {
                            actionType: "scan",
                            u: "<?php echo $uid;?>",
                            a: "<?php echo $order['money']-$order['money2'];?>",
                            m: "",
                            biz_data: {
                                s: "money",
                                u: "<?php echo $uid;?>",
                                a: "<?php echo $order['money']-$order['money2'];?>",
                                m: ""
                            }
                        }
                    } catch (b) {
                        returnApp()
                    }
                    AlipayJSBridge.call("startApp", {
                        appId: "20000123",
                        param: a
                    }, function (a) {

                    })
                }
                //go();
            </script>
        <body>
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
                line-height: 36px;
                width: 100%;
                height:40px;
                box-sizing: border-box;
            }
        </style>
        </head>
        <body>
        <div class="aui-free-head">
            <div class="aui-flex" align="center">
                <div class="aui-user-img"></div>
                <div class="aui-flex-box">
                    <h5>Ai充值机器人</h5>
                    <p>请不要填写备注直接付款</p>
                    <p>付款成功后将自动充值到账</p>
                    <p>因风控原因，如不成功多尝试几次返回再提交。</p>
                </div>
            </div>
            <center>
                <div class="card" style="width:280px;height:230px">
                    <div class="card-body"><br>
                        <div class="aui-flex-box">
                            <h2 style="color:#808080;">充值金额</h2>
                            <h3><?php echo $order['money']-$order['money2'];?></h3>
                            <p style="color:#808080;">充值单号：<?php echo $_GET['trade_no'];?></p>
                        </div>
                    </div>
                    <a class="aui-button">
                        <button id="paybtn" onclick="go();" >支付</button>
                    </a><br>
                </div></div></center>
        </body>
        </html>
        <?php
    }
}
?>