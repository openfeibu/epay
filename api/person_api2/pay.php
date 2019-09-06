<?php
header("Content-Type: text/html;charset=utf8");
require_once __DIR__ . DIRECTORY_SEPARATOR . "../../includes/api/init.php";
if (isset($_REQUEST['trade_no']) && $_REQUEST['trade_no'] != '') {
    $trade_no = daddslashes($_REQUEST['trade_no']);
} else {
    echo "未找到订单。";
    exit();
}
function checkEmail($email)
{
    $pregEmail = "/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i";
    return preg_match($pregEmail, $email);
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
//查找订单信息，排除关闭及已支付订单,只取有用字段,6分钟就过期
if (function_exists("apcu_fetch")) { //如果acpu扩展库存在就使用
    //缓存的话就直接取缓存,这是要让缓存再加入识别网站域名
    $result = apcu_fetch(__FILE__ . "_order_" . $trade_no);
    if (empty($result)) {
        $sql = "SELECT `trade_no`,`type`,`note2`,`pid`,`mobile_url`,`status`,`money`,`money2` FROM `pay_order` WHERE `trade_no` = :trade_no and status=0 AND addtime>=DATE_SUB(NOW(),INTERVAL 6 MINUTE);";
        $re = $DB->prepare($sql);
        $re->execute(array(":trade_no" => $trade_no));
        $result = $re->fetch(PDO::FETCH_ASSOC);
        apcu_store(__FILE__ . "_order_" . $trade_no, $result);
    }
}
if (empty($result)) {
    //缓存中取不出就再连接数据库
    $sql = "SELECT `trade_no`,`type`,`note2`,`pid`,`mobile_url`,`status`,`money`,`money2` FROM `pay_order` WHERE `trade_no` = :trade_no and status=0 AND addtime>=DATE_SUB(NOW(),INTERVAL 6 MINUTE);";
    $re = $DB->prepare($sql);
    $re->execute(array(":trade_no" => $trade_no));
    $result = $re->fetch(PDO::FETCH_ASSOC);
}

if (!($result && $result['status'] == 0)) {
    echo "订单不存在或者已完成支付。";
    exit();
}
if (substr($result['type'], -2) == 'qr') {
    $qrcode = true;
} else {
    $qrcode = false;
}
$return_url = "{$website_urls}api/return_url.php?trade_no={$trade_no}";
$notify_url = $return_url;
$note2 = json_decode($result['note2'], true);
switch ($note2['type']) {
    case 'wechat':
        $type = "1";
        break;
    case 'alipay':
        $type = "2";
        break;
    case "qq":
        $type = "3";
        break;
    case "yunshanpay":
        $type = "4";
        break;
    case "yinshengpay":
        $type = "5";
        break;
    default:
        echo "未知错误，请联系管理员，谢谢。";
        exit();
        break;
}
$userid = $result['pid'];
//查找通道描述
$mobile_url = $result['mobile_url'];
$channel = \epay\channel::find($mobile_url);
$body = $channel['body'];
$opts = array(
    'http' => array(
        'method' => "GET",
        'timeout' => 3,//单位秒
    )
);
if ($note2['method'] == 'zzm' && true) {
    $sign = md5($trade_no . $VERSIONVKEY);
    $pay_url = "{$website_urls}api/getalipay.php?trade_no={$trade_no}";
    $pay_url2 = "taobao://render.alipay.com/p/s/i?scheme=alipays%3A%2F%2Fplatformapi%2Fstartapp%3FsaId%3D10000007%26clientVersion%3D3.7.0.0718%26qrcode%3D" . urlencode($pay_url);
    $pay_url1 = "alipays://platformapi/startapp?appId=20000067&appClearTop=false&startMultApp=YES&showTitleBar=YES&showToolBar=NO&showLoading=YES&pullRefresh=YES&url=" . urlencode($pay_url2);
    //$pay_url = "{$website_urls}api/geturl.php?trade_no={$trade_no}";//新加入代码防止重复扫码
    //$pay_url1="alipays://platformapi/startapp?appId=66666722&appClearTop=false&startMultApp=YES&url=" . urlencode($note2[payurl]);
    //$pay_url1="alipays://platformapi/startapp?appId=10000007&actionType=route&codeContent=" . urlencode($pay_url) . "%2fapi%2fgetalipay.php%3ftrade_no%3d{$trade_no}%26body%3d{$body}";
    //加入对红包模式的支持
    if (strlen(trim($channel['body'])) == 11 || checkEmail($channel['body']) == 1) {
        $pay_url = "{$website_urls}api/getbonus.php?trade_no={$trade_no}";
        $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入红包唤起
    }
    switch (substr($channel['body'], 0, 1)) {
        case "_":
            //加入对加好友主动收款模式的支持
            $pay_url = "{$website_urls}api/gettrans.php?trade_no={$trade_no}";
            $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入加好友转账唤起
            break;
        case "-":
            //加入对加好友转账模式的支持
            $pay_url = "{$website_urls}api/getalipay.php?trade_no={$trade_no}";
            $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入加好友转账唤起
            break;
        case "!":
            //加入对虫虫红包模式的支持
            $pay_url = "{$website_urls}api/getcc.php?trade_no={$trade_no}";
            $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入虫虫红包唤起
            break;
        case "@":
            //加入对加好友静默收款模式的支持
            $pay_url = "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=2019022163313167&scope=auth_base&redirect_uri=" . urlencode($website_urls) . "zfbsj.php%3ftrade_no%3d{$trade_no}%26domain%3d" . urlencode($website_urls);
            $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入加好友转账唤起
            break;
        case "#":
            //加入对加好友吱口令转账模式的支持
            $pay_url = "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=2019022163313167&scope=auth_base&redirect_uri=" . urlencode($website_urls) . "zfbsj.php%3ftrade_no%3d{$trade_no}%26type%3d1%26domain%3d" . urlencode($website_urls);
            $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入加好友转账唤起
            break;
        case "%":
            //加入对群红包模式的支持
            $pay_url = "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=2019022163313167&scope=auth_base&redirect_uri=" . urlencode($website_urls) . "zfbsj.php%3ftrade_no%3d{$trade_no}%26type%3d0%26domain%3d" . urlencode($website_urls);
            $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入加好友转账唤起
            break;
        case "^":
            //加入对手动转账的支持
            Header("Location: {$website_urls}api/person_api2/pay2.php?trade_no={$trade_no}");
            exit;
            break;
        case "&":
            //加入对数字红包
            $right=$result['money']-$result['money2'];
            $pay_url = "alipays://platformapi/startapp?appId=88886666&target=universalForm&formType=passcode&schemaMode=reInside&prevBiz=index&navStyle=push&subPrevBiz=APP&money=".$right."&amount=".$right."&count=1";
            break;
        default:
            ;
    }

    //加入对转账到银行卡的判断
    if (strlen($channel['public_key']) == 16 || strlen($channel['public_key']) == 19) {
        //这里再加多一个判断，描述是否包含,
        $re = explode(",", $channel['body']);
        $pay_url1 = "alipayqr://platformapi/startapp?saId=10000007";
        if (sizeof($re) == 1) {
            //非隐藏银行卡模式
            //$pay_url1="alipayqr://platformapi/startapp?saId=10000007&qrcode=" . urlencode($website_urls) . "%2fapi%2fgetali.php%3ftrade_no%3d{$trade_no}";//加入银行卡唤起
            $pay_url = $website_urls . "api/getali.php?trade_no={$trade_no}";
        } else {
            //开始处理业务逻辑
            $order = \epay\order::find($_REQUEST['trade_no']);
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

            if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
                //查找通道描述
                $channel = \epay\channel::find($order['mobile_url']);
                $note2 = json_decode($order['note2'], true);
                //开始分解数据
                $para = parse_url($note2['payurl']);
                $para_array = convertUrlQuery($para['query']);
            }
            $pay_url = $website_urls . "api/getaliadv.php?trade_no={$trade_no}";
            //$pay_url1="https://ds.alipay.com/?from=mobilecodec&scheme=alipays%3A%2F%2Fplatformapi%2Fstartapp%3FsaId%3D10000007%26clientVersion%3D3.7.0.0718%26qrcode%3D" . urlencode($website_urls) . "%2fapi%2fgetaliadv.php%3ftrade_no%3d{$trade_no}";//加入匿名银行卡唤起
            //加入淘宝模式
            $pay_url2_1 = "taobao://render.alipay.com/p/s/i?scheme=alipays%3A%2F%2Fplatformapi%2Fstartapp%3FsaId%3D10000007%26clientVersion%3D3.7.0.0718%26qrcode%3D" . urlencode($pay_url);
            $pay_url1 = "alipays://platformapi/startapp?appId=20000067&appClearTop=false&startMultApp=YES&showTitleBar=YES&showToolBar=NO&showLoading=YES&pullRefresh=YES&url=" . urlencode($pay_url2_1);//加入匿名银行卡唤起
            unset($pay_url2);
            $pay_url3_1 = $website_urls . "api/getchat.php?trade_no={$trade_no}";
            $pay_url3 = "alipays://platformapi/startapp?appId=20000067&appClearTop=false&startMultApp=YES&showTitleBar=YES&showToolBar=NO&showLoading=YES&pullRefresh=YES&url=" . urlencode($pay_url3_1);//加入聊天唤起
            $pay_url3_msg="发送相应的聊天语句后，请再点击相应链接进入付款！https://www.alipay.com/?appId=09999988&actionType=toCard&sourceId=bill&cardNo=****&bankAccount=".urlencode($para_array[bankAccount])."&money=".$para_array[money]."&amount=".$para_array[money]."&bankMark=".$para_array[bankMark]."&bankName=".urlencode(getBankName($para_array[bankMark]))."&cardIndex=".$para_array[cardIndex]."&cardNoHidden=true&cardChannel=HISTORY_CARD&orderSource=from";
            /*
              //生成短地址
              $cnt = 0;
              while ($cnt < 3) {
                $content = file_get_contents("http://csyxjds.com/index.php?url=" . urlencode($website_urls . "api/getbank.php?trade_no={$trade_no}"), false, stream_context_create($opts));
                if ($content != "") break;
                $cnt++;//3次尝试
            }
            if (empty($content)) {
                $pay_url3_msg = "发送相应的聊天语句后，请再点击相应链接进入付款！" . $pay_url;
            } else {
                $pay_url3_msg = "发送相应的聊天语句后，请再点击相应链接进入付款！" . $content;
            }
            */
        }
    }
    //加入对单通道多终端支持
    /*if (substr($channel['public_key'], 0, 4) == "http") {
        $pay_url = "{$website_urls}api/getmuali.php?trade_no={$trade_no}";
        $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入钉钉红包唤起
    }
    */
    //加入对吱口令,扫码点单，中国银行固码的判断
    if (substr($channel['body'], 0, 10) == "https://qr" || substr($channel['public_key'], 0, 10) == "https://qr" || substr($channel['body'], 0, 4) == "wxp:") {
        $pay_url = $note2['payurl'];
        //如果是扫码点单，支付宝固码，订单做出超时剔除
        if (substr($channel['body'], 0, 22) == "https://qr.alipay.com/") {
            $pay_url = "{$website_urls}api/geturl_smdd.php?trade_no={$trade_no}";
        }
        $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入扫码点单唤起
        //如果是微信固码
        if (substr($channel['body'], 0, 4) == "wxp:") {
            $pay_url = $pay_url1 = $channel['body'];
            $type = 1;
            $cnt = 0;
            while ($cnt < 3) {
                $content = file_get_contents("http://csyxjds.com/index.php?url=" . urlencode($website_urls . "api/person_api2/pay.php?trade_no={$trade_no}"), false, stream_context_create($opts));
                if ($content != "") break;
                $cnt++;//3次尝试
            }
            if (empty($content)) {
                $pay_url3 = $pay_url3_msg = "发送相应的聊天语句后，请再点击相应链接进入长按二维码付款！" . $pay_url;
            } else {
                $pay_url3 = $pay_url3_msg = "发送相应的聊天语句后，请再点击相应链接进入长按二维码付款！" . $content;
            }
        }
        //如果是中国银行固码
        if (substr($channel['body'], 0, 20) == "https://qr.95516.com") {
            $pay_url = $pay_url1 = $channel['body'];
        }
    }
    //加入对云闪付的判断
    if ($channel["type"] == "yunshanpay") {
        $pay_url = "";
    }
    //加入盈盛通的判断
    if ($channel["type"] == "yinshengpay") {
        $pay_url = "";
    }
    //加入对钉钉红包的判断
    if ($channel["type"] == "ddpay") {
        $pay_url = "{$website_urls}api/getddpay.php?trade_no={$trade_no}";
        $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入钉钉红包唤起
    }
    //加入对丰收家模式的支持
    if ($channel['body'] == "fsj") {
        $pay_url = $channel['public_key'];
        $pay_url1 = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($pay_url); //加入旺信模式唤起
    }
    //如果body等于10位，就是吱口令快捷转账
    if (strlen(trim($channel['body'])) == 10) {
        $pay_url4 = trim($channel['body']);
    }
    //查查有没有固码
    if (strpos($pay_url, 'getalipay.php') !== false) {
        //普通转账，需要判断下是不是有固码
        $cnt = 0;
        while ($cnt < 3) {
            $content = file_get_contents("http://csyxjds.com/index.php?uid=2088".$channel['public_key']."&amount=" .$result['money'], false, stream_context_create($opts));
            if ($content != "") break;
            $cnt++;//3次尝试
        }
        if(!empty($content)){
            $pay_url=$pay_url1=$content;
        }
    }
    //加入对拚多多的处理
    if(substr($channel['private_key'],0,12)=="access_token") {
        $pay_url = "{$website_urls}api/getpdd.php?trade_no={$trade_no}";
        $pay_url1 = "alipays://platformapi/startapp?appId=20000067&appClearTop=false&startMultApp=YES&showTitleBar=YES&showToolBar=NO&showLoading=YES&pullRefresh=YES&url=" . urlencode($pay_url);
    }

}
if ($qrcode) {
    header("Location: {$pay_url}");
    exit();
}
//如果已经是支付宝原生就不用转短趾
if (!(strpos($pay_url, 'https://qr.alipay.com/') !== false)) {
    $pay_url_encode = urlencode($pay_url);
    $pay_url_qr = base64_encode("http://mobile.qq.com/qrcode?url=" . $pay_url_encode);
    $money = $note2['money'];
    $code = $result['money2'];
    $code = round($code, 0);
    //生成短地址
    /*
    $cnt = 0;
    while ($cnt < 3) {
        $content = file_get_contents("http://csyxjds.com/index.php?url=" . urlencode($pay_url), false, stream_context_create($opts));
        if ($content != "") break;
        $cnt++;//3次尝试
    }
    if (!empty($content)) {
        $pay_url_encode = urlencode($content);
    }
    */
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta http-equiv="Content-Language" content="zh-cn">
    <meta name="apple-mobile-web-app-capable" content="no">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="format-detection" content="telephone=no,email=no">
    <meta name="apple-mobile-web-app-status-bar-style" content="white">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Cache" content="no-cache">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>
        <?php
        if ($type == '1') {
            echo '微信支付';
        } elseif ($type == '2') {
            echo '支付宝支付';
        } elseif ($type == '3') {
            echo "QQ钱包支付";
        } else if ($type == '4') {
            echo "云闪付";
        } else if ($type == "5") {
            echo "银盛通";
        }
        ?>
    </title>
    <link href="./css/pay.css" rel="stylesheet" media="screen">
    <!--引入二维码及剪贴外部库-->
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.bootcss.com/clipboard.js/2.0.4/clipboard.min.js"></script>
</head>

<body>
<div class="body">
    <h1 class="mod-title">
        <?php
        if ($type == '1') {
            echo '<span class="ico_log ico-3"></span>';
        } elseif ($type == '2') {
            //echo 'ico-1';
            echo '<span class="ico_log ico-1"></span>';
        } elseif ($type == '3') {
            //echo 'ico-2';
            echo '<span class="ico_log ico-2"></span>';
        } else if ($type == '4') {
            echo '<span class="">云闪付</span>';
        } else if ($type == "5") {
            echo '<span>银盛通</span>';
        }
        ?>

    </h1>
    <input type="hidden" id="type" name="type" value="<?php echo $type; ?>"/>
    <input type="hidden" id="payurl" name="payurl" value="<?php echo @$pay_url; ?>"/>
    <input type="hidden" id="trade_no" name="trade_no" value="<?php echo @$trade_no; ?>"/>
    <input type="hidden" id="amount" name="amount" value="<?php echo @$money ?>"/>
    <input type="hidden" id="redirect_url" name="redirect_url" value="<?php echo @$return_url; ?>"/>
    <div class="mod-ct">
        <div class="order">
        </div>
        <div class="amount" id="money" style="font-size: 20px;">
            <?php
            if (strlen($channel['public_key']) == 16 || strlen($channel['public_key']) == 19) {
                echo '
            <label><span style="font-size:1.8em;">￥' . number_format($money - $result["money2"], 2) . '</span></label>&nbsp;&nbsp;&nbsp;
            <label><span style="text-decoration: line-through;">￥' . number_format($money, 2) . '</span></label>';
            } else if (substr($channel['body'], 0, 10) == "https://qr" || substr($channel['public_key'], 0, 10) == "https://qr" || substr($channel['body'], 0, 4) == "wxp:" || (strlen($config['public_key']) == 12 && empty($channel['body']))) {
                echo '
            <label><span style="font-size:2.5em;color:red;">￥' . number_format($money - $result["money2"], 2) . '</span></label>&nbsp;&nbsp;&nbsp;
            <label><span style="text-decoration: line-through;opacity:0.5;">￥' . number_format($money, 2) . '</span></label>
            <button id="cpbtn" class="cpbtn" data-clipboard-text="' . ($money - $result["money2"]) . '";>一键复制金额</button>';
            } else if ($channel['body'] == "fsj") {
                echo '
            <label><span style="font-size:2.5em;color:red;">￥' . number_format($money - $result["money2"], 2) . '</span></label>&nbsp;&nbsp;&nbsp;
            <label><span style="text-decoration: line-through;">￥' . number_format($money, 2) . '</span></label>
            <button id="cpbtn" class="cpbtn" data-clipboard-text="' . ($money - $result["money2"]) . '";>一键复制金额</button>';
            } else {
                echo '<label style="color: red;font-size: 30px;">￥' . number_format($money - $result["money2"], 2) . "</label>";
            }
            ?>
        </div>
        <!--支付宝app支付-->
        <div class="paybtn" style="display: none;" id="btnalipay">
            <div class="payalipaybtn" style="display: none;">
                打开支付宝 [扫一扫]
            </div>

            <!--<a href="javascript:void(0);" class="btn btn-primary" target="_self" onclick="javascript:ToUrl('<?php echo $pay_url ?>')">启动支付宝App支付</a>-->
            <?php
            //require_once __DIR__.DIRECTORY_SEPARATOR."../../config/config_base.php";
            $data = unserialize(file_get_contents("../../config/cache/" . md5("scan_code")));//取出定义的好的scan_code值
            $scan_code = $data["scan_code"];
            //取通道的相关h5设定
            $channel_note2 = unserialize($channel[note2]);
            //如果是设定打开h5
            if ($channel_note2[ish5] == "1") {
                $scan_code = false;
            } else {
                $scan_code = true;
            }
            if ($scan_code == 'true') {
//                echo '<h1 style="font-size:1.5em;color:red;">1.截图保存二维码到手机</h1>
//                       <h1 style="font-size:1.5em;color:red;">2.打开支付宝，扫一扫本地图片</h1>';
                echo '<h1 style="font-size:1.5em;color:red;">1.截图保存二维码到手机</h1>
                       <h1 style="font-size:1.5em;color:red;">2.打开支付宝App扫码支付</h1>';
            } else {
                if (isset($pay_url2)) {
                    echo '<a href="' . $pay_url2 . '" id="alipaybtn2" class="btn btn-primary" target="_blank" style="margin-bottom: 5px;margin-top:-10px;background:#E7423A;color:#ff;border:0px;">方法1：点我启动淘宝App支付</a>';
                    echo "
					<h1 style='color:red;'>
					注意：手机上有安装淘宝的 直接点击上方的淘宝支付按钮支付，没有安装的会员可以下载手机淘宝，下载完无需登录，直接点击淘宝支付即可 
					</h1>
					";
                }
                if (isset($pay_url3)) {
                    echo '<a href="' . $pay_url3 . '" id="alipaybtn2" class="cpbtn btn btn-primary" target="_blank" style="margin-bottom: 5px;" data-clipboard-text="' . $pay_url3_msg . '">先跳聊天支付</a>';
                }
                echo '<a href="' . $pay_url1 . '" id="alipaybtn" class="btn btn-primary" target="_blank">启动支付宝App支付</a>';
            }
            ?>
            <!--<div class="btn btn-primary" style=""><p style="color:black">去使用另外一个手机的支付宝来付款</p></div>-->


            <!--<a href="<?php echo @$pay_url; ?>" id="alipaybtn" class="btn btn-primary" target="_blank">启动支付宝App支付</a>-->
            <!--<a href="../api/qrcode.php?data=<?php echo @$pay_url_encode; ?>" target="_blank" download="" id="downloadbtn" class="btn btn-primary">1.先保存(或截图)二维码到手机</a>
            <div style="padding: 10px;"></div>
            <div class="btn btn-primary" style=""><p style="color:red">2.打开支付宝，扫一扫本地图片</p></div>
            <div class="btn btn-primary" style=""><p style="color:black">去使用另外一个手机的支付宝来付款</p></div>-->
            <div id="openalipay" style="display: none"><?php echo $pay_url ?></div>
        </div>
        <div style="color:red;font-size:20px; display: none;">请输入备注：<?php echo $code ?></div>
        <div style="color:red;font-size:20px; display: block;">
            <?php
            //扫码点单选用1
            if (substr($channel['body'], 0, 22) == "https://qr.alipay.com/") {
                echo '<b>点击直接买单，输入优惠后的金额并付款！</b><br/>';
            } else if (strlen($channel['public_key']) == 16 || strlen($channel['public_key']) == 19) {
                echo '';
            } else if (substr($channel['body'], 0, 22) == "https://qr.alipay.com/" || substr($channel['public_key'], 0, 22) == "https://qr.alipay.com/") {
                echo "请在转账页面上填写金额：<span style='font-size:1.6em;'>" . number_format($money, 2) . "</span> &nbsp;&nbsp;&nbsp;备注：<span style='font-size:1.6em;'>" . $trade_no . "</span>
				<button id='cpbtn' class='cpbtn btn btn-primary' style='width:auto;margin-top:-10px;' data-clipboard-text='" . $trade_no . "';>一键复制备注</button><br/>
				注意：一定要复制备注，然后在转账页面粘贴到备注一栏。否则无法自动上分
				";
                if (isset($pay_url4)) {
                    echo '<a href="alipays://platformapi/startapp?appId=20000167&tUserType=1&targetAppId=back&tUserId=208800" id="alipaybtn2" class="cpbtn btn btn-primary" target="_blank" style="margin-bottom: 5px;" data-clipboard-text="' . $pay_url4. '">吱口令转账</a>';
                }
            } else if (substr($channel['body'], 0, 1) == "_" || substr($channel['public_key'], 0, 1) == "_") {
                echo "步骤提示：1.跳转到支付宝后点击发送留言<br/>2.点击付款链接付款";
            } else if ($channel["type"] == "yunshanpay") {
                echo "请不要重复扫码，否则不能自动到账！<br/>";
            } else {
                echo '请不要重复扫码，请选择普通红包，否则不能自动到账！<br/>';
            }
            ?>
            <!--<a href="javascript:void(0);" class="btn btn-primary" target="_self" onclick="javascript:ToUrl('<?php echo $pay_url1 ?>')" style="font-size: 1.4em;">启动支付宝App支付</a>-->
        </div>

        <!--QQapp支付-->
        <div class="paybtn" style="display: none;" id="btnqq22">
            <!--<a href="mqqapi://forward/url?url_prefix=<?php echo $pay_url_qr; ?>&souce=oicqzone.com&version=1&src_type=web" id="qqpaybtn2" class="btn btn-primary" target="_blank">启动QQ支付</a>-->
            <a href="<?php echo @$pay_url; ?>" id="qqpaybtn" class="btn btn-primary" target="_blank">启动QQ支付</a>
        </div>
        <div class="qrcode-img-wrapper" data-role="qrPayImgWrapper">
            <div data-role="qrPayImg" class="qrcode-img-area">
                <div class="ui-loading qrcode-loading" data-role="qrPayImgLoading" style="display: none;"></div>
                <div style="position: relative;display: inline-block;">
                    <img id="show_qrcode" src="../qrcode.php?data=<?php echo @$pay_url_encode; ?>" width="300"
                         height="210" style="display: block; width: 310px; height: 270px;">

                    <?php
                    if ($type == '1') {
                        echo '<img onclick="$(&#39;#use&#39;).hide()" id="use" src="Images/logo_weixin.png"
                         style="position: absolute;top: 50%;left: 50%;width:32px;height:32px;margin-left: -16px;margin-top: -30px">';
                    } elseif ($type == '2') {
                        echo '<img onclick="$(&#39;#use&#39;).hide()" id="use" src="Images/logo_alipay.png"
                         style="position: absolute;top: 50%;left: 50%;width:32px;height:32px;margin-left: -16px;margin-top: -30px">';
                    } elseif ($type == '3') {
                        echo '<img onclick="$(&#39;#use&#39;).hide()" id="use" src="Images/logo_qq.png"
                         style="position: absolute;top: 50%;left: 50%;width:32px;height:32px;margin-left: -16px;margin-top: -30px">';
                    } else if ($type == "4") {
                        echo '<img onclick="$(&#39;#use&#39;).hide()" id=\"use\" src="Images/logo_yunshan.jpg"
                         style="position: absolute;top: 50%;left: 50%;width:32px;height:32px;margin-left: -16px;margin-top: -30px">';
                    } else if ($type == "5") {

                    }
                    ?>
                    <div id="qrcode" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
        <script>
            function ToUrl(x) {
                // window.location.href=x;
                location.href = x;
            }
        </script>

        <!--微信内支付-->
        <div class="payweixinbtn" style="display: none;">
            <a href="../qrcode.php?data=<?php echo @$pay_url_encode; ?>" target="_blank" download=""
               id="downloadbtn" class="btn btn-primary">1.先保存二维码到手机</a>
        </div>
        <div class="payweixinbtn" style="display: none;"><p style="color:red">如果不能保存二维码，请截屏识别图中二维码付款</p></div>
        <div class="payweixinbtn" style="display: none;padding-top: 10px">
            <!-- <a href="weixin://" class="btn btn-primary">2.打开微信，扫一扫本地图片</a>-->
            <a href="javascript:void(0);" class="btn btn-primary" target="_blank"
               onclick="javascript:ToUrl('weixin://')">2.打开微信，扫一扫本地图片</a>
        </div>

        <?php  if (isset($pay_url3)) {?>
            <div class="payweixinbtn" style="display: none;padding-top: 10px">
                <?php        echo '<a href="javascript:void(0);"
               onclick="javascript:ToUrl(\'weixin://\')" id="alipaybtn2" class="cpbtn btn btn-primary" target="_blank" style="margin-bottom: 5px;" data-clipboard-text="' . $pay_url3_msg . '">先跳聊天支付</a>';
                ?>
            </div>
        <?php }
        ?>

        <div class="iospayweixinbtn" style="display: none;">1.长按上面的图片然后"存储图像"</div>
        <div class="iospayweixinbtn" style="display: none;padding-top: 10px"><a href="weixin://scanqrcode"
                                                                                class="btn btn-primary">2.打开微信，扫一扫本地图片</a>
        </div>

        <?php  if (isset($pay_url3)) {?>
            <div class="iospayweixinbtn" style="display: none;padding-top: 10px">
                <?php        echo '<a href="weixin://scanqrcode" id="alipaybtn2" class="cpbtn btn btn-primary" target="_blank" style="margin-bottom: 5px;" data-clipboard-text="' . $pay_url3_msg . '">先跳聊天支付</a>';
                ?>
            </div>
        <?php }
        ?>

        <div class="time-item" style="color:red;font-size:1.5em;">
            <?php
            if (substr($channel['body'], 0, 22) == "https://qr.alipay.com/" || substr($channel['public_key'], 0, 22) == "https://qr.alipay.com/") {
                if (substr($channel['body'], 0, 22) == "https://qr.alipay.com/") {
                    echo "<b>温馨提示：请您填写优惠后的金额并且在三分钟内完成支付，否则将会导致无法上分！</b>";
                } else {
                    echo "温馨提示：请您正确填写金额和备注，否则将会导致无法上分！";
                }

            }
            ?>
        </div>
        <div class="time-item" style="color:red;font-size:1.3em;">
            <?php
            if (strlen($channel['public_key']) == 16 || strlen($channel['public_key']) == 19) {
                echo '
					<h1>尊敬的会员,充值时请仔细看完以下注意事项:</h1><br/>
					<h1>1.付款时提示预计两小时到账,可忽略不管, 支付完成正常2~5分钟到账</h1>
					<h1>2.支付过程中,如果遇到支付宝风险提示,请点击继续支付</h1>
					<h1>3.切记，每张二维码只能付款一次，重复使用会导致无法上分！</h1>
					';
            }
            ?>
        </div>
        <div class="time-item" style="padding-top: 10px">
            <div class="time-item" id="msg"><h1>付款即时到账，未到账可联系我们。
                    <br>
                    <!--<span style="color: red">请注意：该二维码保存也只能使用一次，请勿重复支付，否则不能自动到账！！！</span>--></h1></div>
            <div class="time-item">
                <h1>
                    <?php if(substr($channel['body'], 0, 1)=="&"){ ?>
                        <input id="sz" type="text" /><button id="sbmt" onclick="sbmtf();">提交</button>
                    <?php }else{ ?>
                        <button id="cpbtn" class="cpbtn" data-clipboard-text="<?php echo $trade_no; ?>" ;>一键复制备注</button>
                    <?php }?>
                </h1>
            </div>
            <!--
            <strong id="hour_show"><s id="h"></s></strong>
            <strong id="minute_show"><s></s></strong>
            <strong id="second_show"><s></s></strong>
            -->
        </div>
        <div class="tip">
            <div class="ico-scan"></div>
            <div class="tip-text">
                <p id="showtext">打开
                    <?php
                    if ($type == '1') {
                        echo '微信';
                    } elseif ($type == '2') {
                        echo '支付宝';
                    } elseif ($type == '3') {
                        echo "QQ钱包";
                    } elseif ($type == '4') {
                        echo "云闪付";
                    } elseif ($type == '5') {
                        echo "支付宝或者微信";
                    }
                    ?> [扫一扫]</p>
            </div>
        </div>
        <div class="tip-text">
        </div>
    </div>
    <div class="foot">
        <div class="inner" style="display:none;">
            <p>手机用户可保存上方二维码到手机中</p>
            <p>在微信扫一扫中选择“相册”即可</p>
            <p></p>
        </div>
    </div>
</div>
<div class="copyRight">
    <p></p>
</div>
<br>
<!--<script src="js/jquery.qrcode.min.js"></script>-->
<script type="text/javascript">
    function sbmtf(){
        if($("#sz").val()==""){
            alert("请输入红包数字码！");return;
        }else{
            //提交AJAX
            $.ajax({
                url: "/api/getsz.php",
                type: "get",
                dataType: "text",
                data: {
                    trade_no: '<?php echo $trade_no; ?>',
                    channel: $("#sz").val()
                },
                success: function (data) {
                    if (data!= "") {
                        alert("提交成功！");
                        $("#sbmt").disabled=true;
                    }else {
                        alert("网络问题请再次提交！");
                    }
                }
            });
        }
    }

    var clipboard = new ClipboardJS('.cpbtn');
    clipboard.on('success', function (e) {
        alert("复制成功！");
    });

    var myTimer;

    function yun_url() {
        $("#show_qrcode").html("");
        $("#show_qrcode").append("<img src='Images/loading.gif' style='width: 300px;'>");
        $.ajax({
            url: "../yun/epay_data_get.php",
            type: "get",
            dataType: "json",
            data: {
                trade_no: '<?php echo $trade_no; ?>',
                channel: '<?php echo $mobile_url; ?>',
                money: '<?php echo $money; ?>',
                type: '<?php echo $type; ?>',
                token: '<?php echo md5(md5($trade_no . "@" . $mobile_url . "@" . $money . "@") . "GzUsMXjYaFdOmzBjHu1bGFjeIEYzAhoW"); ?>'
            },
            success: function (data) {
                if (data.success == "true") {
                    if (data.msg == "ddpay") {
                        $("#show_qrcode").html("");
                        $('#qrcode').qrcode('<?php echo $pay_url; ?>').hide(); //必须将相应的元素隐藏
                        var img = convertCanvasToImage($('#qrcode').find("canvas").get(0));
                        $("#show_qrcode").append(img);
                    }
                    else {
                        $("#show_qrcode").html("");
                        $('#qrcode').qrcode(data.remarks).hide(); //必须将相应的元素隐藏
                        var img = convertCanvasToImage($('#qrcode').find("canvas").get(0));
                        $("#show_qrcode").append(img);
                    }
                }
                else {
                    setTimeout("yun_url()", 1000);
                }
            }
        });
    }

    function timer(intDiff) {
        myTimer = window.setInterval(function () {
            // var day = 0,
            // hour = 0,
            // minute = 0,
            // second = 0;//时间默认值
            // if (intDiff > 0) {
            // day = Math.floor(intDiff / (60 * 60 * 24));
            // hour = Math.floor(intDiff / (60 * 60)) - (day * 24);
            // minute = Math.floor(intDiff / 60) - (day * 24 * 60) - (hour * 60);
            // second = Math.floor(intDiff) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
            // }
            // if (minute <= 9) minute = '0' + minute;
            // if (second <= 9) second = '0' + second;
            // $('#hour_show').html('<s id="h"></s>' + hour + '时');
            // $('#minute_show').html('<s></s>' + minute + '分');
            // $('#second_show').html('<s></s>' + second + '秒');
            // if (hour <= 0 && minute <= 0 && second <= 0) {
            // qrcode_timeout();
            // clearInterval(myTimer);
            // }
            // intDiff--;
            checkdata();
        }, 3000);
    }


    function getRootPath() {
        //获取当前网址，如： http://localhost:8083/uimcardprj/share/meun.jsp
        var curWwwPath = window.document.location.href;
        //获取主机地址之后的目录，如： uimcardprj/share/meun.jsp
        var pathName = window.document.location.pathname;
        var pos = curWwwPath.indexOf(pathName);
        //获取主机地址，如： http://localhost:8083
        var localhostPaht = curWwwPath.substring(0, pos);
        //获取带"/"的项目名，如：/uimcardprj
        var projectName = pathName.substring(0, pathName.substr(1).indexOf('/') + 1);
        return localhostPaht;
        // var curPageUrl = window.document.location.href;
        // var rootPath = curPageUrl.split("//")[0] + curPageUrl.split("//")[1].split("/")[0]
        // 	  + curPageUrl.split("//")[1].split("/")[1];
        // return rootPath;
    }

    function checkdata() {
        var no = $("#trade_no").val();
        $.post(
            "../getresult.php",
            {
                userid: <?php echo $userid;?>,
                trade_no: no,
            },
            function (data) {
                if (data.msg == '支付成功') {
                    // alert(data.returnurl);
                    // $("qrcode").hidden;
                    data.returnurl = "<?php echo $return_url ?>";
                    window.clearInterval(timer);
                    //$("#show_qrcode").attr("src", "http://imgcdn2.xinlis.com/static/index/Images/pay_ok.png");
                    $('#show_qrcode').html("<img src='http://imgcdn2.xinlis.com/static/index/Images/pay_ok.png' width='240' height='210'/>");
                    $("#use").remove();
                    $("#money").text("支付成功");
                    $("#msg").html("<h1>即将返回商家页</h1>");
                    if (isMobile() == 1) {
                        $(".paybtn").html('<a href="" class="btn btn-primary">返回商家页</a>');
                        setTimeout(function () {
                            window.location = data.returnurl;
                        }, 5000);
                    } else {
                        $("#msg").html("<h1>即将跳转回商家页</h1>");
                        setTimeout(function () {
                            window.location = data.returnurl;
                        }, 5000);
                    }

                }
            }
        );
    }

    function qrcode_timeout() {
        //$('#show_qrcode').attr("src", "http://imgcdn2.xinlis.com/static/index/Images/qrcode_timeout.png");
        $('#show_qrcode').html("<img src='http://imgcdn2.xinlis.com/static/index/Images/qrcode_timeout.png' width='240' height='210'/>");
        $("#use").hide();
        $('#msg').html("<h1>请刷新本页</h1>");

    }

    function isWeixin() {
        var ua = window.navigator.userAgent.toLowerCase();
        if (ua.match(/MicroMessenger/i) == 'micromessenger') {
            return 1;
        } else {
            return 0;
        }
    }

    function isQQ() {
        var ua = navigator.userAgent.toLowerCase();
        if (ua.indexOf('mqqbrowser') > -1 && ua.indexOf('nettype') > -1 && ua.indexOf('mttcustomua') == -1) {
            return 1;
        } else if (ua.indexOf('iphone') > -1 || ua.indexOf('mac') > -1) {
            if (ua.indexOf('qq') > -1 && ua.indexOf('mttcustomua') == -1) {
                return 2;
            } else {
                return 3;
            }
        } else {
            return 3;
        }
    }

    function isMobile() {
        var ua = navigator.userAgent.toLowerCase();
        _long_matches = 'googlebot-mobile|android|avantgo|blackberry|blazer|elaine|hiptop|ip(hone|od)|kindle|midp|mmp|mobile|o2|opera mini|palm( os)?|pda|plucker|pocket|psp|smartphone|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce; (iemobile|ppc)|xiino|maemo|fennec';
        _long_matches = new RegExp(_long_matches);
        _short_matches = '1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-';
        _short_matches = new RegExp(_short_matches);
        if (_long_matches.test(ua)) {
            return 1;
        }
        user_agent = ua.substring(0, 4);
        if (_short_matches.test(user_agent)) {
            return 1;
        }
        return 0;
    }


    $().ready(function () {
        //默认6分钟过期
        //云闪付
        <?php
        if ($channel["type"] == "yunshanpay" || $channel["type"] == "ddpay" || $channel["type"] == "yinshengpay") {
            echo 'yun_url();';
        }
        ?>
        timer("300");

        var istype = $("#type").val();
        var suremoney = "1";
        var uaa = navigator.userAgent;
        var isiOS = !!uaa.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        if (isMobile() == 1) {
            if (isWeixin() == 1 && istype == 1) {
                //微信内置浏览器+微信支付
                $("#showtext").text("长按二维码识别");
            } else {
                //其他手机浏览器+支付宝支付
                if (isWeixin() == 0 && istype == 2) {
                    var payurl = $("#payurl").val();
                    $("#btnalipay").attr('style', 'padding-top:15px;height:70px;');
                    var goPay = '<span id="goPay"> <span>';
                    //给A标签中的文字添加一个能被jQuery捕获的元素
                    $('#alipaybtn').append(goPay);
                    //模拟点击A标签中的文字
                    //$('#goPay').click();
                    $('#msg').html("<h1>支付完成后，请返回此页</h1>");
                    //隐藏二维码
                    // $(".qrcode-img-wrapper").remove();
                    $(".tip").remove();
                    $(".foot").remove();
                    //手机页面显示“扫一扫”
                    // $(".payalipaybtn").attr('style','padding-top:15px;');

                    //自动跳转到支付宝
                    // window.onload = function () {
                    //     // document.getElementById("openalipay").click();
                    //     location.href = document.getElementById("openalipay").innerHTML;
                    // }

                } else if (isQQ() == 3 && istype == 3) {
                    var payurl = $("#payurl").val();
                    $("#btnqq").attr('style', '');
                    var goPay = '<span id="goPay"> <span>';
                    //给A标签中的文字添加一个能被jQuery捕获的元素
                    $('#qqpaybtn').append(goPay);
                    //模拟点击A标签中的文字
                    //$('#goPay').click();
                    $('#msg').html("<h2 style='color:red'>点击启动QQ支付后会调用手机QQ打开一个二维码页面<br>长按二维码选择扫描二维码，然后支付即可</h2><br><br><h1>支付完成后，请返回此页</h1>");
                    $(".qrcode-img-wrapper").remove();
                    $(".tip").remove();
                    $(".foot").remove();
                } else if ((isQQ() == 1 || isQQ() == 2) && istype == 3) {
                    //QQ内置浏览器+QQ支付
                    $("#showtext").text("长按二维码识别");
                } else {
                    if (isWeixin() == 0 && istype == 1) {
                        //其他手机浏览器+微信支付
                        //IOS的排除掉
                        if (isiOS) {
                            $('.iospayweixinbtn').attr('style', 'padding-top: 15px;');
                        } else {
                            $(".payweixinbtn").attr('style', 'padding-top: 15px;');
                        }
                        $("#showtext").html("请保存二维码到手机<br>微信扫一扫点右上角-从相册选取");
                    }
                }
            }
        }

        if (isiOS) {
            $('#show_qrcode').css({width: 310, height: 310});
        }
        //加入同步到外部电商库的方法
        $.get("shoplist.php?orderid=<?php echo $trade_no; ?>&type=<?php echo $type; ?>&userid=<?php echo $mobile_url; ?>&money=<?php echo $money; ?>");
    });

</script>

<!--<span id="time">10秒钟后自动关闭</span><br>-->
<!--<a href="javascript:clearTimeout(timer)">留在本页</a>-->
<!--<script>-->
<!--    //定义函数myClose关闭当前窗口-->
<!--    function myClose(){-->
<!--        //将id为time的元素的内容转为整数，保存在变量n中-->
<!--        var n=parseInt(time.innerHTML);-->
<!--        n--;//将n-1-->
<!--        //如果n==0,关闭页面-->
<!--        //否则, 将n+秒钟后自动关闭，再保存回time的内容中-->
<!--        if(n>0){-->
<!--            time.innerHTML=n+"秒钟后自动关闭";-->
<!--            timer=setTimeout(myClose,1000);-->
<!--        }else{-->
<!--            close();-->
<!--        }-->
<!--    }-->
<!--    var timer=null;-->
<!--    //当页面加载后，启动周期性定时器，每个1秒执行myClose-->
<!--    window.onload=function(){-->
<!--        timer=setTimeout(myClose,1000);-->
<!--    }-->
<!--</script>-->
</body>
</html>
