<?php
/**
 * 帮助中心
**/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title='帮助中心';
include './head.php';

?>
 <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<div class="bg-light lter b-b wrapper-md hidden-print">
  <h1 class="m-n font-thin h3"><?php echo $title?></h1>
</div>
<div class="wrapper-md control">
<?php if(isset($msg)){?>
<div class="alert alert-info">
	<?php echo $msg?>
</div>
<?php }?>
	<div class="panel panel-default">
		<div class="panel-heading font-bold">
			使用说明
		</div>
		<div class="panel-body">
		<h3>1分钟读懂<?php echo $conf['web_name']?>交易规则</h3>
			<div style="line-height:26px"><span style="white-space:nowrap;"> 
<p style="white-space:normal;margin-bottom:14px;color:#333333;font-family:'microsoft yahei';font-size:14px;line-height:24px;">
	<strong>一、交易即时到账</strong> 
</p>
<p style="white-space:normal;margin-bottom:14px;color:#333333;font-family:'microsoft yahei';font-size:14px;line-height:24px;">
	你的客户通过<?php echo $conf['web_name']?>中任意一种付款方式（支付宝、微信支付、财付通、QQ钱包）付款成功后均会实时到账于你设置的专用收款账户<?php //echo $conf['web_name']?>账户，你可以在用户中心或订单记录中查看。
</p>
<p style="white-space:normal;margin-bottom:14px;color:#333333;font-family:'microsoft yahei';font-size:14px;line-height:24px;">
	<strong>二、T+1提现方案详解</strong> 
</p>
<p style="white-space:normal;margin-top:0px;margin-bottom:10px;padding:0px;border:0px;font-size:14px;line-height:25px;color:#79848E;font-family:'Microsoft YaHei', 'Heiti SC', simhei, 'Lucida Sans Unicode', 'Myriad Pro', 'Hiragino Sans GB', Verdana;text-indent:2em;background-color:#FFFFFF;">
	1、星期一、<span style="font-size:13px;line-height:20px;text-indent:26px;">星期</span>二、<span style="font-size:13px;line-height:20px;text-indent:26px;">星期</span>三、<span style="font-size:13px;line-height:20px;text-indent:26px;">星期</span>四、<span style="color:#79848E;font-family:'Microsoft YaHei', 'Heiti SC', simhei, 'Lucida Sans Unicode', 'Myriad Pro', 'Hiragino Sans GB', Verdana;font-size:14px;line-height:25px;text-indent:28px;white-space:normal;background-color:#FFFFFF;">星期五、</span><span style="color:#79848E;font-family:'Microsoft YaHei', 'Heiti SC', simhei, 'Lucida Sans Unicode', 'Myriad Pro', 'Hiragino Sans GB', Verdana;white-space:normal;font-size:13px;line-height:20px;text-indent:26px;">星期</span><span style="color:#79848E;font-family:'Microsoft YaHei', 'Heiti SC', simhei, 'Lucida Sans Unicode', 'Myriad Pro', 'Hiragino Sans GB', Verdana;font-size:14px;line-height:25px;text-indent:28px;white-space:normal;background-color:#FFFFFF;">六、</span><span style="color:#79848E;font-family:'Microsoft YaHei', 'Heiti SC', simhei, 'Lucida Sans Unicode', 'Myriad Pro', 'Hiragino Sans GB', Verdana;white-space:normal;font-size:13px;line-height:20px;text-indent:26px;">星期</span><span style="color:#79848E;font-family:'Microsoft YaHei', 'Heiti SC', simhei, 'Lucida Sans Unicode', 'Myriad Pro', 'Hiragino Sans GB', Verdana;font-size:14px;line-height:25px;text-indent:28px;white-space:normal;background-color:#FFFFFF;">日</span>，0点~21点59分59秒间提现的资金将于次日23点前到账。
</p>
<!--p style="white-space:normal;margin-top:0px;margin-bottom:10px;padding:0px;border:0px;font-size:14px;line-height:25px;color:#79848E;font-family:'Microsoft YaHei', 'Heiti SC', simhei, 'Lucida Sans Unicode', 'Myriad Pro', 'Hiragino Sans GB', Verdana;text-indent:2em;background-color:#FFFFFF;">
	<span style="font-size:13px;line-height:20px;text-indent:26px;">2、国家法定节假日期间提现的资金将于 节假日后第一个工作日23点前到账。</span> 
</p-->
<p style="white-space:normal;margin-bottom:14px;color:#333333;font-family:'microsoft yahei';font-size:14px;line-height:24px;">
	<strong>三、服务费率：<?php //echo $userrow['fee']*100; ?><!--%-->面议</strong>
</p>

<p style="white-space:normal;margin-bottom:14px;color:#333333;font-family:'microsoft yahei';font-size:14px;line-height:24px;">
	每笔提现操作将会产生5元提现费用
</p>
<p style="white-space:normal;margin-bottom:14px;color:#333333;font-family:'microsoft yahei';font-size:14px;line-height:24px;">
	<strong>四、多种结算方式</strong> 
</p>
<p style="white-space:normal;margin-bottom:14px;color:#333333;font-family:'microsoft yahei';font-size:14px;line-height:24px;">
	<span style="font-family:'Microsoft YaHei', 'Heiti SC', simhei, 'Lucida Sans Unicode', 'Myriad Pro', 'Hiragino Sans GB', Verdana;font-size:13px;line-height:20px;text-indent:26px;background-color:#FFFFFF;"><?php echo $conf['web_name']?></span>官方企业支付宝 -&gt; 您的个人支付宝（小额）
</p>
<p style="white-space:normal;margin-bottom:14px;color:#333333;font-family:'microsoft yahei';font-size:14px;line-height:24px;">
	<span style="font-family:'Microsoft YaHei', 'Heiti SC', simhei, 'Lucida Sans Unicode', 'Myriad Pro', 'Hiragino Sans GB', Verdana;font-size:13px;line-height:20px;text-indent:26px;background-color:#FFFFFF;"><?php echo $conf['web_name']?></span>官方对公账户 -&gt; 您的个人银行卡（大额）
</p>
</span></div>

		</div>
	</div>
</div>
    </div>
  </div>

<?php include 'foot.php';?>