<?php
/**
 * 结算操作
**/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '结算操作';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
?>

  <!-- content -->
  <div id="content" class="app-content" role="main">
      <div class="app-content-body ">


<?php
$count=$DB->query("SELECT * from pay_user where (money>={$conf['settle_money']} or apply=1) and account is not null and username is not null")->rowCount();

$action = $_REQUEST['action'];
switch ($action){
    case 'create':
        $limit='1000';
        $rs=$DB->query("SELECT * from pay_user where (money>={$conf['settle_money']} or apply=1) and account is not null and username is not null and type!=2 and active=1 limit {$limit}");

        $batch=date("Ymd").rand(111,999);
        $i=0;
        $allmoney=0;
        while($row = $rs->fetch()){
            $i++;
            //if($row['apply']==1 && $row['money']<$conf['settle_money']){$fee=$conf['settle_fee'];$row['money']-=$fee;}
            //else $fee=0;
            $fee = round($row['money'] * 0.005, 2);
            if ($fee < 1) $fee = 1;
            if ($fee > 25) $fee = 25;
            $row['money'] = $row['money'] - $fee;
            $DB->exec("update `pay_user` set `money`='0',`apply`='0' where `id`='{$row['id']}'");
            $DB->exec("INSERT INTO `pay_settle` (`pid`, `batch`, `type`, `username`, `account`, `money`, `fee`, `time`, `status`) VALUES ('{$row['id']}', '{$batch}', '{$row['type']}', '{$row['username']}', '{$row['account']}', '{$row['money']}', '{$fee}', '{$date}', '1')");
            $allmoney += $row['money'];
        }
        exit("<script language='javascript'>alert('生成结算列表成功！');window.location.href='./settle.php?action=batch&batch={$batch}&allmoney={$allmoney}';</script>");
        break;
    case 'batch':
        $batch = $_GET['batch'];
        $allmoney = $_GET['allmoney'];
        print <<< EOF
          <form action="download.php" method="get" role="form">
          <input type="hidden" name="action" value="batch" />
		  <input type="hidden" name="batch" value="{$batch}"/>
		  <input type="hidden" name="allmoney" value="{$allmoney}"/>
			<p>当前需要结算的共有{$count}条记录</p>
			<p>批次号：{$batch}</p>
            <p><input type="submit" value="下载CSV文件" class="btn btn-primary form-control"/></p>
          </form>
		  <form action="alipayapi.php" method="get" role="form">
		  <input type="hidden" name="batch" value="{$batch}"/>
		  <input type="hidden" name="allmoney" value="{$allmoney}"/>
			<p><input type="text" name="batch2" value="" placeholder="自定义批次号（如果不需要自定义请留空）" class="form-control"/></p>
            <p><input type="submit" value="批量付款到支付宝账户" class="btn btn-success form-control"/></p>
          </form>
EOF;
        break;
    default:
            print <<< EOF_2
		  <form action="settle.php" method="get" role="form">
		  <input type="hidden" name="action" value="create"/>
			<p>当前需要结算的共有{$count}条记录</p>
            <p><input type="submit" value="立即生成结算列表" class="btn btn-primary form-control"/></p>
          </form>
EOF_2;
        break;
}

?>
        </div>
		<div class="panel-footer">
          <span class="glyphicon glyphicon-info-sign"></span> 结算标准：金额大于<?php echo $conf['settle_money']?>元，或主动申请的（需扣除手续费<?php echo $conf['settle_fee']?>元）<br/>
		  结算列表请勿重复生成，CSV文件可以重复下载！
        </div>
    </div>
</div>
<?php include_once __DIR__."/foot.php" ?>