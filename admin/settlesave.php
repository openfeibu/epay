<?php
/**
 * 提现操作
**/
header("Content-Type: text/html; charset=utf-8");
include("../includes/common.php");
//require_once __DIR__."/../includes/api/debug.php";
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

$memos = daddslashes($_REQUEST['memos']);
$status = daddslashes($_REQUEST['status']);
$rmb = daddslashes($_REQUEST['rmb']);

$whichcard = daddslashes($_REQUEST['whichcard']);
$info = explode("_", $whichcard);

$id = daddslashes($_REQUEST['id']);
$pid = daddslashes($_REQUEST['pid']);

$endtime = daddslashes($_REQUEST['endtime']);
date_default_timezone_set("Asia/Hong_Kong");
if($endtime == '' || $endtime == '0000-00-00 00:00:00'){
    $endtime = date("Y-m-d H:i:s");
}

$msg_reciever = daddslashes($_REQUEST['msg_reciever']);
$lines = explode(";", $msg_reciever);    //将多行数据分开

if ($memos=="银行返回成功"){
    foreach ($lines as $userinfo) {
    
    //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, 'http://71cn.com/wifi/cloud/sendtplmsg_bank_diy.php');
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        $post_data = array(
            "rmb" => $rmb,
            "openid" => $userinfo,
            "bank" => $info[1],
            "cardid" => $info[2],
            "names" => $info[3],
            "password" => "12345"
            );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        usleep(100000);
        //显示获得的数据
        //print_r($data);
    }

}
$sql = "SELECT * FROM `pay_apply` WHERE `id` = '{$id}' AND `pid` = '{$pid}';";
$results = $DB->query($sql);
$result = $results->fetch();
if(!$result){
    exit("<script language='javascript'>alert('保存不成功！');history.go(-1);</script>");
}
switch ($status){
    case '0':
        $sql = "UPDATE `pay_apply` SET `status` = {$status}, `note2` = '{$memos}' WHERE `id` = '{$id}' AND `pid` = '{$pid}';";
        break;
    case '1':
        $sql = "UPDATE `pay_apply` SET `endtime` = '{$endtime}', `status` = {$status}, `note2` = '{$memos}' WHERE `id` = '{$id}' AND `pid` = '{$pid}';";
        break;
    case '2':
        $sql = "UPDATE `pay_apply` SET `status` = {$status}, `note2` = '{$memos}' WHERE `id` = '{$id}' AND `pid` = '{$pid}';";
        break;
    case '3':
        //exit();
        $now = date("Y-m-d H:i:s");
        //添加退款到余额并记录
        try{
            $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $DB->beginTransaction();

            //更新提现记录为已拒绝并退款到余额
            $sql = "UPDATE `pay_apply` SET `status` = {$status}, `note2` = '{$memos}' WHERE `id` = '{$id}' AND `pid` = '{$pid}';";
            $DB->query($sql);

            //查询余额
            $sql1 = "SELECT * FROM `pay_balance` WHERE `id` = '{$pid}' FOR UPDATE ";
            $balance = intval($DB->query($sql1)->fetch()['balance']);

            //查找提现记录
//            $sql1 = "INSERT INTO `pay_apply` (`id`, `bank_name`, `bank_no`, `bank_user`, `cash`, `fee`, `addtime`, `is_over`, `status`, `pid`) VALUES
//(null,	'{$bankname}',	'{$bankcardid}',	'{$bankxinming}',	{$cash},'500','{$now}',	0,	0,	'{$pid}');";
            $trade_no = "apply@".$id;
            $apply_fee_no = "apply_fee@".$id;
            $sql1 = "SELECT * FROM `pay_balance_history` WHERE `userid` = '{$pid}' AND `trade_no` = '{$trade_no}'";
            $sql10 = "SELECT * FROM `pay_balance_history` WHERE `userid` = '{$pid}' AND `trade_no` = '{$apply_fee_no}'";
            $result = $DB->query($sql1)->fetch();
            $cash = $result['money'];
            $result = $DB->query($sql10)->fetch();
            $cash_fee = $result['money'];


            //插入余额增加记录
            $trade_no = "apply_refund@".$id;
            $apply_fee_no = "apply_fee_refund@".$id;
            $balance_before = $balance;
            $balance = $balance_before + $cash;
            $balance2 = $balance + $cash_fee; //增加手续费后余额
            $sql2 = "INSERT INTO `pay_balance_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$pid}', '{$trade_no}', '1', '{$balance_before}', '{$cash}', '{$balance}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', '提现退回', NULL);";
            $sql20 = "INSERT INTO `pay_balance_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$pid}', '{$apply_fee_no}', '1', '{$balance}', '{$cash_fee}', '{$balance2}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', '提现手续费退回', NULL);";
//
//            //更新余额
            $sql3 = "UPDATE `pay_balance` SET `balance` = '{$balance2}' WHERE `pay_balance`.`id` = '{$pid}';";
            $DB->query($sql2);
            $DB->query($sql20);
            $DB->query($sql3);

            //提交事务
            $DB->commit();
            exit("<script language='JavaScript'>alert('提现拒绝并退回到商户余额成功，谢谢。');history.back();</script>");
        }catch (PDOException $e){
            $DB->rollBack();
//            echo "Failed: " . $e->getMessage();
            exit("<script language='JavaScript'>alert('提现拒绝并退回到商户余额失败，失败原因：{$e->getMessage()}，请重试或联系客服。');history.back();</script>");
        }
        break;
    default:
        break;
}

if($status != 3){
    if($DB->query($sql)){
        exit("<script language='javascript'>alert('修改成功！');history.go(-1);</script>");
    }else{
        exit("<script language='javascript'>alert('修改不成功！');history.go(-1);</script>");
    }
}

//exit;

//$sqs=$DB->exec("update `pay_settle` set `memos` ='".$memos."',`status` =".$status.",`whichcard` ='".$whichcard."' where `id`=".$_REQUEST['ids']);
//header('Location:slist.php');
//echo "update `pay_settle` set `memos` ='".$memos."',`status` =".$status." where `pid`=".$pid." and (time between '".$yesterday." 00:00:00' and '".$yesterday." 23:59:59')";
?>
