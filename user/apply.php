<?php
/**
 * 申请提现
 **/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '申请提现';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";


$now = time();
$today = date("Y-m-d",$now);
//$yesterday = date("Y-m-d",strtotime("-1 day"));

//查询今日订单总额
$today = date("Y-m-d").' 00:00:00';
$sql = "SELECT * from pay_order where pid={$pid} and status=1 and cashstatus=0 and (endtime between '{$today} 00:00:00' and '{$today} 23:59:59')";
$rs = $DB->query($sql);
$order_today = 0;
while($row = $rs->fetch()){
    $order_today += $row['money'];
}

//账户余额
$sql4 = "SELECT * FROM `pay_balance` WHERE `id` = '{$pid}'";
$row3 = $DB->query($sql4)->fetch();
if($row3){
    $balance = round($row3['balance'],0) / 100;
}else{
    $balance = 0;
}

//查询未结算总额
$rs = $DB->query("SELECT * from pay_settle where pid={$pid} and status=0");
$unsettle_money = 0;
while($row = $rs->fetch()){
    $unsettle_money += $row['money'];
}
$order_today = $order_today + $unsettle_money;


//今日cqpay订单总额
$rs = $DB->query("SELECT * from pay_order where pid={$pid} and status=1 and type='cqpay' and (endtime between '{$today} 00:00:00' and '{$today} 23:59:59')");
$todaycqpay_money = 0;
while($row = $rs->fetch()){
    $todaycqpay_money += $row['money'];
}

//
$today0timestamp = strtotime(date("Y-m-d"),time());
$rs = $DB->query("SELECT * from bc_outmoney where User_id={$pid} and   paytype='XFZF_DF_DZ'  and out_status<2  and out_id>{$today0timestamp}");
$todayD0cqpay_money = 0;
$todayD0count = 0;
//计算出今天直接代付的总额
while($row = $rs->fetch()){
    $todayD0cqpay_money += $row['out_money'];
    $todayD0count++;

}

$todayAllowDZOut = floor(((($todaycqpay_money - (($todayD0cqpay_money + $todayD0count) / 0.8) / (1 - $userrow['fee']))) * 0.8 - 1) * (1 - $userrow['fee']) * 100) / 100;

if($todayAllowDZOut < 0){
    $todayAllowDZOut = 0;
}


$todaymdstr = date("Ymd",time());
$todaymdstrymdhms0 = strtotime($todaymdstr." 00:00:00");

$rs = $DB->query("SELECT * from bc_tradeday where ymd={$todaymdstr}");
$prevdaystrtmp = $todaymdstr;
while($row = $rs->fetch()){
    $prevdaystrtmp = strval($row['prevday']);
}
date_default_timezone_set("PRC");
if($prevdaystrtmp == $todaymdstr){
    $prevdaystrtmp3 = strtotime($prevdaystrtmp." 00:00:00");
    $prevdaystrtmp2 = substr($prevdaystrtmp,0,4)."-".substr($prevdaystrtmp,4,2)."-".substr($prevdaystrtmp,6,2)." 00:00:00";
}else{
    $prevdaystrtmp3 = strtotime($prevdaystrtmp." 23:59:59");
    $prevdaystrtmp2 = substr($prevdaystrtmp,0,4)."-".substr($prevdaystrtmp,4,2)."-".substr($prevdaystrtmp,6,2)." 23:59:59";
}

$rs = $DB->query("SELECT * from pay_order where pid={$pid} and status=1 and type='cqpay' and (endtime between '2018-01-01 00:00:00' and '{$prevdaystrtmp2}')");
$fdsafl = "SELECT * from pay_order where pid={$pid} and status=1 and type='cqpay' and (endtime between '2018-01-01 00:00:00' and '{$prevdaystrtmp2}')";
//计算出前一个交易日及其之前的所有充值总额
$prevcqpay_money = 0;
while($row = $rs->fetch()){
    $prevcqpay_money += $row['money'];

}


