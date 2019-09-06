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

$datenow = date('Y-m-d',time());
$timenow = strtotime($datenow);
$yesterday = date('Y-m-d',$timenow);


$today = date("Y-m-d").' 00:00:00';
$rs = $DB->query("SELECT * from pay_order where pid={$pid} and status=1 and cashstatus=0 and (endtime between '{$yesterday} 00:00:00' and '{$yesterday} 23:59:59')");
$order_today = 0;
while($row = $rs->fetch()){
    $order_today += $row['money'];
}

$rs = $DB->query("SELECT * from pay_settle where pid={$pid} and status=0");
$unsettle_money = 0;
while($row = $rs->fetch()){
    $unsettle_money += $row['money'];

}
$order_today = $order_today + $unsettle_money;


$rs = $DB->query("SELECT * from pay_order where pid={$pid} and status=1 and type='cqpay' and (endtime between '{$yesterday} 00:00:00' and '{$yesterday} 23:59:59')");

$todaycqpay_money = 0;
while($row = $rs->fetch()){
    $todaycqpay_money += $row['money'];

}

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
$todaymdstrymdhms0 = strtotime($$todaymdstr." 00:00:00");

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

if(isset($_GET['act']) && $_GET['act'] == 'do'){
    if($_POST['submit'] == '申请提现'){
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
                <?php if(isset($msg)){ ?>
                    <div class="alert alert-info">
                        <?php echo $msg ?>
                    </div>
                <?php } ?>
                <div class="panel panel-default">
                    <div class="panel-heading font-bold">
                        申请提现
                    </div>
                    <div class="panel-body">
                        <!--<form class="form-horizontal devform" action="./apply.php?act=do" method="post">
                        -->
                        <form name="frmSearch" id="myForm" class="form-horizontal devform"
                              action="cqpayapi_outmoney.php" method="post">
                            <input type=hidden name="pid" value="<?php echo $pid; ?> ">
                            <input type=hidden id="paytypeid" name="paytype" value="XFZF_DF_NO">
                            <input type=hidden id="fourpaymoneyid" name="fourpaymoney" value="0">

                            <div class="form-group">
                                <label class="col-sm-4 control-label">支付宝账号</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" value="<?php echo $userrow['account'] ?>"
                                           disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">支付宝姓名</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" value="<?php echo $userrow['username'] ?>"
                                           disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">开户银行</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="bankname"
                                           value="<?php echo $userrow['bankname'] ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">收款人账户开户行联行号 <a href="http://www.lianhanghao.com/"
                                                                                     target="_blank"><font
                                                color=red>查询</font></a></label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="bankopenid"
                                           value="<?php echo $userrow['bankopenid'] ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">收款人姓名</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="bankxinming"
                                           value="<?php echo $userrow['bankxinming'] ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">收款人银行卡号</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="bankcardid"
                                           value="<?php echo $userrow['bankcardid'] ?>">
                                </div>
                            </div>
                            <!--
				<div class="form-group">
					<label class="col-sm-4 control-label">当前余额</label>
					<div class="col-sm-7">
						<input class="form-control" type="text" value="<?php echo $order_today ?>" disabled>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">可提现余额</label>
					<div class="col-sm-7">
						<input class="form-control" type="text" name="tmoney" value="<?php echo $enable_money ?>" disabled>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-sm-4 control-label">第四方京东支付交易总额</label>
					<div class="col-sm-7">
						<input class="form-control" type="text" name="tmoney" value="<?php echo $cqpay_money ?>" disabled>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 control-label">第四方京东支付已提现总额</label>
					<div class="col-sm-7">
						<input class="form-control" type="text" name="tmoney" value="<?php echo $userrow['already4paymoney'] ?>" disabled>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-sm-4 control-label">第四方京东支付余额</label>
					<div class="col-sm-7">
						<input class="form-control" type="text" name="tmoney" value="<?php echo strval($cqpay_money - $userrow['already4paymoney']) ?>" disabled>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 control-label">第四方京东支付可提现余额<br/>【扣除2%的服务费和单笔提现手续费1元后剩下的钱】</label>
					<div class="col-sm-7">
						<input class="form-control" type="text" name="tmoney" value="<?php echo strval(($cqpay_money - $userrow['already4paymoney']) * (1 - $userrow['fee']) - 1) ?>" disabled>
					</div>
				</div>
				-->

                            <div class="form-group">
                                <label class="col-sm-4 control-label">第四方京东支付今天交易总额</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="tmoney"
                                           value="<?php echo $todaycqpay_money ?>" disabled>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label">第四方京东支付今天已D0垫资代付总额</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="tmoney"
                                           value="<?php echo $todayD0cqpay_money ?>" disabled>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label">请输入第四方京东支付提现金额<br/>垫资代付D0[可提取当天收款总额的80%]</label>
                                <div class="col-sm-3">
                                    <input class="form-control" type="text" id="fourpaymoneyDZid" name="fourpaymoneyDZ"
                                           value="<?php echo $todayAllowDZOut ?>">
                                </div>
                                <div class="col-sm-4">
                                    <input type="button" name="submit3" onclick="checkaction('XFZF_DF_DZ');"
                                           value="申请提现[垫资代付]" class="btn btn-success form-control"/>
                                </div>

                            </div>


                            <div class="form-group">
                                <label class="col-sm-4 control-label">第四方京东支付 上一交易日及其之前的历史交易总额</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="tmoney"
                                           value="<?php echo $prevcqpay_money ?>" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">第四方京东支付 历史提现总额</label>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" name="tmoney"
                                           value="<?php echo $prevandbeforecqpay_money ?>" disabled>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-sm-4 control-label">请输入第四方京东支付提现金额<br/>余额代付</label>
                                <div class="col-sm-3">
                                    <input class="form-control" type="text" id="fourpaymoneyNOid" name="fourpaymoneyNO"
                                           value="<?php echo strval($couldoutmoney) ?>">
                                </div>
                                <div class="col-sm-4">
                                    <input type="button" name="submit2" onclick="checkaction('XFZF_DF_NO');"
                                           value="申请提现[余额代付]" class="btn btn-primary form-control"/>
                                </div>
                            </div>


                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-4"></label>
                                <div class="col-sm-6">
                                    <h4><span class="glyphicon glyphicon-info-sign"></span>注意事项</h4>
                                    每笔提现操作需要支付提现手续费1元<br/>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'foot.php'; ?>