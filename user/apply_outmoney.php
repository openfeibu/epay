<?php
/**
 * 申请提现
 **/
header("Content-Type: text/html; charset=UTF-8");
require_once __DIR__.'/../includes/common.php';
require_once __DIR__."/../includes/api/init.php";
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
//是否是迁移模式
if(isset($migrate_db) && $migrate_db){
    exit("<script language='JavaScript'>alert('系统维护中，暂停此功能。');history.back();</script>");//迁移模式，停止提现
}

$now = date("Y-m-d H:i:s");

if(isset($_REQUEST['pid']) && isset($_REQUEST['paytype']) && isset($_REQUEST['fourpaymoney']) && isset($_REQUEST['cash']) && isset($_REQUEST['bankxinming']) && isset($_REQUEST['bankname']) && isset($_REQUEST['bankcardid']) && isset($_REQUEST['cash_pwd'])){
    $paytype = \epay\daddslashes($_REQUEST['paytype']);
    $fourpaymoney = \epay\daddslashes($_REQUEST['fourpaymoney']);
    $cash = \epay\daddslashes($_REQUEST['cash']);
    $bankxinming = \epay\daddslashes($_REQUEST['bankxinming']);
    $bankname = \epay\daddslashes($_REQUEST['bankname']);
    $bankcardid = \epay\daddslashes($_REQUEST['bankcardid']);
    $cash_pwd = \epay\daddslashes($_REQUEST['cash_pwd']);
}else{
    exit("<script language='JavaScript'>alert('提现申请失败，失败原因是：提交的信息不完整。');history.back();</script>");
}

//Google验证器
$user = \epay\user::find_user($pid);
$googleauth = $user['googleauth'];
if($googleauth != ""){
    $googleauth = json_decode($googleauth,true);
    if($googleauth['status'] == "on"){
        $googleauth = true;
    }else{
        $googleauth = false;
    }
}else{
    $googleauth = false;
}
if($googleauth_user == true){
    $googleauth = true;
}

if($googleauth == true){
    //使用Google验证器
    require_once __DIR__.DIRECTORY_SEPARATOR."../includes/PHPGangsta/GoogleAuthenticator.php";
    $ga = new PHPGangsta_GoogleAuthenticator();
    $onecode = $ga->getCode($googleauth['secret']);
    if(isset($_REQUEST['googlecode']) && $_REQUEST['googlecode'] != ''){
        if($onecode != $_REQUEST['googlecode']){
            exit("<script language='JavaScript'>alert('提现申请失败，失败原因是：Google验证码错误。');history.back();</script>");
        }
    }else{
        exit("<script language='JavaScript'>alert('提现申请失败，失败原因是：Google验证码为空。');history.back();</script>");
    }
}

//是否验证短信验证码，true为验证，false为不验证。
if($sms_switch){
    if(isset($_REQUEST['sms']) && $_REQUEST['sms'] != ''){
        $sms = daddslashes($_REQUEST['sms']);
    }else{
        exit("<script language='javascript'>alert('短信验证码为空，请重新输入，验证码5分钟内有效性。');history.go(-1);</script>");
    }
    //检查短信验证码是否正确
    $sql = "SELECT * FROM `pay_smslog` WHERE `userid` = '{$pid}' AND `code` = '{$sms}' ORDER BY `id` DESC LIMIT 1";
    $result = $DB->query($sql);
    if($result){
        $row = $result->fetch();
        if(!$row){
            exit("<script language='javascript'>alert('短信验证码不正确，请重新输入，验证码5分钟内有效性。');history.go(-1);</script>");
        }
        if($row['expiredtime'] < $now){
            exit("<script language='javascript'>alert('短信验证码已过期，请重新获取，验证码5分钟内有效性。');history.go(-1);</script>");
        }
    }else{
        //系统错误，请重试，或联系系统管理员。
        exit("<script language='javascript'>alert('系统错误，请重试，或联系系统管理员。');history.go(-1);</script>");
    }
}

//检查信息完整性
if(floatval($_POST['fourpaymoney']) <= 0){
    //如果今天需要代付的钱超过了允许范围【有人想hack】
    exit("<script language='javascript'>alert('转账失败,失败原因是：取现金额为0或负数，这是非法的。');history.go(-1);</script>");
}

