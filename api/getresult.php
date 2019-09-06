<?PHP
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__."/../includes/api/init.php";

//写日志也要按天来生成
$file_date=date('Ymd');
$log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/getresult.log.".$file_date.".php";
$log_error_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/getresult.log_error.".$file_date.".php";
//判断日志文件存在与否
if(!file_exists($log_file)){
	//不存在就生成
	file_put_contents($log_file, "<?php require_once __DIR__.'/../config_log.php'; ?>".PHP_EOL, FILE_APPEND);
}
if(!file_exists($log_error_file)){
	//不存在就生成
	file_put_contents($log_error_file, "<?php require_once __DIR__.'/../config_log.php'; ?>".PHP_EOL, FILE_APPEND);
}
\epay\log::writeLog($log_file,$_REQUEST);

//初始化返回数组
$result = array();
$result['code'] = 0;
$result['msg'] = "未支付";
if(isset($_REQUEST['userid'])){
    $userid = daddslashes($_REQUEST['userid']);
}
//if(isset($_REQUEST['mark']) && $_REQUEST['mark'] != ''){
//    $trade_no = daddslashes($_REQUEST['mark']);
//}
if(isset($_REQUEST['trade_no']) && $_REQUEST['trade_no'] != ''){
    $trade_no = daddslashes($_REQUEST['trade_no']);
}else{
    \epay\output::output($result,true,true);
}

$sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' LIMIT 1; ";
$row = $DB->query($sql)->fetch();
if($row){
    if($row['status'] == 1){
        $result['code'] = 1;
        $result['msg'] = "支付成功";
    }
}else{
    $result['error'] = "无此订单号";
    //找不到订单号，异常记录
    \epay\log::writeLog($log_error_file,$_REQUEST);
}
\epay\output::output($result,true,true);
