<?php
/**
 * 余额明细
 **/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '余额明细';
if(!isset($_REQUEST['excel'])){
    include './head.php';
}
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";

$header = '
  <!-- content -->
  <div id="content" class="app-content" role="main">
      <div class="app-content-body ">
';


$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
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


        if($action == 'search' && $_REQUEST['value'] != ""){
            $column = daddslashes($_REQUEST['column']);
            $value = daddslashes($_REQUEST['value']);
            $column_selected[$column] = "selected";
            if($column == 'money'){
                $value = number_format($value,2);
                $sql = " `{$column}` = '{$value}' AND `createtime` >= '{$begintime}' AND `createtime` <= '{$endtime}' ";
            }else{
                //采用模糊搜索
                $sql = " `{$column}` LIKE '%{$value}%' AND `createtime` >= '{$begintime}' AND `createtime` <= '{$endtime}' ";
            }
            $sql2 = "SELECT * FROM `pay_balance_history` WHERE `userid` = '{$pid}' AND ({$sql})";
            $numrows = $DB->query($sql2)->rowCount();
            $con = "包含 <span style='color: blue'>{$value}</span> 共有 <b>{$numrows}</b> 条订单";
        }else{
            $sql = " `userid` = '{$pid}' AND `createtime` >= '{$begintime}' AND `createtime` <= '{$endtime}'";
            $sql2 = "SELECT * FROM `pay_balance_history` WHERE {$sql}";
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
            $csv_title = ["序号","编号","类型","入账前余额","金额","入账时间","余额","备注"];
            $csv_result = array();
            $rs = $DB->query($sql2);
            $res = $rs->fetch();
            $t = "\t";
            $i = 1;
            while($res){
                switch($res['type']){
                    case '1':
                        $type_name = "收入";
                        break;
                    case '0':
                        $type_name = "支出";
                        break;
                    default:
                        $type_name = "未知";
                        break;
                }
                $csv_result[] = [
                    $t.$i.$t,
                    $t.$res["trade_no"].$t,
                    $type_name,
                    round($res['balance_before'] / 100,2),
                    round($res['money'] / 100,2),
                    $res['createtime'],
                    round($res['balance'] / 100,2),
                    round($res['note1'] / 100,2),
                ];
                $res = $rs->fetch();
                $i++;
            }
            $csv = new \epay\excel();
            $csv->exportToExcel("aaa.csv",$csv_title,$csv_result);
            exit();
        }

        //统计总数
        $rs = $DB->query($sql2);
        $money_in = 0;
        $money_out = 0;
        while($res = $rs->fetch()){
            $type = $res['type'];
            if($type == 1){
                $money_in += $res['money'];
            }elseif($type == 0){
                $money_out += $res['money'];
            }
        }
        $money_in = round($money_in / 100,2);
        $money_out = round($money_out / 100,2);

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
                        <!--<option value="out_trade_no" {$column_selected['out_trade_no']}>商户订单号</option>-->
                        <!--<option value="pid" {$column_selected['pid']}>商户号</option>-->
                        <!--<option value="name" {$column_selected['name']}>商品名称</option>-->
                        <!--<option value="money" {$column_selected['money']}>金额</option>-->
                        <!--<option value="type" {$column_selected['type']}>支付方式</option>-->
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

      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>编号</th><th>类型</th><th>金额</th><th>入账时间</th><th>余额</th><th>备注</th></tr></thead>
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

        $sql3 = "SELECT * FROM `pay_balance_history` WHERE `userid` = '{$pid}' AND ({$sql}) order by `id` DESC limit $offset,$pagesize";
        $rs = $DB->query($sql3);
        $order_today = 0;
        $wx_today = 0;
        $alipay_today = 0;
        $QQpay_today = 0;
        $money_in2 = 0;
        $money_out2 = 0;
        while($res = $rs->fetch()){
            $nowrec_money = 0;
            $type = $res['type'];
            if($type == 1){
                $money_in2 += $res['money'];
            }elseif($type == 0){
                $money_out2 += $res['money'];
            }
            // switch ($type){
            //     case 'wechat2':
            //     case 'wechat2qr':
            //     case 'wechat':
            //         $wx_today+=$res['money'];
            //         $nowrec_money=$res['money']*$userrow['wxpay_fee'];
            //         break;
            //     case 'alipay2':
            //     case 'alipay2qr':
            //     case 'alipay':
            //         $alipay_today+=$res['money'];
            //         $nowrec_money=$res['money']*$userrow['alipay_fee'];
            //         break;
            //     case 'qqpay':
            //         $QQpay_today+=$res['money'];
            //         $nowrec_money=$res['money']*$userrow['qqpay_fee'];
            //         break;
            //     case 'cqpay':
            //         $QQpay_today+=$res['money'];
            //         $nowrec_money=$res['money']*$userrow['fourpay_fee'];
            //         break;
            //     default:
            //         $nowrec_money=$res['money']*$userrow['fee'];
            //         break;
            // }
            $order_today += $res['money'];

            $nowrec_money2 = round($nowrec_money,2);
            if($res['type'] == 1){
                $status = "<font color=green>收入</font>";
            }else{
                $status = "<font color=red>支出</font>";
            }
            $money = round($res['money'] / 100,2);
            $balance = round($res['balance'] / 100,2);
            $note1 = $res['note1'];
            echo "<tr><td>{$res['trade_no']}</td><td>{$status}</td><td>￥ {$money}</td><td>{$res['createtime']}</td><td>￥ {$balance}</td><td>{$note1}</td></tr>";
        }
        echo "
    </tbody>
  </table>
</div>
<ul class=\"pagination\">
";
        $first = 1;
        $prev = $page - 1;
        $next = $page + 1;
        $last = $pages;
        if($page > 1){
            echo "<li><a href='{$self_url}?page={$first}{$link}'>首页</a></li>";
            echo "<li><a href='{$self_url}?page={$prev}{$link}'>&laquo;</a></li>";
        }else{
            echo "<li class='disabled'><a>首页</a></li>";
            echo "<li class='disabled'><a>&laquo;</a></li>";
        }
        for($i = 1; $i < $page; $i++){
            echo "<li><a href='{$self_url}?page={$i}{$link}'>{$i}</a></li>";
        }
        echo "<li class='disabled'><a>{$page}</a></li>";
        if($pages >= 10){
            // $pages=10;
        }
        for($i = $page + 1; $i <= $pages; $i++){
            echo "<li><a href='{$self_url}?page={$i}{$link}'>{$i}</a></li>";
        }

        if($page < $pages){
            echo "<li><a href='{$self_url}?page={$next}{$link}'>&raquo;</a></li>";
            echo "<li><a href='{$self_url}?page={$last}{$link}'>尾页</a></li>";
        }else{
            echo "<li class='disabled'><a>&raquo;</a></li>";
            echo "<li class='disabled'><a>尾页</a></li>";
        }
        $money_in2 = round($money_in2 / 100,2);
        $money_out2 = round($money_out2 / 100,2);
        $tj_echo = "<span style=\"display: none;\">总计：<?php echo $order_today; ?>元，其中微信支付<?php echo $wx_today; ?>元，支付宝支付<?php echo $alipay_today; ?>元，QQ钱包<?php echo $QQpay_today; ?>元。</span>
		<span style=\"\">收入：<?php echo $money_in; ?>元。支出：<?php echo $money_out?>元</span>";
        echo "</ul>
{$tj_echo}";
#分页
        echo "</div>";
        break;
}
?>


    </div>
    </div>
    <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>