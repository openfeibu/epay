<?php
/**
 * 提现记录
 **/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '提现记录';
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
//查询管理员充值余额，余额不足则停止运行本程序。
// $credit = 6500;
// $sql = "SELECT * FROM `pay_recharge` WHERE `id` = '0' AND `balance` < '{$credit}' ";
// $result = $DB->query($sql)->fetch();
// if($result){
//     echo "&emsp;余额不足，请及时充值。";
//     exit();
// }

isset($_REQUEST['action']) ? $action = daddslashes($_REQUEST['action']) : $action = '';
switch($action){
    case 'searchaaa':
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


        if($action == 'search' && $_REQUEST['column'] != "all"){
            $column = daddslashes($_REQUEST['column']);
            $value = daddslashes($_REQUEST['value']);
            $column_selected[$column] = "selected";
            $sql = " `{$column}` = '{$value}' AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' ";
            $sql2 = "SELECT * FROM `pay_apply` WHERE ({$sql}) AND `pid` = '{$pid}' ";
            $numrows = $DB->query($sql2)->rowCount();
            $con = "包含 <span style='color: blue'>{$value}</span> 共有 <b>{$numrows}</b> 条订单";
        }else{
            $column = "all";
            $value = "";
            $sql = " `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}'";
            $sql2 = "SELECT * FROM `pay_apply` WHERE ({$sql}) AND `pid` = '{$pid}'";
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
            $csv_title = ["ID","商户号","申请时间","结算时间","结算金额","服务费","入账金额","状态"];
            $csv_result = array();
            $rs = $DB->query($sql2);
            $res = $rs->fetch();
            $t = "\t";
            while($res){
                switch($res['status']){
                    case 0:
                        $status = "待结算";
                        break;
                    case 1:
                        $status = "已结算";
                        break;
                    case 2:
                        $status = "已拒绝";
                        break;
                    case 3:
                        $status = "已拒绝并退款到商户余额";
                        break;
                }
                $cash = round($res['cash'] / 100,2);
                $fee = round($res['fee'] / 100,2);
                if($status == 1){
                    $cash_complete = $cash;
                }else{
                    $cash_complete = "";
                }
                $csv_result[] = [
                    $t.$res['id'].$t,
                    $t.$res['pid'].$t,
                    $res['addtime'],
                    $res['endtime'],
                    $cash,
                    $fee,
                    $cash_complete,
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
            "paying" => 0,
            "fee"    => 0,
        );
        while($result){
            $tj['total'] += $result['cash'];
            if($result['status'] == 0){
                $tj['paying'] += $result['cash'];
                $tj['fee'] += $result['fee'];
            }elseif($result['status'] == 1){
                $tj['pay'] += $result['cash'];
                $tj['fee'] += $result['fee'];
            }elseif($result['status'] == 2){
                $tj['unpay'] += $result['cash'];
                $tj['fee'] += $result['fee'];
            }elseif($result['status'] == 3){
                $tj['unpay'] += $result['cash'];
            }
            $result = $results->fetch();
        }
        $tj['total'] = round($tj['total'] / 100,2);
        $tj['pay'] = round($tj['pay'] / 100,2);
        $tj['paying'] = round($tj['paying'] / 100,2);
        $tj['fee'] = round($tj['fee'] / 100,2);

        $tj_echo = "<div style='padding-left: 20px;'><span style='color: #bbb;font-size: 12px;'>总发起提现金额：{$tj['total']}元。<br>
已结算：{$tj['pay']}元。<br>
待结算：{$tj['paying']}<br>
手续费：{$tj['fee']}元<br></span></div>";
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
                    <option value="all" {$column_selected['all']}>所有</option>
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
          <thead><tr><th>ID</th><th>商户号</th><th>开户银行</th><th>银行卡号</th><th>收款人</th><th>提现金额</th><th>提交时间</th><th>完成时间</th><th>预计到账时间</th><th>提现收费</th><th>状态</th></tr></thead>
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

        $sql3 = "SELECT * FROM `pay_apply` WHERE ($sql) AND `pid`='{$pid}' order by `id` DESC limit $offset,$pagesize";
        $rs = $DB->query($sql3);
        isset($msg) ? $msg = "<div class=\"alert alert-info\">{$msg}</div>" : $msg = "";
        while($res = $rs->fetch()){
            switch($res['status']){
                case '0':
                    $status = "<font color=red>待结算</font>";
                    break;
                case '1':
                    $status = "<font color=green>已结算</font>";
                    break;
                case '2':
                    $status = "<font color=red>已拒绝</font>";
                    break;
                case '3':
                    $status = "<font color=red>已拒绝并退款到商户余额</font>";
                    break;
                default:
                    $status = "处理中";
                    break;
            }

            $paytype = "-";
            if($res['paytype'] == "XFZF_DF_DZ"){
                $paytype = "垫资代付";
            }
            if($res['paytype'] == "XFZF_DF_NO"){
                $paytype = "余额代付";
            }
            //echo '<tr><td>'.substr($res['time'],0,10).'</td><td>￥ <b>'.$res['money'].'</b><br/>'.$res['alipay'].'/'.$res['wxpay'].'/'.$res['qqpay'].'</td><td>￥ <b>'.$res['fee'].'</b></td><td>￥ <b>'.strval($res['money']-$res['fee']).'</b><br/>'.strval(round($res['alipay']*(100-$conf['settle_fee'])/100,2)).'/'.strval(round($res['wxpay']*(100-$conf['settle_fee'])/100,2)).'/'.strval(round($res['qqpay']*(100-$conf['settle_fee'])/100,2)).'</td><td>'.$res['memos'].'</td></tr>';
            $cash = round($res['cash'] / 100,2);
            $fee = round($res['fee'] / 100,2);
            echo "<tr><td>{$res['id']}</td><td>{$res['pid']}</td><td>{$res['bank_name']}</td><td>{$res['bank_no']}</td><td>{$res['bank_user']}</td><td>￥ <b>{$cash}元</b></td><td>{$res['addtime']}</td><td>{$res['endtime']}</td><td>T+0到账</td><td>￥{$fee}元</td><td>{$status}</td></tr>";
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