$rs = $DB->query("SELECT * from bc_outmoney where (User_id={$pid} and    out_status<2  and out_id<={$prevdaystrtmp3})  or (User_id={$pid} and    out_status<2 and paytype='XFZF_DF_NO' and out_id>{$todaymdstrymdhms0})");

$prevandbeforecqpay_money = 0;
$todayD0count = 1;
//计算出前一个交易日及其之前的提现总额
while($row = $rs->fetch()){
    $prevandbeforecqpay_money += $row['out_money'];
    $todayD0count++;
}


//计算可提现余额有多少
$couldoutmoney = floor((($prevcqpay_money * (1 - $userrow['fee']) - $todayD0count - $prevandbeforecqpay_money)) * 100) / 100;

if($couldoutmoney < 0){
    $couldoutmoney = 0;
}
$rs = $DB->query("SELECT * from pay_order where pid={$pid} and status=1 and type='cqpay'");

$cqpay_money = 0;
while($row = $rs->fetch()){
    $cqpay_money += $row['money'];
}


$enable_money = round($order_today - $order_today * $conf['settle_fee'] / 100,2);

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'do'){
    if($_REQUEST['submit'] == '申请提现'){
        var_dump($userrow);
        exit();
        if($userrow['apply'] == 1){
            exit("<script language='javascript'>alert('你今天已经申请过提现，请勿重复申请！');history.go(-1);</script>");
        }
        if($enable_money < $conf['settle_money']){
            exit("<script language='javascript'>alert('可提现余额不足！');history.go(-1);</script>");
        }
        if($userrow['type'] == 2){
            exit("<script language='javascript'>alert('您的商户出现异常，无法提现');history.go(-1);</script>");
        }
        $sqs = $DB->exec("update `pay_user` set `apply` ='1' where `id`='$pid'");
        exit("<script language='javascript'>alert('申请提现成功！');history.go(-1);</script>");
    }
}


?>
<SCRIPT language=javascript1.2>

    function checkaction(v) {

        var x = document.getElementById("paytypeid");
        var z = document.getElementById("fourpaymoneyid");
        //alert(x.value);

        x.value = v;

        var y = document.getElementById("paytypeid");

        if (v == "XFZF_DF_NO") {
            z.value = document.getElementById("fourpaymoneyNOid").value;
        } else {
            z.value = document.getElementById("fourpaymoneyDZid").value;
        }
        //alert(y.value);

//document.frmSearch.submit(); 
        document.getElementById("myForm").submit();
    }

</SCRIPT>


<div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<div class="bg-light lter b-b wrapper-md hidden-print">
  <h1 class="m-n font-thin h3">申请提现</h1>
</div>
<div class="wrapper-md control">
<?php
if(isset($msg)){
    echo "<div class=\"alert alert-info\">{$msg}</div>";
}

require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/autoload.php";
$user = \epay\user::find_user($pid);

