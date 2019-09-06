<?php
/**
 * 充值记录
 **/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '充值记录';
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
                $isorderpage = 1;
                if(!empty($_GET['type']) && !empty($_GET['kw'])){
                    $kw = daddslashes($_GET['kw']);
                    if($_GET['type'] == 1) $sql = " and trade_no='$kw'";
                    elseif($_GET['type'] == 2) $sql = " and out_trade_no='$kw'";
                    elseif($_GET['type'] == 3) $sql = " and name='$kw'";
                    elseif($_GET['type'] == 4) $sql = " and money='$kw'";
                    elseif($_GET['type'] == 5) $sql = " and type='$kw'";
                    else $sql = "";
                    $link = '&type='.$_GET['type'].'&kw='.$_GET['kw'];
                }else{
                    $sql = "";
                    $link = '';
                }
                $pid = 0;
                $query = "SELECT * FROM `pay_recharge_record` WHERE `status` > 0 AND `pid` = '{$pid}'{$sql}";
                $query = "SELECT * FROM `pay_recharge_record` WHERE `status` > 0 {$sql}";
                $numrows = $DB->query($query)->rowCount();
                $pagesize = 200;
                $pages = intval($numrows / $pagesize);
                if($numrows % $pagesize){
                    $pages++;
                }
                if(isset($_GET['page'])){
                    $page = intval($_GET['page']);
                }else{
                    $page = 1;
                }
                $offset = $pagesize * ($page - 1);
                //$sql = "SELECT * FROM `pay_recharge_record` WHERE `status` >= 0 AND `pid` = '{$pid}'{$sql} order by addtime desc limit $offset,$pagesize";
                $sql = "SELECT * FROM `pay_recharge_record` WHERE `status` >= 0 {$sql} order by addtime desc limit $offset,$pagesize";
                $list = $DB->query($sql)->fetchAll();


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
	      <form action="recharge_record.php" method="GET" class="form-inline">
	        <div class="form-group">
			<select class="input-sm form-control" name="type">
			  <option value="1">交易号</option>
			  <option value="2">商户订单号</option>
			  <option value="3">商品名称</option>
			  <option value="4">商品金额</option>
			  <option value="5">支付方式</option>
			</select>
		    </div>
			<div class="form-group">
			  <input type="text" class="input-sm form-control" name="kw" placeholder="搜索内容">
			</div>
			 <div class="form-group">
				<button class="btn btn-sm btn-default" type="submit">搜索</button>
			 </div>
		  </form>
		</div>
      </div>
		<div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>交易号/商户订单号</th><th>商户ID</th><th>名称</th><th>充值金额</th><th>支付方式</th><th>创建时间/完成时间</th><th>状态</th></tr></thead>
          <tbody>
EOF;

                $order_today = 0;
                $wx_today = 0;
                $alipay_today = 0;
                $QQpay_today = 0;
                foreach($list as $res){
                    $nowrec_money = 0;
                    if($res['type'] == "wxpay"){
                        $wx_today += $res['money'];
                        $nowrec_money = $res['money'] * $userrow['wxpay_fee'];
                    }
                    if($res['type'] == "alipay"){
                        $alipay_today += $res['money'];
                        $nowrec_money = $res['money'] * $userrow['alipay_fee'];
                    }
                    if($res['type'] == "qqpay"){
                        $QQpay_today += $res['money'];
                        $nowrec_money = $res['money'] * $userrow['qqpay_fee'];
                    }
                    if($res['type'] == "cqpay"){
                        $QQpay_today += $res['money'];
                        $nowrec_money = $res['money'] * $userrow['fourpay_fee'];
                    }
                    $order_today += $res['money'];

                    if($res['status'] == 1){
                        $status = "<font color=green>完成</font>";
                    }else{
                        $status = "<font color=red>未完成</font>";
                    }
                    if(isset($_REQUEST['wmf'])){
                        $buyer = $res['buyer'];
                    }else{
                        $buyer = "";
                    }
                    echo "<tr><td>{$res['trade_no']}<br/>{$res['out_trade_no']}</td><td>{$res['pid']}</td><td>{$res['name']}<br>{$buyer}</td><td>￥ <b>{$res['money']}</b></td><td>{$res['type']}</td><td>{$res['addtime']}<br />{$res['endtime']}</td><td>{$status}</td></tr>";
                }
                print "
		  </tbody>
        </table>
      </div>

	<footer class=\"panel-footer\">
";
                require '../includes/page.class.php';
                #分页
                ?>
                </footer>
            </div>
            总计：<?php echo $order_today; ?>元，其中微信支付<?php echo $wx_today; ?>元，支付宝支付<?php echo $alipay_today; ?>
            元，QQ钱包<?php echo $QQpay_today; ?>元。
        </div>
    </div>
    </div>
    <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>