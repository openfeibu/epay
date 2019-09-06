<?php
exit();
//ini_set('display_errors','On');
//error_reporting(E_ALL);


require './includes/common.php';

echo $_POST['bankxinming'];
echo $_COOKIE['user_token'];
exit;


require_once(SYSTEM_ROOT."cspay/epay.config.php");
require_once(SYSTEM_ROOT."cspay/epay_submit.class.php");

/**************************请求参数**************************/


$t = time();

//构造要请求的参数数组，无需改动
$parameter = array(
    "account_name" => $_POST['bankxinming'],
    "bank_card"    => $_POST['bankcardid'],
    "bank_linked"  => $_POST['bankopenid'],
    "bank_name"    => $_POST['bankname'],
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

if($html_text["ret_code"] == "SUCCESS"){
    if($html_text["tradeStatus"] == 2){
        //echo "转账失败,失败原因是：".$html_text["tradeMessage"];
        //exit("<script language='javascript'>alert('转账失败,失败原因是：".print_r($html_text)."');history.go(-1);</script>");
        print_r($html_text);
        exit;
    }

    if($html_text["tradeStatus"] == 1){
        //echo "转账处理中";

        //减少金额并插入bc_outmoney表轮寻处理结果
        $DB->query("update pay_user set already4paymoney=already4paymoney+{$_POST['fourpaymoney']} where id={$_POST['pid']}");
        $DB->query("insert into  `bc_outmoney` (`out_id`,`User_id`,`paytype`,`out_money`,`out_status`) values (".$t.",".$_POST['pid'].",'".$_POST['paytype']."',".$_POST['fourpaymoney'].",0) ");
        exit("<script language='javascript'>alert('转账处理中".$html_text["tradeMessage"]."');window.location.assign('/easypay/user/apply.php');</script>");

    }

    if($html_text["tradeStatus"] == 3){
        //echo "转账成功";
        $DB->query("update pay_user set already4paymoney=already4paymoney+{$_POST['fourpaymoney']} where id={$_POST['pid']}");
        $DB->query("insert into  `bc_outmoney` (`out_id`,`User_id`,`paytype`,`out_money`,`out_status`) values (".$t.",".$_POST['pid'].",'".$_POST['paytype']."',".$_POST['fourpaymoney'].",1) ");
        exit("<script language='javascript'>alert('转账成功".$html_text["tradeMessage"]."');window.location.assign('/easypay/user/apply.php');</script>");
        //减少金额
    }
}else{
    print_r($html_text);
    print_r($parameter);
    exit;
    //exit("<script language='javascript'>alert('转账失败,失败原因是：".$html_text["tradeMessage"]."');history.go(-1);</script>");


}

//echo "QR is ".$html_text["payinfo"];	
?>
