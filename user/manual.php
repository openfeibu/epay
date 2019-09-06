<?php
/**
 * 手工修正系统
 **/
include("../includes/common.php");
if(!$_SESSION['userid']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
if($_SESSION['agentuuid'] != '1'){
    exit("<script language='javascript'>window.history.go(-1) ;</script>");
}
$userid = $_SESSION['userid'];
//非结算算用，退出。
if($userrow['type'] != '2'){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

$title = '手工修正系统';
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
//查询管理员充值余额，余额不足则停止运行本程序。
// $credit = 6500;
// $sql = "SELECT * FROM `pay_recharge` WHERE `id` = '0' AND `balance` < '{$credit}' ";
// $result = $DB->query($sql)->fetch();
// if($result){
//     echo "&emsp;余额不足，请及时充值。";
//     exit();
// }
echo $header;

isset($_REQUEST['action']) ? $action = daddslashes($_REQUEST['action']) : $action = '';
switch($action){
    case 'add':
        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">创建新订单</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      创建新订单
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">订单号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="trade_no" value="">
          </div>
        </div>
        
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
              <input class="btn btn-success form-control" type="submit" value="确定添加">
              <br>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
            <input value="返回" class="btn btn-primary form-control" onclick="window.location.href='{$self_url}'" type="button">
            <br>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
EOF;
        break;
    case 'issue_order':
        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">补单</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      补单
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=issue_order_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-3 control-label">原订单号<span style="color: red;">*</span>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="trade_no" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">订单说明<span style="color: blue">（此说明将会添加到订单号中，如填写1，代表第1次补单。）<span style="color: red;">*</span></span>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="addon" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">备注：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-4">
              <input class="btn btn-success form-control" type="submit" value="确定补单">
              <br>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-4">
            <input value="返回" class="btn btn-primary form-control" onclick="window.location.href='{$self_url}'" type="button">
            <br>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

EOF;

        break;
    case 'edit2':
        break;
    case 'edit':
        if(!isset($_REQUEST['trade_no']) || $_REQUEST['trade_no'] == ''){
            break;
        }
        $trade_no = daddslashes($_REQUEST['trade_no']);
        if($userrow["uid"]=='1') {
            $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' AND `uid` = '{$userid}'; ";
        }else{
            $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' AND `pid` = '{$userid}'; ";
        }
        $order = $DB->query($sql)->fetch();
        $url = getdomain($order['notify_url']);
        $status = ['','',''];
        switch ($order['status']){
            case '0':
                $status[0] = "selected";
                break;
            case '1':
                $status[1] = "selected";
                break;
            default:
                $status[2] = "selected";
                break;
        }
        if($order['endtime'] == '' || $order['endtime'] == '0000-00-00 00:00:00' || $order['endtime'] == null){
            $endtime = date("Y-m-d H:i:s");
        }else{
            $endtime = $order['endtime'];
        }
        echo <<< EOF2
            <div class="bg-light lter b-b wrapper-md">
                <h1 class="m-n font-thin h3">修改订单信息</h1>
            </div>
            <div class="wrapper-md control">
<form action="{$self_url}?action=save" method="POST">
    <div class="form-group">
        <label>订单号/商户订单号：</label>{$order['trade_no']}/{$order['out_trade_no']}
        <input type="hidden" class="form-control" name="trade_no" value="{$order['trade_no']}">
        <input type="hidden" class="form-control" name="out_trade_no" value="{$order['out_trade_no']}">
    </div>

    <div class="form-group">
        <label>商户号/网站域名：</label>{$order['pid']}/{$url}
        <input type="hidden" class="form-control" name="out_trade_no" value="{$order['out_trade_no']}">
    </div>

    <div class="form-group">
        <label>	商品名称/金额：</label>{$order['name']}/￥{$order['money']}
        <input type="hidden" class="form-control" name="name" value="{$order['name']}">
    </div>

    <div class="form-group">
        <label>	支付方式：</label>{$order['type']}
        <input type="hidden" class="form-control" name="type" value="{$order['type']}">
    </div>

    <div class="form-group">
        <label>	创建时间：</label>{$order['addtime']}
        <input type="hidden" class="form-control" name="addtime" value="{$order['addtime']}">
    </div>

    <div class="form-group">
        <label>	完成时间：</label>
        <input type="datetime" class="form-control" name="endtime" value="{$endtime}">
    </div>

    <div class="form-group">
        <label>	支付状态：</label><br>
        <select class="form-control" name="status">
            <option value="0" {$status[0]}>0_未支付</option>
            <option value="1" {$status[1]}>1_已完成</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>	提现密码（安全密码）：</label>
        <input type="password" class="form-control" name="twoauth" value="">
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-4">
            <input type="submit" class="btn btn-primary btn-block" value="保存修改">
        </div>
    </div>
    
</form>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-4">
            <input value="返回" class="btn form-control" onclick="window.location.href='{$self_url}?action=search&column=trade_no&value={$trade_no}'" type="button"><br>
        </div>
    </div>
EOF2;
        exit();
        break;
    case 'save':
        $status = daddslashes($_REQUEST['status']);
        $trade_no = daddslashes($_REQUEST['trade_no']);
        //$endtime = daddslashes($_REQUEST['endtime']);
        $endtime = date("Y-m-d H:i:s"); //完成时间取当前时间，旧方法由用户自定不接受
        $buyer = date("Y-m-d H:i:s")."@".real_ip()."@agent@manual";
        $twoauth = daddslashes($_REQUEST['twoauth']);
        if($twoauth != $userrow['cash_pwd']){
            exit("<script language='javascript'>alert('提现密码错误，修改订单状态失败。');history.go(-1);</script>");
        }
        if($userrow["uid"]=='1') {
            $sql = "UPDATE `pay_order` SET `status` = '{$status}', `endtime` = '{$endtime}', `buyer` = '{$buyer}' WHERE `trade_no` = '{$trade_no}'  AND `uid` = '{$userid}'; ";
        }else{
            $sql = "UPDATE `pay_order` SET `status` = '{$status}', `endtime` = '{$endtime}', `buyer` = '{$buyer}' WHERE `trade_no` = '{$trade_no}'  AND `pid` = '{$userid}'; ";
        }
        if($DB->query($sql)){
            exit("<script language='javascript'>alert('保存成功。');history.go(-1);</script>");
        }else{
            exit("<script language='javascript'>alert('保存失败。');history.go(-1);</script>");
        }
        break;
    case 'add_submit':

        break;
    case 'issue_order_submit':
        if(isset($_REQUEST['trade_no']) && isset($_REQUEST['addon']) && $_REQUEST['addon'] != ''){
            $trade_no = daddslashes($_REQUEST['trade_no']);
            $addon = daddslashes($_REQUEST['addon']);
        }
        isset($_REQUEST['note1']) ? $note1 = daddslashes($_REQUEST['note1']) : $note1 = "";
        //查找订单信息
        if($userrow["uid"]=='1') {
            $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}'  AND `uid` = '{$userid}' LIMIT 1";
        }
        else{
            $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}'  AND `pid` = '{$userid}' LIMIT 1";
        }
        $res = $DB->query($sql)->fetch();
        if(!$res){
            exit("<script language='javascript'>alert('找不到原订单号，请重新输入。');history.go(-1);</script>");
        }

        //根据订单信息，创建新订单
        $trade_no .= "@".$addon;
        $out_trade_no = $res['out_trade_no']."@".$addon;
        $notify_url = "http://127.0.0.1";
        $return_url = $res['return_url'];
        $type = $res['type'];
        $pid = $res['pid'];
        $uid = $res['uid'];
        $addtime = date("Y-m-d H:i:s");
        $name = $res['name'];
        $money = $res['money'];
        $status = 0;
        $data_json = $res['data'];
        $sql = "INSERT INTO `pay_order` (`trade_no`,`out_trade_no`,`notify_url`,`return_url`,`type`,`pid`,`uid`,`addtime`,`name`,`money`,`status`, `data`,`note1`) values ('{$trade_no}','{$out_trade_no}','{$notify_url}','{$return_url}','{$type}','{$pid}','{$userrow['uid']}','{$date}','{$name}','{$money}','0', '{$data_json}', '{$note1}' )";
        if(!$DB->query($sql)){
            exit("<script language='javascript'>alert('创建订单失败（原因：补单说明重复），请返回重试！');history.go(-1);</script>");
        }
        //创建订单成功，返回到手工修正页面
        exit("<script language='JavaScript'>alert('创建订单成功！');window.location.href='{$self_url}?action=search&column=trade_no&value={$trade_no}';</script>");
        break;
    default:

        $column_selected = [
            'all' => '',
            'pid' => '',
            'mobile_url' => '',
            'trade_no' => '',
            'out_trade_no' => '',
            'name' => '',
            'money' => '',
            'type' => '',
        ];


        if($action == 'search'){
            $column = daddslashes($_REQUEST['column']);
            $value = daddslashes($_REQUEST['value']);
            $column_selected[$column] = "selected";
            if($column == 'money'){
                $value = number_format($value,2);
            }elseif($column == 'trade_no' || $column == 'out_trade_no'){
                //采用模糊搜索
                $sql = " `{$column}` = '{$value}' OR `{$column}` LIKE '{$value}@%' ";
            }else{
                $sql = " `{$column}` = '{$value}'";
            }
            if($userrow["uid"]=='1'){
                $sql2 = "SELECT * FROM `pay_order` WHERE ({$sql}) AND `uid` = '{$userid}'";
            }
            else{
                $sql2 = "SELECT * FROM `pay_order` WHERE ({$sql}) AND `pid` = '{$userid}'";
            }
            $numrows = $DB->query($sql2)->rowCount();
            $con = "包含 <span style='color: blue'>{$value}</span> 共有 <b>{$numrows}</b> 条订单";
        }else{
            $column = "trade_no";
            $value = "";
            $sql = " false";
            $sql = " `{$column}` = '{$value}'";
            if($userrow["uid"]=='1'){
                $sql2 = "SELECT * FROM `pay_order` WHERE ({$sql}) AND `uid` = '{$userid}'";
            }
            else{
                $sql2 = "SELECT * FROM `pay_order` WHERE ({$sql}) AND `pid` = '{$userid}'";
            }
            $numrows = $DB->query($sql2)->rowCount();
            $con = "共有 <b>{$numrows}</b> 条订单";
        }
        $link = $_REQUEST;
        unset($link['page']);
        $link = http_build_query($link);
        $link = "&".$link;
        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">{$title}</h1>
</div>
<div class="wrapper-md control">
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
                </select>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="value" placeholder="搜索内容" value="{$value}">
            </div>
                <button type="submit" class="btn btn-primary">查询</button>&nbsp;<a href="{$self_url}?action=add" class="btn btn-success" style="display: none;">创建新订单</a>&nbsp;<a href="{$self_url}?action=issue_order" class="btn btn-success">补单</a>
        </form>
{$con}

      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>订单号/商户订单号</th><th>商户号</th><th>商品名称/金额</th><th>支付方式</th><th>创建时间/完成时间</th><th>状态</th></tr></thead>
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
        while($res = $rs->fetch()){
            $url = getdomain($res['notify_url']);
            $url = "";
            switch($res['status']){
                case 0:
                    $status = "<font color='blue'>未完成</font>";
                    $status .= "<br><a href='{$self_url}?action=edit&trade_no={$res['trade_no']}' style='color: red;'>修改状态</a>";
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

            //隐藏隧道地址
            $mobile_url = $res['mobile_url'];
            $mobile_url = str_replace("http://","",$mobile_url);
            $mobile_url = str_replace("th","",$mobile_url);
            $mobile_url = str_replace(".s1.natapp.cc/","",$mobile_url);
            echo "<tr><td><b>{$res['trade_no']}</b><br/>{$res['out_trade_no']}</td><td>{$res['pid']}<br/>{$url}</td><td>{$res['name']}<br/>￥{$res['money']}</td><td>{$res['type']}<br>{$mobile_url}<br>{$mobile}</td><td>{$res['addtime']}<br/>{$res['endtime']}</td><td>{$status}</td></tr>";
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
        echo "</ul>";
#分页
        echo "</div>";
        break;
}
?>


    </div>
  </div>
  <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>