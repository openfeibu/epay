<?php
/**
 * 结算记录
**/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '结算记录';
include './head.php';
?>
<?php

$numrows=$DB->query("SELECT * from pay_settle WHERE pid={$pid}")->rowCount();
$pagesize=20;
$pages=intval($numrows/$pagesize);
if ($numrows%$pagesize)
{
 $pages++;
 }
if (isset($_GET['page'])){
$page=intval($_GET['page']);
}
else{
$page=1;
}
$offset=$pagesize*($page - 1);

$list=$DB->query("SELECT * FROM pay_settle WHERE pid={$pid} order by time desc limit $offset,$pagesize")->fetchAll();

?>
 <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<div class="bg-light lter b-b wrapper-md hidden-print">
  <h1 class="m-n font-thin h3">结算记录</h1>
</div>
<div class="wrapper-md control">
<?php if(isset($msg)){?>
<div class="alert alert-info">
	<?php echo $msg?>
</div>
<?php }?>
	<div class="panel panel-default">
		<div class="panel-heading font-bold">
			结算记录&nbsp;(<?php echo $numrows?>)
		</div>
		<div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>结算日期</th><th><u>支付结算金额(元)</u><br/>支付宝/微信/QQ钱包/京东支付</th><th>服务费</th><th><u>入账金额(元)</u><br/>支付宝/微信/QQ钱包/京东支付</th><th>状态</th><th>备注</th></tr></thead>
          <tbody>
<?php
foreach($list as $res){
	if ($res['status']==1){
		$status="已结算";
	}else{
		$status="未结算";		
	}
  //echo '<tr><td>'.substr($res['time'],0,10).'</td><td>￥ <b>'.$res['money'].'</b><br/>'.$res['alipay'].'/'.$res['wxpay'].'/'.$res['qqpay'].'</td><td>￥ <b>'.$res['fee'].'</b></td><td>￥ <b>'.strval($res['money']-$res['fee']).'</b><br/>'.strval(round($res['alipay']*(100-$conf['settle_fee'])/100,2)).'/'.strval(round($res['wxpay']*(100-$conf['settle_fee'])/100,2)).'/'.strval(round($res['qqpay']*(100-$conf['settle_fee'])/100,2)).'</td><td>'.$res['memos'].'</td></tr>';
	echo '<tr><td>'.substr($res['time'],0,19).'</td><td>￥ <b>'.$res['money'].'</b><br/>'.$res['alipay'].'/'.$res['wxpay'].'/'.$res['qqpay'].'/'.$res['cqpay'].'</td><td>￥ <b>'.$res['fee'].'</b></td><td>￥ <b>'.strval(round($res['alipay']*(1-$userrow['alipay_fee']),2)+round($res['wxpay']*(1-$userrow['wxpay_fee']),2)+round($res['qqpay']*(1-$userrow['qqpay_fee']),2)+round($res['cqpay']*(1-$userrow['fourpay_fee']),2)).'</b><br/>'.strval(round($res['alipay']*(1-$userrow['alipay_fee']),2)).'/'.strval(round($res['wxpay']*(1-$userrow['wxpay_fee']),2)).'/'.strval(round($res['qqpay']*(1-$userrow['qqpay_fee']),2)).'/'.strval(round($res['cqpay']*(1-$userrow['fourpay_fee']),2)).'</td><td>'.$status.'</td><td>'.$res['memos'].'</td></tr>';	
}
?>
		  </tbody>
        </table>
      </div>

	<footer class="panel-footer">
<?php
echo'<ul class="pagination">';
$first=1;
$prev=$page-1;
$next=$page+1;
$last=$pages;
if ($page>1)
{
echo '<li><a href="settle.php?page='.$first.$link.'">首页</a></li>';
echo '<li><a href="settle.php?page='.$prev.$link.'">&laquo;</a></li>';
} else {
echo '<li class="disabled"><a>首页</a></li>';
echo '<li class="disabled"><a>&laquo;</a></li>';
}
for ($i=1;$i<$page;$i++)
echo '<li><a href="settle.php?page='.$i.$link.'">'.$i .'</a></li>';
echo '<li class="disabled"><a>'.$page.'</a></li>';
if($pages>=10)$pages=10;
for ($i=$page+1;$i<=$pages;$i++)
echo '<li><a href="settle.php?page='.$i.$link.'">'.$i .'</a></li>';
echo '';
if ($page<$pages)
{
echo '<li><a href="settle.php?page='.$next.$link.'">&raquo;</a></li>';
echo '<li><a href="settle.php?page='.$last.$link.'">尾页</a></li>';
} else {
echo '<li class="disabled"><a>&raquo;</a></li>';
echo '<li class="disabled"><a>尾页</a></li>';
}
echo'</ul>';
#分页
?>
</footer>
	</div>
</div>
    </div>
  </div>

<?php include 'foot.php';?>