if($_POST['paytype'] == "XFZF_DF_DZ"){
    if(floatval($_POST['fourpaymoney']) > $todayAllowDZOut){
        //如果今天需要代付的钱超过了允许范围【有人想hack】
        exit("<script language='javascript'>alert('转账失败,失败原因是：取现金额超过了今天代付的剩余额度".print_r($html_text)."');history.go(-1);</script>");
    }
}else{
    $couldoutmoney = 100000;
    if(floatval($_POST['fourpaymoney']) > $couldoutmoney){
        //如果今天需要代付的钱超过了允许范围【有人想hack】
        exit("<script language='javascript'>alert('转账失败,失败原因是：取现金额超过了剩余额度');history.go(-1);</script>");
    }
}

//账户余额
$sql4 = "SELECT * FROM `pay_balance` WHERE `id` = '{$pid}'";
$row3 = $DB->query($sql4)->fetch();
if($row3){
    $balance = $row3['balance'];
}

//检查提现密码是否正确
$sql = "SELECT * FROM `pay_user` WHERE `id` = '{$pid}'";
$user = $DB->query($sql)->fetch();
if($user['cash_pwd'] != $cash_pwd || $cash_pwd == ''){
    exit("<script language='JavaScript'>alert('提现申请失败，失败原因是：提现密码错误。');history.back();</script>");
}

//检查提现金额是否正确
if(!is_numeric($cash) || floatval($cash) <= 0){
    exit("<script language='JavaScript'>alert('提现申请失败，失败原因是：取现金额不正确。');history.back();</script>");
}else{
    $cash = abs(intval($cash * 100));
}

if($balance <= 500){
    exit("<script language='JavaScript'>alert('提现申请失败，失败原因是：余额少于或等于5元，扣除手续费后不能提现。');history.back();</script>");
}

if($cash > 5000000){
    exit("<script language='JavaScript'>alert('提现申请失败，失败原因是：提现金额不能大于5万。');history.back();</script>");
}

if($cash <= 0){
    exit("<script language='JavaScript'>alert('提现申请失败，失败原因是：提现金额错误。');history.back();</script>");
}

if($balance - 500 < $cash){
    $balance = ($balance - 500) / 100;
    exit("<script language='JavaScript'>alert('提现申请失败，失败原因是：扣除5元手续费后，最大可提现余额为{$balance}元，请重试。');history.back();</script>");
}


//扣除余额并记录
try{
    $DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $DB->beginTransaction();

    //查询余额
    $sql1 = "SELECT * FROM `pay_balance` WHERE `id` = '{$pid}' FOR UPDATE ";
    $balance = intval($DB->query($sql1)->fetch()['balance']);
//    var_dump($balance);
//    var_dump($cash);
//    exit();

    //插入提现记录
    if($_SESSION['agentuuid'] == '1'){
        $sql1 = "INSERT INTO `pay_apply` (`id`, `bank_name`, `bank_no`, `bank_user`, `cash`, `fee`, `addtime`, `is_over`, `status`, `pid`) VALUES
(null,	'{$bankname}',	'{$bankcardid}',	'{$bankxinming}',	{$cash},'500','{$now}',	0,	0,	'{$pid}');";
    }
    else{
        $uid = $_SESSION["uid"];
        $sql1 = "INSERT INTO `pay_apply` (`id`, `bank_name`, `bank_no`, `bank_user`, `cash`, `fee`, `addtime`, `is_over`, `status`, `pid`, `uid`) VALUES
(null,	'{$bankname}',	'{$bankcardid}',	'{$bankxinming}',	{$cash},'500','{$now}',	0,	0,	'{$pid}', '{$uid}');";
    }
    $result = $DB->query($sql1);
    $trade_no = "apply@".$DB->lastInsertId();
    $apply_fee_no = "apply_fee@".$DB->lastInsertId();

    //存储提现ip
    $clientip = $_SERVER['REMOTE_ADDR'];
    //插入余额减少记录
    $balance_before = $balance;
    $balance = $balance_before - $cash;
    $balance2 = $balance - 500; //扣除手续费后余额
    $sql2 = "INSERT INTO `pay_balance_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$pid}', '{$trade_no}', '0', '{$balance_before}', '{$cash}', '{$balance}', '{$now}', CURRENT_TIMESTAMP, '{$clientip}', '提现', NULL);";
    $sql20 = "INSERT INTO `pay_balance_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$pid}', '{$apply_fee_no}', '0', '{$balance}', '500', '{$balance2}', '{$now}', CURRENT_TIMESTAMP, '{$clientip}', '提现手续费', NULL);";

    //更新余额
    $sql3 = "UPDATE `pay_balance` SET `balance` = '{$balance2}' WHERE `pay_balance`.`id` = '{$pid}';";
    $DB->query($sql2);
    $DB->query($sql20);
    $DB->query($sql3);

    //提交事务
    $DB->commit();
    exit("<script language='JavaScript'>alert('提现申请成功，请等待系统审核通过，谢谢。');history.back();</script>");
}catch(PDOException $e){
    $DB->rollBack();
    echo "Failed: ".$e->getMessage();
    exit("<script language='JavaScript'>alert('提现申请失败，失败原因：{$e->getMessage()}，请重试或联系客服。');history.back();</script>");
}

