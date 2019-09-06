<?php
/**
 * 订单记录
 **/
include("../includes/common.php");
//require_once '../includes/api/debug.php';
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$admin_uuid = $_SESSION['admin_uuid'];
$title = '订单回收站';
include './head.php';
if($_SESSION['admin_id']!='1'){
    exit();
}
$today = date("Y-m-d");
$begintime = $today." 00:00:00";
$endtime = $today." 23:59:59";
$self_url = $_SERVER['PHP_SELF'];
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
$link = $_REQUEST;
unset($link['page']);
$link = http_build_query($link);
$link = "&".$link;
$sql2 = "SELECT * FROM `pay_order` WHERE `status` = 9 AND  addtime between '{$begintime}' and '{$endtime}'";
$result=$DB->query("SELECT count(*) FROM `pay_order` WHERE `status` = 9 AND  addtime between '{$begintime}' and '{$endtime}'")->fetch();
$numrows =$result[0];
$con = "共有 <b>{$numrows}</b> 条订单";
        print <<< EOF
<div id="content" class="app-content" role="main">
    <div class="app-content-body ">
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
                        开始时间：<input class="form-control" type="datetime" name="begintime" value="{$begintime}">
                        结束时间：<input class="form-control" type="datetime" name="endtime" value="{$endtime}">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">查询</button>
                    </div>
                </form>
                {$con}
                <br>
    
                <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>订单信息</th><th>商品名称/金额</th><th>创建时间</th><th>状态</th><th>操作</th></tr></thead>
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
                    $status = "<font color='blue'>等待支付</font>";
                    $operate = "<a href='manual.php?action=search&column=trade_no&value={$res['trade_no']}'><button>我已收款</button></a>";
                    break;
                case 1:
                    $status = "<font color='green'>已完成【已通知】</font>";
                    $operate = "
                        <span>
							<a href='../api/return_url.php?trade_no={$res['trade_no']}&notify=yes' style='color: green;' target='_blank'>重发通知（异步）</a>
							<br><a href='../api/return_url.php?trade_no={$res['trade_no']}' style='color: green;' target='_blank'>重发通知（同步）</a>
						</span> ";
                    break;
                case 9:
                    $status = "<font color='black'>已关闭</font>";
                    $operate = "";
                    break;
                default:
                    $status = "";
                    $operate = "";
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
            if($res['endtime'] == "0000-00-00 00:00:00"){
                $res['endtime'] = "";
            }

            //隐藏隧道地址
            $mobile_url = $res['mobile_url'];
            $mobile_url = str_replace("http://","",$mobile_url);
            $mobile_url = str_replace("wang","",$mobile_url);
            $mobile_url = str_replace(".s1.natapp.cc/","",$mobile_url);
            print <<< EOF
                    <tr>
                        <td>
                            商户号：{$res['pid']}<br/>
                            订单号：<b>{$res['trade_no']}</b><br/>
                            收款账号：【{$mobile_url}】<br/>
                            商户订单号：{$res['out_trade_no']}
                        </td>
                        <td>
                            {$res['name']}<br/>
                            ￥{$res['money']}<br>
                            <!--手续费：￥<br>-->
                            {$url}
                        </td>
                        <td>{$res['addtime']}</td>
                        <td>
                            {$status}<br>
                            <span style="color: green">{$res['endtime']}</span><br>
                            [{$res['type']}]<br>
                            [{$mobile}]
                        </td>
                        <td>
                        {$operate}
                        </td>
                    </tr>
EOF;
        }
        echo "
                </tbody>
            </table>
            </div>
";

        require '../includes/page.class.php';
        echo $tj_echo;
#分页
        echo "
        </div>";
?>
    </div>
    </div>

    <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>