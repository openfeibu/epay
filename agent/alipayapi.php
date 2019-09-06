<?php
exit();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>支付宝批量付款到支付宝账户有密接口接口</title>
</head>
<?php
/* *
 * 功能：批量付款到支付宝账户有密接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
 * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
 * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */

include("../includes/common.php");
require_once(SYSTEM_ROOT."alipay/alipay.config.php");
require_once(SYSTEM_ROOT."alipay/alipay_submit.class.php");

if($islogin == 1){
}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

$batch_no = $_GET['batch'];
$batch_no2 = $_GET['batch2'];
$batch_fee = $_GET['allmoney'];
$data = '';
$rs = $DB->query("SELECT * from pay_settle where batch='$batch_no'");

if(!empty($batch_no2)) $batch_no = $batch_no2;
$i = 0;
while($row = $rs->fetch()){
    $i++;
    $data .= $batch_no.$i.'^'.$row['account'].'^'.$row['username'].'^'.$row['money'].'^epay|';
}
$data = substr($data,0,-1);

$date = date("Ymd");

//构造要请求的参数数组，无需改动
$parameter = array(
    "service"        => "batch_trans_notify",
    "partner"        => trim($alipay_config['partner']),
    //"notify_url"	=> $notify_url,
    "email"          => trim($alipay_config['seller_email']), //付款账号
    "account_name"   => $conf['pay_name'], //付款账户名
    "pay_date"       => $date, //付款当天日期
    "batch_no"       => $batch_no, //批次号
    "batch_fee"      => $batch_fee, //付款总金额
    "batch_num"      => $i, //付款笔数
    "detail_data"    => $data, //付款详细数据
    "_input_charset" => trim(strtolower($alipay_config['input_charset'])),
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter,"post","确认");
echo $html_text;

?>
</body>
</html>