//$rs = $DB->query($sql);
//if($rs){
//    exit("<script language='JavaScript'>alert('提现申请成功，请等待系统审核通过，谢谢。');history.back();</script>");
//}else{
//    exit("<script language='JavaScript'>alert('提现申请失败，失败原因：未知错误，请重试或联系客服。');history.back();</script>");
//}
//exit();


require_once(SYSTEM_ROOT."cspay/epay.config.php");
require_once(SYSTEM_ROOT."cspay/epay_submit.class.php");
/**************************请求参数**************************/


$t = time();

//构造要请求的参数数组，无需改动
$parameter = array(
    "account_name" => $userrow['bankxinming'],
    "bank_card"    => $userrow['bankcardid'],
    "bank_linked"  => $userrow['bankopenid'],
    "bank_name"    => $userrow['bankname'],
    "mch_id"       => trim($alipay_config['partner']),
    "out_trade_no" => $t,
    "service"      => $_POST['paytype'],
    "trans_money"  => $_POST['fourpaymoney'] * 100,
);


//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestOutMoneyCurl($parameter);
//echo $html_text;
//print_r($html_text);
//exit;

if($html_text["ret_code"] == "SUCCESS"){
    if($html_text["tradeStatus"] == 2){
        //echo "转账失败,失败原因是：".$html_text["tradeMessage"];
        exit("<script language='javascript'>alert('转账失败,失败原因是：".print_r($html_text)."');history.go(-1);</script>");
        //print_r($html_text);
        //exit;
    }

    if($html_text["tradeStatus"] == 1){
        //echo "转账处理中";

        //减少金额并插入bc_outmoney表轮寻处理结果
        $DB->query("update pay_user set already4paymoney=already4paymoney+{$_POST['fourpaymoney']} where id={$pid}");
        $DB->query("insert into  `bc_outmoney` (`out_id`,`User_id`,`paytype`,`out_money`,`out_status`) values (".$t.",".$pid.",'".$_POST['paytype']."',".$_POST['fourpaymoney'].",0) ");
        exit("<script language='javascript'>alert('已向结算中心发出转账申请，转账处理中，请耐心等候。当前结算中心返回：".$html_text["tradeMessage"]."');window.location.assign('/easypay/user/apply.php');</script>");

    }

    if($html_text["tradeStatus"] == 3){
        //echo "转账成功";
        $DB->query("update pay_user set already4paymoney=already4paymoney+{$_POST['fourpaymoney']} where id={$pid}");
        $DB->query("insert into  `bc_outmoney` (`out_id`,`User_id`,`paytype`,`out_money`,`out_status`) values (".$t.",".$pid.",'".$_POST['paytype']."',".$_POST['fourpaymoney'].",1) ");
        exit("<script language='javascript'>alert('转账成功".$html_text["tradeMessage"]."');window.location.assign('/easypay/user/apply.php');</script>");
        //减少金额
    }
}else{
    //print_r($html_text);
    //print_r($parameter);
    //exit;
    exit("<script language='javascript'>alert('转账失败,失败原因是：".$html_text["ret_message"]."');history.go(-1);</script>");


}

//echo "QR is ".$html_text["payinfo"];	
?>
