<?php
header('Content-Type: text/html; charset=UTF-8');
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
//初始化参数
$result = array();
$log =  array();
$result['code'] = '0'; //返回状态码：1为成功，其它值为失败
$result['status'] = 'error:未支付'; //支付状态：'success'为支付成功，'error:错误信息'为未支付成功。
$result['type'] = ''; //支付方式：个人二维码支付接口可选的参数是：alipay2（支付宝）、 wechat2（微信）。
//$result['no'] = ''; //订单号：O87f4NTor-Jm4nIMOJTL8yT9D9Sk57ZyD5rnlg_zjTs
$result['trade_no'] = ''; //订单号：O87f4NTor-Jm4nIMOJTL8yT9D9Sk57ZyD5rnlg_zjTs
$result['money'] = ''; //金额：0.01
//$result['mark'] = ''; //备注：20180703111043591
$result['out_trade_no'] = ''; //备注：20180703111043591，商户订单号
//$result['dt'] = ''; //时间：1530587493950
$result['endtime'] = ''; //时间：2018-01-02 20:20:20
$result['version'] = '1'; //版本号：版本号，现在为1
$result['pid'] = ''; //商户ID：10003
$result['sign'] = ''; //签名方法：dt+mark+money+no+type+signkey+userids+version这几个参数拼接然后md5，signkey是商户在收费助手的程序配置里面设置的的signkey，userids是商户在收费助手的程序配置里面设置的商户ID[需要事先在平台里面创建好]
$result['sign_type'] = 'MD5'; //默认为MD5，不参与签名

if(isset($_REQUEST['trade_no'])){
    $trade_no = $_REQUEST['trade_no'];
}else{
    $result['code'] = 0;
    $result['status'] = 'error:未输入trade_no';
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
    exit();
}
require_once __DIR__.'/../includes/api/init.php';
$trade_no = daddslashes($trade_no);
//只获取完成订单信息
$order = \epay\order::findFinish($trade_no);
//var_dump($order);
if(!$order){
    if(isset($_REQUEST['return_url'])){
        header("Location: {$_REQUEST['return_url']}");
    }else{
        $result['code'] = 0;
        $result['status'] = 'error:查询失败，查不到订单号。';
        echo json_encode($result,JSON_UNESCAPED_UNICODE);
        exit();
    }
}

//获取正常用户信息
$sql = "SELECT `key` FROM `pay_user` WHERE `active`='1' AND `id` = '{$order['pid']}' LIMIT 1";
$user = $DB->query($sql)->fetch();
$key = $user['key'];

//提交数据到商户return_url;
$result['code'] = $order['status']; //返回状态码：1为成功，其它值为失败
$result['status'] = $order['status']; //支付状态：'success'为支付成功，'error:错误信息'为未支付成功。
//if($order['status'] == '1'){
//    $result['status'] = 'success';
//}else{
//    $result['status'] = 'error:code '.$order['status'];
//}
$result['type'] = $order['type']; //支付方式：个人二维码支付接口可选的参数是：alipay2（支付宝）、 wechat2（微信）。
$result['money'] = $order['money']; //金额：0.01
$result['trade_no'] = $order['trade_no']; //订单号
$result['out_trade_no'] = $order['out_trade_no']; //商户订单号
$result['endtime'] = $order['endtime']; //时间：1530587493950
$result['version'] = '1'; //版本号：版本号，现在为1
$result['pid'] = $order['pid']; //商户ID：10003
$result['sign_type'] = 'MD5'; //默认为MD5，不参与签名
//查询attach
if(isset($order['attach']) && $order['attach'] != ''){
    $result['attach'] = $order['attach'];
}

$result['sign'] = \epay\getSign($result,$key); //签名方法：

//根据是否有notify，判断是同步还是异步。
//日志
$log['request'] = $_REQUEST;
$log['post'] = $result;

if(isset($_REQUEST['notify']) && $_REQUEST['notify'] == 'yes'){
    //异步，采用POST
    $url = $order['notify_url'];
    $result1 = \epay\curl_request($url,$result);
    $result2 = strtolower(DeleteHtml($result1));
    if($result2 == 'success'){
        $data=array();
        $data["trade_no"] = $trade_no;
        $data["result1"] = $result1;
        $sql = "UPDATE `pay_order` SET `order_five` = '1', `remarks` = :result1 WHERE `trade_no` = :trade_no AND `remarks` = '';";
        try{
            $re = $DB->prepare($sql);
            $re->execute($data);
        }catch(\Exception $e){
            echo "执行失败：";
        }
        echo "回调成功：";
    }else{
        echo "回调返回不正确($url)：";
    }
    echo $result1;
    $log['result'] = $result1;
    $log['notify_url'] = $url;
    //var_dump($log);
}else{
    //同步，这里要额外加入对？号处理。
    if(strpos($order['return_url'],'?') !==false){
        $url = $order['return_url']."&".http_build_query($result);
    }else{
        $url = $order['return_url']."?".http_build_query($result);
    }
    header("Location: $url");
    $log['return_url'] = $url;
}

//日志,改成分天
$file_date=date('Ymd');
$log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/return_url.log".$file_date.".php";
//判断日志文件存在与否
if(!file_exists($log_file)){
    //不存在就生成
    file_put_contents($log_file, "<?php exit;?>".PHP_EOL, FILE_APPEND);
}
file_put_contents($log_file,date("Y-m-d H:i:s : ")."(1-ALL)".json_encode($log).PHP_EOL,FILE_APPEND);

/**
 * 去除空白
 */
function DeleteHtml($str)
{
    return trimall($str);
    $str = trim($str); //清除字符串两边的空格
    $str = preg_replace("/\t/","",$str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
    $str = preg_replace("/\r\n/","",$str);
    $str = preg_replace("/\r/","",$str);
    $str = preg_replace("/\n/","",$str);
    $str = preg_replace("/ /","",$str);
    $str = preg_replace("/  /","",$str);  //匹配html中的空格
    return trim($str); //返回字符串
}

function trimall($str){
    $qian=array(" ","　","\t","\n","\r");
    return str_replace($qian, '', $str);
}
