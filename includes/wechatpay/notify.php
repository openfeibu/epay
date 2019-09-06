<?php
require_once __DIR__."/../../includes/common.php";

#获取网关配置信息
$appid = "";
$mchid = "";
$security_code = "";

#获取到的微信返回内容
$wx_return = array();
//$postStr = $GLOBALS["HTTP_RAW_POST_DATA"]; windows的PHP有的不支持此项，使用以下代替。
$postStr = file_get_contents("php://input");
$wx_return = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

# Get Returned Variables
$return_code    = $wx_return['return_code'];    //获取微信返回代码
if($return_code == "SUCCESS"){
    $return_appid = $wx_return['appid'];
    $return_mch_id = $wx_return['mch_id'];
    $result_code    = $wx_return['result_code'];    //获取微信返回信号
    $invoiceid = $wx_return['attach']; //获取微信传递过来的账单号
    $transid   = $wx_return['transaction_id'];     //获取微信传递过来的微信支付订单号,即交易号
    $amount    = $wx_return['total_fee'];       //获取微信传递过来的总价格，微信使用分作单位，换算成元。本例无需换算。
    $fee       = 0;
}else{
    $return_msg = $wx_return['return_msg'];    //获取微信返回消息，可选
}

function getSign($data, $key) {
    $String = formatParameters($data, false);
    //签名步骤二：在string后加入KEY
    $String = $String . "&key=" . $key;
    //echo "<br>parm:".$String."<br>"; 此为测试使用，请勿打开，否则将会泄露您的数据。
    //签名步骤三：MD5加密
    $String = md5($String);
    //签名步骤四：所有字符转为大写
    $result_ = strtoupper($String);
    return $result_;
}

function formatParameters($paraMap, $urlencode) {
    $buff = "";
    ksort($paraMap);
    foreach ($paraMap as $k => $v) {
        if($k=="sign"){
            continue;
        }
        if ($urlencode) {
            $v = urlencode($v);
        }
        $buff .= $k . "=" . $v . "&";
    }
    $reqPar="";
    if (strlen($buff) > 0) {
        $reqPar = substr($buff, 0, strlen($buff) - 1);
    }
    return $reqPar;
}

$checkSign = getSign($wx_return, $security_code);

if($checkSign == $wx_return['sign']){
    # Successful
    wx_reply(true);

    //商户订单号
    $out_trade_no = $invoiceid;

    //微信支付交易号
    $trade_no = $transid;

    //业务处理代码
    $now = date("Y-m-d H:i:s");
    //根据out_trade_no，查找出商户ID
    $sql1 = "SELECT * FROM `pay_recharge_record` WHERE `out_trade_no`='{$out_trade_no}' limit 1";
    $row = $DB->query($sql1)->fetch();
    $pid = $row['pid'];
//    var_dump($row);

    //初始化pay_recharge表
    $sql2 = "INSERT IGNORE INTO `pay_recharge` (`id`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES ('{$pid}', '0', '{$now}', CURRENT_TIMESTAMP, '0', '0');";
    $DB->exec($sql2);

    //更新数据库
    $sql3 = "update `pay_recharge_record` set `trade_no`='{$trade_no}', `status` ='1',`endtime` ='{$now}' where `out_trade_no`='{$out_trade_no}'";
    $DB->exec($sql3);

    //添加到余额并记录
    try{
        $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $DB->beginTransaction();
        //查询当前余额
        $sql4 = "SELECT * FROM `pay_recharge` WHERE `id` = '{$pid}' FOR UPDATE ";
        $row2 = $DB->query($sql4)->fetch();
        $balance_before = round($row2['balance'],0);
        $income = $row2['income'];

        //插入充值记录
        $money = round($amount,0);
        $balance = $balance_before + $money;
        $income = $income + $money;

        $sql5 = "INSERT INTO `pay_recharge_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$pid}', '{$trade_no}', '1', '{$balance_before}', '{$money}', '{$balance}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', NULL, NULL);";

        //更新充值余额
        $sql6 = "UPDATE `pay_recharge` SET `balance` = '{$balance}', `income` = '{$income}' WHERE `pay_recharge`.`id` = '{$pid}';";

        $DB->exec($sql5);
        $DB->exec($sql6);
        $DB->commit();
    }catch (Exception $e){
        $DB->rollBack();
//        echo "Failed: " . $e->getMessage();
    }

    #签名验证成功，核对价格后，执行逻辑代码
    //checkCbInvoiceID($invoiceid,$GATEWAY["name"]); # Checks invoice ID is a valid invoice number or ends processing
    //checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does
    //
    //addInvoicePayment($invoiceid,$transid,$amount,$fee,$gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
    //logTransaction($GATEWAY["name"],$wx_return,"Successful"); # Save to Gateway Log: name, data array, status
} else {
    # Unsuccessful
    wx_reply(false);
    //logTransaction($GATEWAY["name"],$wx_return,"Unsuccessful".$return_code); # Save to Gateway Log: name, data array, status
}

//回复微信，默认为成功
function wx_reply ($t = true) {
    if($t == true){
        echo "<xml>
<return_code>SUCCESS</return_code>
<return_msg>OK</return_msg>
</xml>";
    }else{
        echo "<xml>
<return_code>FAIL</return_code>
<return_msg>签名错误</return_msg>
</xml>";
    }
}