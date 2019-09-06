<?php
/**
 * 报表统计
 **/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '报表统计系统';
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
        $column = daddslashes($_REQUEST['column']);
        $value = daddslashes($_REQUEST['value']);
        $begintime = daddslashes($_REQUEST['begintime']);
        $endtime = daddslashes($_REQUEST['endtime']);
        if($column == 'all'){
            $value = '';
        }
        break;
    case 'edit':
        break;
    case 'save':
        break;
    default:
        $today = date("Y-m-d");
        $begintime = $today." 00:00:00";
        $endtime = $today." 23:59:59";

        $query = array();
        if(isset($_REQUEST['pid']) && $_REQUEST['pid'] != ''){
            $pid = daddslashes($_REQUEST['pid']);
            $query['pid'] = $pid;
        }else{
            $pid = '';
        }

        if(isset($_REQUEST['mobile_url']) && $_REQUEST['mobile_url'] != ''){
            $mobile_url = daddslashes($_REQUEST['mobile_url']);
            $query['mobile_url'] = $mobile_url;
        }else{
            $mobile_url = "";
        }

        if(isset($_REQUEST['trade_no']) && $_REQUEST['trade_no'] != ''){
            $trade_no = daddslashes($_REQUEST['trade_no']);
            $query['trade_no'] = $trade_no;
        }else{
            $trade_no = "";
        }

        if(isset($_REQUEST['out_trade_no']) && $_REQUEST['out_trade_no'] != ''){
            $out_trade_no = daddslashes($_REQUEST['out_trade_no']);
            $query['out_trade_no'] = $out_trade_no;
        }else{
            $out_trade_no = "";
        }

        if(isset($_REQUEST['name']) && $_REQUEST['name'] != ''){
            $name = daddslashes($_REQUEST['name']);
            $query['name'] = $name;
        }else{
            $name = "";
        }

        if(isset($_REQUEST['money']) && $_REQUEST['money'] != ''){
            $money = daddslashes($_REQUEST['money']);
            $money = explode('-',$money);
            if(count($money) == 1){
                $money_min = number_format($money[0],2,'.','');
                $money_max = number_format($money[0],2,'.','');
            }elseif(count($money) == 2){
                $money_min = number_format($money[0],2,'.','');
                $money_max = number_format($money[1],2,'.','');
            }
            $query2 = " `money` >= '{$money_min}' AND `money` <= '{$money_max}' ";
            $money = daddslashes($_REQUEST['money']);
        }else{
            $money = "";
        }
        if(count($query) == 0){
            $sql = '';
        }else{
            $sql = " 1 = 1 ";
            foreach($query as $key => $value){
                $sql .= " AND `{$key}` = '{$value}' ";
            }
        }

        if(isset($query2)){
            if($sql == ''){
                $sql = $query2;
            }else{
                $sql .= " AND ".$query2;
            }
        }


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

        if($sql == ''){
            $action = 'all';
        }

        //获取查询类型
        if(isset($_REQUEST['time_type']) && $_REQUEST['time_type'] != ''){
            $time_type = daddslashes($_REQUEST['time_type']);
            if($time_type != 'addtime' && $time_type != 'endtime'){
                $time_type = 'addtime';
            }
        }else{
            $time_type = 'endtime';//默认按订单完成时间
        }

        $time_type_selected = array("addtime" => "","endtime" => "");
        $time_type_selected[$time_type] = "selected";

        if($action == 'search'){
            $sql = " {$sql} AND `{$time_type}` >= '{$begintime}' AND `{$time_type}` <= '{$endtime}' ";
//            if($column == 'mobile_url' && $value == ''){
//                $sql=" `{$column}` is null AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' ";
//            }
            $sql2 = "SELECT * from `pay_order` WHERE `status` != 9 AND {$sql}";


            $numrows = $DB->query($sql2)->rowCount();
            $con = "包含 查询条件 的共有 <b>{$numrows}</b> 条订单";
        }else{
            $sql = " `{$time_type}` >= '{$begintime}' AND `{$time_type}` <= '{$endtime}'";
            $sql2 = "SELECT * from `pay_order` WHERE `status` != 9 AND {$sql}";
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
            $csv_title = ["订单号","商户订单号","商户号","商品名称","金额","支付方式","通道地址","浏览器类型","创建时间","完成时间","状态"];
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
                    $res['mobile_url'],
                    $mobile,
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
        $tj['total'] = 0;//总额
        $tj['pay'] = 0;//已支付
        $tj['unpay'] = 0;//未支付
        $tj['alipay'] = 0;//支付宝
        $tj['wechat'] = 0;//微信
        $tj['others'] = 0;//其他
        while($result){
            $tj['total'] += $result['money'];
            if($result['status'] == 1){
                $tj['pay'] += $result['money'];
                switch($result['type']){
                    case 'alipay2':
                    case 'alipay2qr':
                        $tj['alipay'] += $result['money'];
                        break;
                    case 'wechat2':
                    case 'wechat2qr':
                        $tj['wechat'] += $result['money'];
                        break;
                    default:
                        $tj['others'] += $result['money'];
                        break;
                }
            }elseif($result['status'] == 0){
                $tj['unpay'] += $result['money'];
            }
            $result = $results->fetch();
        }

        $tj_echo = "<div style='padding-left: 20px;'><span style='color: blue;font-size: 14px;'>总发起支付金额：{$tj['total']}元。<br>
已支付：{$tj['pay']}，其中支付宝付款：{$tj['alipay']}元，微信付款：{$tj['wechat']}元，其他支付方式付款：{$tj['others']}元。<br>
未支付：{$tj['unpay']}<br></span></div>";
        $tj_echo = "<div style='padding-left: 20px;'><span style='color: #bbb;font-size: 12px;'>总发起支付金额：{$tj['total']}元。<br>
已支付：{$tj['pay']}，其中支付宝付款：{$tj['alipay']}元，微信付款：{$tj['wechat']}元，其他支付方式付款：{$tj['others']}元。<br>
未支付：{$tj['unpay']}<br></span></div>";
        echo $header;
        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">{$title}</h1>
</div>
<div class="wrapper-md control">
    <div class="panel panel-default">
        <div class="panel-heading font-bold">
            {$title}&nbsp;($numrows)
        </div>
        <form action="{$self_url}" method="GET" class="form-horizontal">
            <input type="hidden" name="action" value="search">
            <div class="form-group">
                <label class="col-sm-1 control-label">搜索条件：</label>
                <div class="col-sm-11">
                    <div class="row">
                      <div class="col-md-1">
                            商户号：<input class="form-control" placeholder="" type="text" name="pid" value="{$pid}">
                          </div>
                          <div class="col-md-3">
                            通道地址：<input class="form-control" placeholder="" type="text" name="mobile_url" value="{$mobile_url}">
                          </div>
                          <div class="col-md-2">
                            订单号：<input class="form-control" placeholder="" type="text" name="trade_no" value="{$trade_no}">
                          </div>
                          <div class="col-md-2">
                            商户订单号：<input class="form-control" placeholder="" type="text" name="out_trade_no" value="{$out_trade_no}">
                          </div>
                          <div class="col-md-2">
                            商品名称：<input class="form-control" placeholder="" type="text" name="name" value="{$name}">
                          </div>
                          <div class="col-md-2">
                            金额：<input class="form-control" placeholder="如：1000-3000" type="text" name="money" value="{$money}">
                          </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="form-group">
              <div class="col-sm-2 control-label">查询类型：
                  <select name="time_type">
                      <option value="addtime" {$time_type_selected['addtime']}>按订单创建时间</option>
                      <option value="endtime" {$time_type_selected['endtime']}>按订单完成时间</option>
                  </select>
              </div>
            <div class="col-sm-10">
                <div class="row">
                    <div class="col-md-4">
                    开始时间：<input class="form-control" type="datetime" name="begintime" value="{$begintime}">
                    </div>
                    <div class="col-md-4">
                    结束时间：<input class="form-control" type="datetime" name="endtime" value="{$endtime}">
                    </div>
                    <div class="col-md-2">
                    <br><button type="submit" class="btn btn-primary">查 &emsp; 询</button>
                    </div>
                </div>
            </div>

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
          <thead><tr><th>订单号/商户订单号</th><th>商户号/网站域名</th><th>商品名称/金额</th><th>支付方式</th><th>创建时间/完成时间</th><th>支付状态</th></tr></thead>
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

        $sql2 = "SELECT * FROM `pay_order` WHERE {$sql} order by `addtime` DESC limit $offset,$pagesize";
        $rs = $DB->query($sql2);
        while($res = $rs->fetch()){
            $url = getdomain($res['notify_url']);
            $url = "";
            switch($res['status']){
                case 0:
                    $status = "<font color='red'>未完成</font>";
                    //$status .= "<br><a href='{$self_url}?my=edit&trade_no={$res['trade_no']}' style='color: red;'>修改状态</a>";
                    break;
                case 1:
                    $status = "<font color='green'>已完成</font>";
                    break;
                case 2:
                    $status = "<font color='black'>已关闭</font>";
            }
            echo "<tr><td><b>{$res['trade_no']}</b><br/>{$res['out_trade_no']}</td><td>{$res['pid']}<br/>{$url}</td><td>{$res['name']}<br/>￥{$res['money']}</td><td>{$res['type']}<br>{$res['mobile_url']}</td><td>{$res['addtime']}<br/>{$res['endtime']}</td><td>{$status}</td></tr>";
        }
        echo "
    </tbody>
  </table>
</div>
";

include_once __DIR__.DIRECTORY_SEPARATOR.'../includes/page.class.php';
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