//查询是否适用Google验证器
$googleauth = $user['googleauth'];
if($googleauth != ""){
    $googleauth = json_decode($googleauth,true);
    if($googleauth['status'] == "on"){
        //使用Google验证器
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
    $googleauth_echo = <<< EOF
                        <div class="form-group">
                    <label class="col-sm-4 control-label">Google验证码</label>
                    <div class="col-sm-4">
                        <input class="form-control" type="text" name="googlecode"  value="">
                        <a href="../assets/doc/APP下载/下载地址.php" style="color: blue;">Google验证器下载地址</a>&emsp;
                        <a href="./userGoogleAuth.php" style="color: blue;">Google验证器设置</a>
                    </div>
                </div>
EOF;
}else{
    $googleauth_echo = "";
    if($sms_switch){
        $sms_echo = <<< EOF
                        <div class="form-group">
                    <label class="col-sm-4 control-label">短信验证码</label>
                    <div class="col-sm-4">
                        <input class="form-control" type="text" name="sms"  value="">
                    </div>
                    <div class="col-sm-4">
                        <button id="sendmsg" type="button" value="">获取验证码</button>
                    </div>
                </div>
EOF;
        $googleauth_echo = $sms_echo;
    }
}

    print <<< EOF
<div class="panel panel-default">
		<div class="panel-heading font-bold">
			申请提现
		</div>
		<div class="panel-body">
			<!--<form class="form-horizontal devform" action="./apply.php?act=do" method="post">
			-->
			<form  name="frmSearch" id="myForm" class="form-horizontal devform" action="apply_outmoney.php" method="post">
			<input type=hidden name="pid" value="{$pid}">
			<input type=hidden id="paytype" name="paytype" value="XFZF_DF_NO">
			<input type=hidden id="fourpaymoneyid" name="fourpaymoney" value="10">
			
				<div class="form-group">
					<label class="col-sm-4 control-label">账户余额</label>
					<div class="col-sm-7">
						<input class="form-control" name="total_money" type="text" value="{$balance}" disabled>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">提现金额</label>
					<div class="col-sm-7">
						<input class="form-control" name="cash" type="text" value="" required>
					</div>
				</div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">收款人姓名</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="text" name="bankxinming"  value="{$userrow['bankxinming']}">
                    </div>
                </div>
				<div class="form-group">
					<label class="col-sm-4 control-label">开户银行</label>
					<div class="col-sm-7">
						<input class="form-control" type="text" name="bankname" value="{$userrow['bankname']}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">收款人银行卡号</label>
					<div class="col-sm-7">
						<input class="form-control" type="text" name="bankcardid"  value="{$userrow['bankcardid']}">
					</div>
				</div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">提现密码</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="password" name="cash_pwd"  value="">
                    </div>
                </div>
{$googleauth_echo}
                <div class="form-group">
                    <label class="col-sm-4 control-label"></label>
                    <div class="col-sm-7">
                        <input value="提交申请" class="btn btn-success form-control" type="submit">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"></label>
                    <div class="col-sm-7">
                        <font color="red">每笔提现操作将会产生5元提现费用，单笔提现金额不能大于5万。</font>
                    </div>
                </div>
			</form>
		</div>
	</div>
</div>
    </div>
  </div>
<script>
        var InterValObj;//timer变量，控制时间
        var count = 60;//间隔函数，1秒执行
        var curCount;//当前剩余秒数
        var code = "";//验证码
        var codeLength = 6;//验证码长度

        $(function() {
            $('#sendmsg').click(function() {
                console.log(this);
                $.ajax({
                    type: "POST",
                    url: "{$website_urls}api/sms/index.php?act=sendsms&pid={$pid}",
                    // data: "pid=",
                    success: function(result) {
                        if(result == 0){
                            curCount = count;
                            $("#sendmsg").css("background-color", "LightSkyBlue");
                            $("#sendmsg").attr("disabled",true);
                            InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
                            alert("验证码发送成功，请查收!");
                        }
                        if(result==1){
                            alert("短信发送失败，请与管理员联系或重试！");
                        }
                        if(result==2){
                            alert("手机号不正确，请在“证件审核”栏目填写正确的手机号，谢谢！");
                        }
                    },
                    dataType: 'json'
                });
            });
        });

        function SetRemainTime() {
            if(curCount == 0){
                window.clearInterval(InterValObj);//停止计时器
                $("#sendmsg").removeAttr("disabled");//启用按钮
                $("#sendmsg").css("background-color", "");
                $("#sendmsg").val("重发验证码");
                code = "";//清除验证码。如果不清除，过时间后，输入收到的验证码依然有效
            }else {
                curCount--;
                $("#sendmsg").val("获取" + curCount + "秒");
                // $("#sendmsg").innerText("aaaaaa");
                // document.getElementById("getmsg").value("获取成功");
            }
        }
    </script>
EOF;

include_once __DIR__.DIRECTORY_SEPARATOR.'foot.php';
