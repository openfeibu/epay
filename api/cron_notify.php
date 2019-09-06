<?php
/**
 * 本脚本为异步通知脚本，1分钟运行1次。
 */
header("Content-Type: text/html; charset=utf-8");
ini_set("display_errors","on");
error_reporting();
require_once __DIR__."/../includes/api/init.php";
if(!isset($order_by)){
    $order_by = "  order by endtime desc";
}
$sql = "SELECT `trade_no`,`order_one`,`endtime` FROM `pay_order` WHERE `status` = '1' AND `order_five` = '0' AND buyer<>'' AND endtime<>'0000-00-00 00:00:00' AND ADDTIME>=DATE_SUB(ENDTIME,INTERVAL 10 MINUTE) ".$order_by;
$results = $DB->query($sql)->fetchAll();
//var_dump($sql);
//比较时间要放置在外边，不然有偏差。
$now = time();
foreach ($results as $result){
    $trade_no = $result['trade_no'];
    $endtime = $result['endtime'];
    $endtime = strtotime($endtime);
    $order_one = $result['order_one'];
    $order_one++;
    //要根据通知条件来比较，只有时间到了才进行处理
    switch($order_one){
        case '1':
            $notify_time = $endtime;
            break;
        case '2':
            $notify_time = $endtime + 1 * 60;
            break;
        case '3':
            $notify_time = $endtime + 1 * 60;
            break;
        case '4':
            $notify_time = $endtime + 2 * 60;
            break;
        case '5':
            $notify_time = $endtime + 3 * 60;
            break;
        case '6':
            $notify_time = $endtime + 3 * 60;
            break;
        default:
            $notify_time = $endtime + 5 * 60;
            break;
    }
    if($notify_time > $now){
        continue; //不到时间就直接处理下一个
    }
    var_dump($trade_no);

    //通知处理，原有流程没有加入超时处理，现在加入限制30秒，尝试三次
	$cnt=0;
	$opts = array(
		'http'=>array(
			'method'=>"GET",
			'timeout'=>30,//单位秒
		)
	);
	$url = "{$website_urls}api/return_url.php?notify=yes&trade_no={$trade_no}";
	while($cnt<3){ //三次尝试不成就自动断开
        $return = file_get_contents($url, false, stream_context_create($opts));
        if($return===FALSE) {
            continue;
        }
        break;
    }
    $return_low = \epay\func::DeleteHtml(strtolower($return));
    if($return_low == "success" || $order_one >= 7){
        if($return_low != "success"){
            $return = "";
        }
        $sql = "UPDATE `pay_order` SET `order_one` = '{$order_one}', `order_five` = '1',`remarks` = '{$return}' WHERE `trade_no` = '{$trade_no}';"; //成功增加标记下一次跳过
    }else{
        $sql = "UPDATE `pay_order` SET `order_one` = '{}'  WHERE `trade_no` = '{$trade_no}';";//不成功只增计数
    }
    var_dump($sql);
    $result2 = $DB->query($sql);
    var_dump($return);
    var_dump($result2);

	//日志,改成分天
    $str = "{$return}--{$order_one}--{$url}--{$sql}";
	$file_date=date('Ymd');
    $log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/cron_notify.log.".$file_date.".php";
	//判断日志文件存在与否
	if(!file_exists($log_file)){
		//不存在就生成
		file_put_contents($log_file, "<?php exit;?>".PHP_EOL, FILE_APPEND);
	}
	\epay\log::writeLog($log_file,$str);
    echo "<br>";
}