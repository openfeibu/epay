<?php
/**
 * 下载
**/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

$batch=$_GET['batch'];
$allmoney=$_GET['allmoney'];
$data='';
$rs=$DB->query("SELECT * from pay_settle where batch='$batch'");

$i=0;
while($row = $rs->fetch())
{
	$i++;
	$data.=$i.','.$row['account'].','.mb_convert_encoding($row['username'], "GB2312", "UTF-8").','.$row['money'].',支付自动结算'."\r\n";
}

$date=date("Ymd");
$file="批次号,付款日期,付款人email,账户名称,总金额（元）,总笔数\r\n";
$file.="{$batch},{$date},{$conf['pay_email']},云端网络科技,{$allmoney},{$i}\r\n";
$file.="商户流水号,收款人email,收款人姓名,付款金额（元）,付款理由\r\n";
$file.=$data;

$file_name='pay_'.date("YmdHis").'.csv';
$file_size=strlen($file);
header("Content-Description: File Transfer");
header("Content-Type:application/force-download");
header("Content-Length: {$file_size}");
header("Content-Disposition:attachment; filename={$file_name}");
echo $file;
?>