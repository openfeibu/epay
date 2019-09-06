<?php
	/**
	 * 订单记录
	 **/
	include("../includes/common.php");
	if(!isset($_SESSION['userid'])){
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
	$title = '订单记录';
	if(!isset($_REQUEST['excel'])){
		include './head.php';
	}
	$self_url = $_SERVER['PHP_SELF'];
	// require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";

	$header = '
  <!-- content -->
  <div id="content" class="app-content" role="main">
      <div class="app-content-body ">
';


	isset($_REQUEST['action']) ? $action = daddslashes($_REQUEST['action']) : $action = '';
	switch($action){
		case 'edit2':
		case 'edit':
			break;
		case 'save':
			break;
		default:
			$today = date("Y-m-d");
			$begintime = $today." 00:00:00";
			$endtime = $today." 23:59:59";

			$column_selected = [
				'all'          => '',
				'pid'          => '',
				'mobile_url'   => '',
				'trade_no'     => '',
				'out_trade_no' => '',
				'name'         => '',
				'money'        => '',
				'type'         => '',
			];


			if(!isset($_REQUEST['begintime']) || $_REQUEST['begintime'] == ''){
				$_REQUEST['begintime'] = $begintime;
			}else{
				$begintime = daddslashes($_REQUEST['begintime']);
			}
			if(!isset($_REQUEST['endtime']) || $_REQUEST['endtime'] == ''){
				$_REQUEST['endtime'] = $endtime;
			}else{
				$endtime = daddslashes($_REQUEST['endtime']);
			}

			$table_name="`pay_order`"; //默认表名
			$is_history = date("Y-m-d h:i:s",strtotime("-31 day"));
			if($_REQUEST['begintime'] <$is_history || $_REQUEST['endtime']<$is_history) //根据时间来判断是不是要调用历史表
			{
				$table_name="`pay_order_history`";
			}

			if($action == 'search' && $_REQUEST['value'] != ""){
				$column = daddslashes($_REQUEST['column']);
				$value = daddslashes($_REQUEST['value']);
				$column_selected[$column] = "selected";
				if($column == 'money'){
					//$value = number_format($value,2);
					$sql = " money-money2 = '{$value}' AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' ";
				}else{
					//采用模糊搜索
					$sql = " `{$column}` LIKE '%{$value}%' AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' ";
				}
				$sql2 = "SELECT *,money-money2 as truemoney FROM `pay_order` WHERE `pid` = '{$pid}' AND `status` != 9 AND ({$sql})";
				$numrows = $DB->query($sql2)->rowCount();
				$con = "包含 <span style='color: blue'>{$value}</span> 共有 <b>{$numrows}</b> 条订单";
			}else{
				$sql = " `pid` = '{$pid}' AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}'";
				$sql2 = "SELECT *,money-money2 as truemoney FROM `pay_order` WHERE `status` != 9 AND {$sql}";
				$numrows = $DB->query($sql2)->rowCount();
				$con = "共有 <b>{$numrows}</b> 条订单";
			}
			$link = $_REQUEST;
			unset($link['page']);
			$link = http_build_query($link);
			$link = "&".$link;
			//导出excel表格
			if(isset($_REQUEST['excel']) && $_REQUEST['excel'] == 'yes'){
				require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/autoload.php";
				$csv_title = ["订单号","商户订单号","商户号","商品名称","金额","支付方式","创建时间","完成时间","状态"];
				$csv_result = array();
				$rs = $DB->query($sql2);
				$res = $rs->fetch();
				$t = "\t";
				while($res){
					switch($res['status']){
						case 0:
							$status = "未完成";
							break;
						case 1:
							$status = "已完成";
							break;
						case 2:
							$status = "已关闭";
					}
					$data = $res['data'];
					if($data != ''){
						$data = json_decode($data,true);
						if($data['is_mobile'] == true){
							$mobile = $data['mobile_style'];
						}else{
							$mobile = "PC";
						}
					}else{
						$mobile = '未知';
					}
					$csv_result[] = [
						$t.$res['trade_no'].$t,
						$t.$res['out_trade_no'].$t,
						$res['pid'],
						$res['name'],
						$res['money'],
						$res['type'],
						$res['addtime'],
						$res['endtime'],
						$status,
					];
					$res = $rs->fetch();
				}
				$csv = new \epay\excel();
				$csv->exportToExcel("aaa.csv",$csv_title,$csv_result);
				exit();
			}
			$results = $DB->query($sql2);
			$result = $results->fetch();
			$tj = array(
				"total"  => 0,
				"pay"    => 0,
				"unpay"  => 0,
				"alipay" => 0,
				"wechat" => 0,
				"qqpay"  => 0,
				"fee"    => 0,
				"others" => 0,
			);
			while($result){
				$tj['total'] += $result['money'];
				if($result['status'] == 1){
					$tj['pay'] += $result['money'];
					switch($result['type']){
						case 'alipay2':
						case 'alipay2_url':
						case 'alipay2qr':
							$tj['alipay'] += $result['money'];
							$tj['fee'] += $result['money'] * $userrow['alipay_fee'];
							break;
						case 'wechat2':
						case 'wechat2_url':
						case 'wechat2qr':
							$tj['wechat'] += $result['money'];
							$tj['fee'] += $result['money'] * $userrow['wxpay_fee'];
							break;
						case 'qqpay2':
						case 'qqpay2_url':
						case 'qqpay2qr':
							$tj['qqpay'] += $result['money'];
							$tj['fee'] += $result['money'] * $userrow['qqpay_fee'];
							break;
						default:
							$tj['others'] += $result['money'];
							break;
					}
					$tj['fee'] = round($tj['fee'],2); //四舍五入
				}elseif($result['status'] == 0){
					$tj['unpay'] += $result['money'];
				}
				$result = $results->fetch();
			}

			$tj_echo = "<div style='padding-left: 20px;'><span style='color: blue;font-size: 14px;'>总发起支付金额：{$tj['total']}元。<br>
已支付：{$tj['pay']}元，其中支付宝付款：{$tj['alipay']}元，微信付款：{$tj['wechat']}元，其他支付方式付款：{$tj['others']}元。<br>
未支付：{$tj['unpay']}<br>
手续费：{$tj['fee']}元<br></span></div>";
			$tj_echo = "<div style='padding-left: 20px;'><span style='color: #bbb;font-size: 12px;'>总发起支付金额：{$tj['total']}元。<br>
已支付：{$tj['pay']}元，其中支付宝付款：{$tj['alipay']}元，微信付款：{$tj['wechat']}元，其他支付方式付款：{$tj['others']}元。<br>
未支付：{$tj['unpay']}<br>
手续费：{$tj['fee']}元<br></span></div>";
			isset($msg) ? $msg = "<div class=\"alert alert-info\">{$msg}</div>" : $msg = "";
			echo $header;
			print <<< EOF
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">{$title}</h1>
</div>
<div class="wrapper-md control">
{$msg}
    <div class="panel panel-default">
        <div class="panel-heading font-bold">
            {$title}&nbsp;($numrows)
        </div>
        <form action="{$self_url}" method="GET" class="form-inline">
            <input type="hidden" name="action" value="search">
            <div class="form-group">
                <label>搜索</label>
                <select name="column" class="form-control">
                    <option value="trade_no" {$column_selected['trade_no']}>订单号</option>
                    <option value="out_trade_no" {$column_selected['out_trade_no']}>商户订单号</option>
                    <option value="pid" {$column_selected['pid']}>商户号</option>
                    <option value="name" {$column_selected['name']}>商品名称</option>
                    <option value="money" {$column_selected['money']}>金额</option>
                    <option value="type" {$column_selected['type']}>支付方式</option>
                </select>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="value" placeholder="搜索内容" value="{$value}">
            </div>

            <div class="form-group">
                开始时间：<input class="form-control" type="datetime" name="begintime" value="{$begintime}">
                结束时间：<input class="form-control" type="datetime" name="endtime" value="{$endtime}">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">查询</button>
            </div>
        </form>
<form action="" method="post">
    <input type="hidden" name="excel" value="yes">
    <button type="submit">导出为EXCEL表格</button>
</form>

        {$con}
<br>
<!--{$tj_echo}-->
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>订单号/商户订单号</th><th>名称</th><th>商品金额/服务费</th><th>支付方式</th><th>创建时间/完成时间</th><th>状态</th></tr></thead>
          <tbody>
EOF;

			$pagesize = 30;
			$pages = intval($numrows / $pagesize);
			if($numrows % $pagesize){
				$pages++;
			}
			if(isset($_REQUEST['page'])){
				$page = intval($_REQUEST['page']);
			}else{
				$page = 1;
			}
			$offset = $pagesize * ($page - 1);

			$sql3 = $sql2." order by `addtime` DESC limit $offset,$pagesize";
			$rs = $DB->query($sql3);
			$total_fee = 0;
			while($res = $rs->fetch()){
				$nowrec_money = 0;
				switch($res['type']){
					case 'wechat2':
					case 'wechat2_url':
					case 'wechat2qr':
						$nowrec_money = $res['money'] * $userrow['wxpay_fee'];
						break;
					case 'alipay2':
					case 'alipay2_url':
					case 'alipay2qr':
						$nowrec_money = $res['money'] * $userrow['alipay_fee'];
						break;
					case 'qqpay2':
					case 'qqpay2_url':
					case 'qqpay2qr':
						$nowrec_money = $res['money'] * $userrow['qqpay_fee'];
						break;
					default:
						$nowrec_money = $res['money'] * $userrow['fee'];
						break;
				}
				$nowrec_money2 = round($nowrec_money,2);
				$total_fee += $nowrec_money2;
				switch($res['status']){
					case 0:
						$status = "<font color='red'>未完成</font>";
						break;
					case 1:
						$status = "<font color='green'>已完成</font>";
						break;
					case 2:
						$status = "<font color='black'>已关闭</font>";
						break;
					default:
						$status = "";
						break;
				}
				echo "<tr><td><b>{$res['trade_no']}</b><br/>{$res['out_trade_no']}</td><td>{$res['name']}</td><td>￥ <b>{$res['truemoney']}</b><br/>￥<font color=green>-{$nowrec_money2}</font></td><td>{$res['type']}</td><td>{$res['addtime']}<br />{$res['endtime']}</td><td>{$status}</td></tr>";
			}
			echo "
    </tbody>
  </table>
</div>
";

			require '../includes/page.class.php';
			echo $tj_echo;
#分页
			echo "</div>";
			break;
	}
?>


</div>
</div>
<!-- /content -->
<?php include_once __DIR__."/foot.php" ?>
