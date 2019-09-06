<?php
exit();
include("../includes/common.php");
$title = $website_name.'代理商后台管理';
include './head.php';
if($islogin != 1){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

$userrow = $DB->query("SELECT * FROM pay_admin WHERE admin_user='{$user}' limit 1")->fetch();
$count1 = $DB->query("SELECT * from pay_order")->rowCount();
$count2 = $DB->query("SELECT * from pay_user")->rowCount();
$count3 = file_get_contents(SYSTEM_ROOT.'all.txt');
$count4 = file_get_contents(SYSTEM_ROOT.'settle.txt');
$mysqlversion = $DB->query("select VERSION()")->fetch();
?>
<div class="content" class="app-content" role="main">
    <div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
        <!--
      <div class="panel panel-primary">
        <div class="panel-heading"><h3 class="panel-title">后台管理首页</h3></div>
          <ul class="list-group">
            <li class="list-group-item"><span class="glyphicon glyphicon-stats"></span> <b>订单总数：</b><?php echo $count1 ?></li>
			<li class="list-group-item"><span class="glyphicon glyphicon-tint"></span> <b>商户数量：</b><?php echo $count2 ?></li>
			<li class="list-group-item"><span class="glyphicon glyphicon-tint"></span> <b>总计余额：</b><?php echo $count3 ?>元（每小时更新一次）</li>
			<li class="list-group-item"><span class="glyphicon glyphicon-tint"></span> <b>结算余额：</b><?php echo $count4 ?>元（每小时更新一次）</li>
            <li class="list-group-item"><span class="glyphicon glyphicon-time"></span> <b>现在时间：</b> <?=$date?></li>
			<li class="list-group-item"><span class="glyphicon glyphicon-home"></span> <a href="../" class="btn btn-xs btn-primary">返回首页</a>
			</li>
          </ul>
      </div>
	  -->
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">代理信息 [代理信息修改请联系平台管理员]</h3>
            </div>
            <ul class="list-group">
                <li class="list-group-item">
                    <b>代理ＩＤ：</b><?php echo $userrow['admin_user'] ?>

                </li>
                <li class="list-group-item">
                    <b>代理名称：</b><?php echo $userrow['agentname'] ?>
                </li>
                <li class="list-group-item">
                    <b>结算银行卡号：</b><?php echo $userrow['bankcardid'] ?>
                </li>

                <li class="list-group-item">
                    <b>开户行：</b><?php echo $userrow['bankopename'] ?>
                </li>
                <li class="list-group-item">
                    <b>户名：</b><?php echo $userrow['bankaccountname']; ?>
                </li>
                <li class="list-group-item">
                    <b>联系电话：</b><?php echo $userrow['agentele']; ?>
                </li>
            </ul>
        </div>
    </div>
</div>