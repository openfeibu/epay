<?php
/**
 * 消费记录
**/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$id = $_SESSION['admin_userid'];
if($id==1){
    $id = 0;
}
$title = '消费记录';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
?>

  <!-- content -->
  <div id="content" class="app-content" role="main">
      <div class="app-content-body ">

            <div class="bg-light lter b-b wrapper-md">
                <h1 class="m-n font-thin h3"><?php echo $title ?></h1>
            </div>
            <div class="wrapper-md control">
<?php
$isorderpage=1;
if(!empty($_GET['type']) && !empty($_GET['kw'])) {
	$kw=daddslashes($_GET['kw']);
	if($_GET['type']==1)$sql=" and trade_no like '%$kw%'";
	elseif($_GET['type']==2)$sql=" and name='$kw'";
	elseif($_GET['type']==3)$sql=" and money='$kw'";
	elseif($_GET['type']==4)$sql=" and type='$kw'";
	else $sql="";
	$link='&type='.$_GET['type'].'&kw='.$_GET['kw'];
}else{
	$sql="";
	$link='';
    $sql = "AND createtime >='".date("Y-m-d 00:00:00")."'";
}
$pid = 0;
$query = "SELECT count(*) as a FROM `pay_recharge_history` WHERE `type` = 0 AND `userid` = '{$id}'{$sql}";
$numrows = $DB->query($query)->fetch();
$numrows = $numrows["a"];
$pagesize = 30;
$pages=intval($numrows/$pagesize);
if ($numrows%$pagesize){
    $pages++;
}
if (isset($_GET['page'])){
    $page = intval($_GET['page']);
}else{
    $page = 1;
}
$offset=$pagesize*($page - 1);
$sql = "SELECT * FROM `pay_recharge_history` WHERE `type` = 0 AND `userid` = '{$pid}'{$sql} order by id desc limit $offset,$pagesize";
$list=$DB->query($sql)->fetchAll();


if(isset($msg)){
    echo "<div class=\"alert alert-info\">{$msg}</div>";
}

print <<< EOF
<div class="panel panel-default">
		<div class="panel-heading font-bold">
			{$title}&nbsp;($numrows)
		</div>
	  <div class="row wrapper">
	    <div class="col-sm-5 m-b-xs">
	      <form action="recharge_reduce.php" method="GET" class="form-inline">
	        <div class="form-group">
			<select class="input-sm form-control" name="type">
			  <option value="1">交易号</option>
			  <option value="2">商品名称</option>
			  <option value="3">商品金额</option>
			  <option value="4">支付方式</option>
			</select>
		    </div>
			<div class="form-group">
			  <input type="text" class="input-sm form-control" name="kw" placeholder="搜索内容">
			</div>
			 <div class="form-group">
				<button class="btn btn-sm btn-default" type="submit">搜索</button>
				<a href="recharge_reduce.php?type=1&kw=扣除服务费"><button class="btn btn-sm btn-default" type="button">所有的服务器扣除费用</button></a>
			 </div>
		  </form>
		</div>
      </div>
		<div class="table-responsive">
        <table class="table table-striped" style="font-size: 16px;">
          <thead><tr><th>序号</th><th>扣费编号</th><th>商户ID</th><th>交易金额</th><th>服务费</th><th>扣费时间</th><th>状态</th></tr></thead>
          <tbody>
EOF;

$order_today=0;
$wx_today=0;
$alipay_today=0;
$QQpay_today=0;
foreach($list as $res){
	$nowrec_money=0;
	if ($res['type']=="wxpay"){
		$wx_today+=$res['money'];
	}
	if ($res['type']=="alipay"){
		$alipay_today+=$res['money'];
	}
	if ($res['type']=="qqpay"){
		$QQpay_today+=$res['money'];
    }
	if ($res['type']=="cqpay"){
		$QQpay_today+=$res['money'];
	}	
	$order_today+=$res['money'];

    $fee = round($res['money']/100,2);
    $money = round($fee * 100, 2);
    $trade_no = $res['trade_no'];
    $trade_no = substr($trade_no,0,strpos($trade_no, '@'));
    $result = $DB->query("SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}'")->fetch();
    $money = $result['money'];
    $userid = $result['pid'];
	echo "<tr><td>{$res['id']}</td><td>{$res['trade_no']}</td><td>{$userid}</td><td>￥{$money}<td>￥{$fee}<td>{$res['createtime']}</td><td>完成</td></tr>";
}
print "
          </tbody>
        </table>
      </div>
";

require '../includes/page.class.php';
#分页
?>
	</div>
		总计：<?php echo $order_today; ?>元，其中微信支付<?php echo $wx_today; ?>元，支付宝支付<?php echo $alipay_today; ?>元，QQ钱包<?php echo $QQpay_today; ?>元。	
</div>
    </div>
</div>
  <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>