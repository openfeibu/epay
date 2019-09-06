<?php
include("../includes/common.php");
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$title='结算记录';
include './head.php';
?>
<?php

$numrows = $DB->query("SELECT * from bc_outmoney WHERE User_id={$pid}")->rowCount();
$pagesize=16;
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

$list=$DB->query("SELECT * FROM bc_outmoney WHERE User_id={$pid} order by out_id desc limit $offset,$pagesize")->fetchAll();

?>
 <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<div class="bg-light lter b-b wrapper-md hidden-print">
  <h1 class="m-n font-thin h3">取现记录</h1>
</div>
<div class="wrapper-md control">
<?php if(isset($msg)){?>
<div class="alert alert-info">
	<?php echo $msg?>
</div>
<?php }?>
	<div class="panel panel-default">
		<div class="panel-heading font-bold">
			取现记录&nbsp;(<?php echo $numrows?>)
		</div>
		<div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>商户号</th><th>开户银行</th><th>银行卡号</th><th>收款人</th><th>提现金额</th><th>提交时间</th><th>完成时间</th><th>预计到账时间</th><th>提现收费</th><th>状态</th></tr></thead>
          <tbody>
<?php
foreach($list as $res){
	if ($res['out_status']==0){
		$status="处理中";
	}
	if ($res['out_status']==1){
		$status="成功取现";
	}

	if ($res['out_status']==2){
		$status="取现失败";
	}
	
	$paytype="-";
	if ($res['paytype']=="XFZF_DF_DZ"){
		$paytype="垫资代付";
	}
	if ($res['paytype']=="XFZF_DF_NO"){
		$paytype="余额代付";
	}	
  //echo '<tr><td>'.substr($res['time'],0,10).'</td><td>￥ <b>'.$res['money'].'</b><br/>'.$res['alipay'].'/'.$res['wxpay'].'/'.$res['qqpay'].'</td><td>￥ <b>'.$res['fee'].'</b></td><td>￥ <b>'.strval($res['money']-$res['fee']).'</b><br/>'.strval(round($res['alipay']*(100-$conf['settle_fee'])/100,2)).'/'.strval(round($res['wxpay']*(100-$conf['settle_fee'])/100,2)).'/'.strval(round($res['qqpay']*(100-$conf['settle_fee'])/100,2)).'</td><td>'.$res['memos'].'</td></tr>';
	echo '<tr><td>'.date("Y-m-d H:i:s",$res['out_id']).'</td><td>￥ <b>'.round($res['out_money'],2).'</td><td>'.$paytype.'</td><td>'.$status.'</td><td>-</td></tr>';	
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