<?php
/**
 * 用户中心首页
 **/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '用户中心';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
//订单总数
/*采用ajax
$row = $DB->query("SELECT count(*)  as a FROM `pay_order` WHERE `pid` = '{$pid}' OR `uid` = '{$pid}'")->fetch();
$orders = $row[0];
*/

//充值账户余额
$row2 = $DB->query("SELECT income,payment,balance FROM `pay_recharge` WHERE `id` = '{$pid}'")->fetch();
$income = $row2['income'] / 100;
$payment = $row2['payment'] / 100;
$balance = round($row2['balance'],0) / 100;

//账户余额
$row3 = $DB->query("SELECT balance FROM `pay_balance` WHERE `id` = '{$pid}'")->fetch();
if($userrow['type'] == 2){
    $payment = round($row3['balance'],0) / 100;
    $balance = $income - $payment;
}elseif($balance == 0){
    $balance = round($row3['balance'],0) / 100;
    $payment = $income - $balance;
    $payment = 0;
}

$lastday = date("Y-m-d",strtotime("-1 day")).' 00:00:00';
$today_middle = date("Y-m-d").' 12:00:00';
$today_begin = date("Y-m-d").' 00:00:00';
$today_end = date("Y-m-d")."23:59:59";

/*采用ajax
//今日收入
$sql = "SELECT SUM(money) from `pay_order` WHERE `status` = 1 AND (`pid` = '{$pid}' OR `uid` = '{$pid}') AND `endtime`>='{$today_begin}'";
$row = $DB->query($sql)->fetch();
$order_today = $row[0]==null?0:$row[0];

//昨日收入
$sql = "SELECT SUM(money) from `pay_order` WHERE `status` = 1 AND (`pid` = '{$pid}' OR `uid` = '{$pid}') AND `endtime`>='{$lastday}' AND `endtime`<='{$today_begin}'";
$row = $DB->query($sql)->fetch();
$order_lastday = $row[0]==null?0:$row[0];
*/

/*未使用因此注释掉
$rs = $DB->query("SELECT sum(money) as a from `pay_order` WHERE `status` = 1 AND (`pid` = '{$pid}' OR `uid` = '{$pid}') AND type='wxpay' AND DATE_FORMAT(endtime,'%Y-%m-%d')=CURDATE()")->fetch();
$wx_today = $rs[0] == null ? 0 : $rs[0]; //微信今天收入
$rs = $DB->query("SELECT sum(money) as a from `pay_order` WHERE `status` = 1 AND (`pid` = '{$pid}' OR `uid` = '{$pid}') AND left(type,6)='alipay' AND DATE_FORMAT(endtime,'%Y-%m-%d')=CURDATE() ")->fetch();
$alipay_today = $rs[0] == null ? 0 : $rs[0]; //支付宝今天收入

//因QQ不再用于收费，直接置成收入0
//$rs = $DB->query("SELECT sum(money) as a from `pay_order` WHERE (`pid` = '{$pid}' OR `uid` = '{$pid}') AND type='qqpay' AND `status` = 1 and `endtime` >= '{$today_begin}' ")->fetch();
//$QQpay_today = $rs[0]==null?0:$rs[0]; //QQpay今天收入
//$rs = $DB->query("SELECT sum(money) as a from `pay_order` WHERE (`pid` = '{$pid}' OR `uid` = '{$pid}') AND type='cqpay' AND `status` = 1 and `endtime` >= '{$today_begin}' ")->fetch();
//$CQpay_today = $rs[0]==null?0:$rs[0]; ///CQpay今天收入
$QQpay_today = $CQpay_today = 0;
$order_today = $wx_today + $alipay_today + $QQpay_today + $CQpay_today; //统计今天收入
$order_today_left = $wx_today * (1 - $userrow['wxpay_fee']) + $alipay_today * (1 - $userrow['alipay_fee']) + $QQpay_today ** (1 - $userrow['qqpay_fee']) + $CQpay_today * (1 - $userrow['fourpay_fee']); //统计今天费用
*/

//昨天收入
//$json = null;
//$data = $cache->get($pid.'_order_lastday');
//if(empty($cache) || $data['lastday'] != $lastday){
//    //如果无缓存就执行查库
//    $row = $DB->query("SELECT sum(money) as b from `pay_order` where (`pid` = '{$pid}' OR `uid` = '{$pid}') and `status` = 1 and DATE_FORMAT(endtime,'%Y-%m-%d')=TIMESTAMPADD(DAY,-1,CURDATE())")->fetch();
//    $order_lastday = $row[0] == null ? 0 : $row[0];
//    $json["order_firstday"] = $order_lastday;
//    $json["lastday"] = $lastday;
//    $cache->put($pid.'_order_lastday',$json);
//}else{
//    //否则取缓存
//    $order_lastday = $data["order_firstday"];
//}


/*图表功能暂未用到先注释
$rs = $DB->query("SELECT sum(money) as a,max(money) as b,count(*) as c from pay_settle where pid={$pid} and status=1")->fetch();
$settle_money = $rs[0]==null?0:$rs[0];
$max_settle = $rs[1]==null?0:$rs[1];
$rs = $DB->query("SELECT money from pay_settle where pid={$pid} and status=1 limit 10");
$chart = '';
$i = 0;
while($row = $rs->fetch()){
    $chart .= '['.$i.','.$row['money'].'],';
    $i++;
}


$rs = $DB->query("SELECT sum(money) as a from `pay_order` WHERE pid={$pid} and status=0 AND type='wxpay'")->fetch();
$wx_today = $rs[0]; //微信今天收入
$rs = $DB->query("SELECT sum(money) as a from `pay_order` WHERE pid={$pid} and status=0 AND  left(type,6)='alipay'")->fetch();
$alipay_today = $rs[0]; //支付宝今天收入
$rs = $DB->query("SELECT sum(money) as a from `pay_order` WHERE pid={$pid} and status=0 AND type='qqpay'")->fetch();
$QQpay_today = $rs[0]; //QQpay今天收入
$rs = $DB->query("SELECT sum(money) as a from `pay_order` WHERE pid={$pid} and status=0 AND type='cqpay'")->fetch();
$CQpay_today = $rs[0]; //CQpay今天收入
$unsettle_money = $wx_today + $alipay_today + $QQpay_today + $CQpay_today; //统计今天未完成收入
$unsettle_money_multi = $wx_today * (1 - $userrow['wxpay_fee']) + $alipay_today * (1 - $userrow['alipay_fee']) + $QQpay_today ** (1 - $userrow['qqpay_fee']) + $CQpay_today * (1 - $userrow['fourpay_fee']);  //统计今天未完成收入费用

$rs = $DB->query("SELECT sum(money) as a,sum(fee) as b from `pay_settle` where `pid` = '{$pid}' and `time` >= '{$today_begin}' and `time` <= '{$today_middle}' AND `status` = 1")->fetch();
$todaysettle_money = $rs[0];
$todaysettle_fee = $rs[1];

$chart = substr($chart,0,-1);
*/
/*未使用
$p_id = $_SESSION['userid'];
$r = $DB->query("select p.money from pay_user p where id = '{$p_id}'")->fetch();
$s_money = $r['money'];
*/
?>

    <script>

        function index_load_1() {
            $.ajax({                                                                    //获取今天收入
                type: "GET",
                url: "../api/ajax_user_index_api.php",
                data: {
                    token: "order_today",
                    pid:<?php echo $pid; ?>
                },
                success: function (data) {
                    data = eval('(' + data + ')');
                    $("#order_today").html("￥" + data["order_today"]);
                    $("#order_sum").html(data["order_sum"] + "个");
                }
            });
            $.ajax({                                                                    //获取昨天收入
                type: "GET",
                url: "../api/ajax_user_index_api.php",
                data: {
                    token: "order_lastday",
                    pid:<?php echo $pid; ?>
                },
                success: function (data) {
                    data = eval('(' + data + ')');
                    $("#order_lastday").html("￥" + data["order_lastday"]);
                    $("#order_lastday_num").html(data["order_lastday_num"] + "笔");
                }
            });
        }
    </script>
    <div id="content" class="app-content" role="main">
        <div class="app-content-body ">

            <div class="bg-light lter b-b wrapper-md hidden-print">
                <h1 class="m-n font-thin h3"><a href="recharge_result.php"><?php echo $title ?></a></h1>
                <small class="text-muted">欢迎使用<?php echo $conf['web_name'] ?></small>
            </div>
            <div class="wrapper-md control">
                <!-- stats -->
                <div class="row">
                    <div class="col-md-5">
                        <div class="row row-sm text-center">
                            <div class="col-xs-6">
                                <div class="panel padder-v item">
                                    <div class="h1 text-info font-thin h1" id="order_sum">
                                        <img src="../admin/assets/img/loading.gif"
                                                                                style="width: 80px;position: absolute;margin-top: -7%;margin-left: -8%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;个
                                    </div>
                                    <span class="text-muted text-xs">订单总数</span>
                                    <div class="top text-right w-full">
                                        <i class="fa fa-caret-down text-warning m-r-sm"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="block panel padder-v bg-primary item">
                                    <span class="text-white font-thin h1 block">￥<?php echo $income ?></span>
                                    <span class="text-muted text-xs">服务预存总额</span>
                                    <span class="bottom text-right w-full">
<!--                  <i class="fa fa-caret-down text-muted m-r-sm"></i>-->
                </span>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="block panel padder-v bg-info item">
                                    <span class="text-white font-thin h1 block" id="order_today">
                                        ￥&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../admin/assets/img/loading.gif"
                                                                                  style="width: 80px;position: absolute;margin-top: -7%;margin-left: -20%;">
                                    </span>
                                    <span class="text-muted text-xs">今日收入</span>
                                    <span class="top">
                  <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                </span>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="panel padder-v item">
                                    <div class="font-thin h1" id="order_lastday">
                                        ￥&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../admin/assets/img/loading.gif"
                                                                                  style="width: 80px;position: absolute;margin-top: -7%;margin-left: -20%;">
                                    </div>
                                    <span class="text-muted text-xs">昨日收入</span>
                                    <div class="bottom">
                                        <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="col-xs-12 m-b-md">
              <div class="r bg-light dker item hbox no-border">
                <div class="col w-xs v-middle hidden-md">
                  <div ng-init="d3_3=[60,40]" ui-jq="sparkline" ui-options="[60,40], {type:'pie', height:40, sliceColors:['#fad733','#fff']}" class="sparkline inline"></div>
                </div>
                <div class="col dk padder-v r-r">
                  <div class="text-primary-dk font-thin h1"><span>￥<?php echo $s_money; ?></span></div>
                  <span class="text-muted text-xs">商户当前余额</span>
                </div>
              </div>
            </div>-->
                            <div class="col-xs-6">
                                <div class="panel padder-v item">
                                    <div class="font-thin h1">￥<?php echo $payment; ?></div>
                                    <span class="text-muted text-xs">已消费金额</span>
                                    <div class="bottom">
                                        <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="panel padder-v item">
                                    <div class="font-thin h1">￥<?php echo $balance; ?></div>
                                    <span class="text-muted text-xs">商户当前余额</span>
                                    <div class="bottom">
                                        <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="panel wrapper">
                            <label class="i-switch bg-warning pull-right" ng-init="showSpline=true">
                                <input type="checkbox" ng-model="showSpline">
                                <i></i>
                            </label>
                            <h4 class="font-thin m-t-none m-b text-muted">结算统计表</h4>
                            <div ui-jq="plot" ui-refresh="showSpline" ui-options="
              [
                { data: [ 0<?php //echo $chart ?> ], label:'结算金额', points: { show: true, radius: 1}, splines: { show: true, tension: 0.4, lineWidth: 1, fill: 0.8 } }
              ],
              {
                colors: ['#23b7e5', '#7266ba'],
                series: { shadowSize: 3 },
                xaxis:{ font: { color: '#a1a7ac' } },
                yaxis:{ font: { color: '#a1a7ac' }, max:0<?php //echo($max_settle + 10) ?> },
                grid: { hoverable: true, clickable: true, borderWidth: 0, color: '#dce5ec' },
                tooltip: true,
                tooltipOpts: { content: '结算金额￥%y',  defaultTheme: false, shifts: { x: 10, y: -25 } }
              }
            " style="height:246px">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- / stats -->
                <div class="panel panel-default">
                    <div class="panel-heading font-bold">
                        基本资料
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal devform">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">商户ID</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" value="<?php echo $_SESSION['userid'] ?>"
                                           readonly="true">
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">商户密钥</label>
                                <div class="col-sm-9">
                                    <input class="form-control" id="user_key" type="text" value="<?php echo hideStr($userrow['key'],3,27); ?>"
                                           readonly="true">
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">
                                    <a href="javascript:get_user_key();" class="btn btn-primary btn-sm" target="_blank">
                                        一键获取令牌
                                    </a>
                                </label>
                                <div class="col-sm-9">

                                    <input type="text" id="validation_key" class="form-control" style="width: 50%;float: left;">&nbsp;&nbsp;&nbsp;&nbsp;
                                    <a href="javascript:validation_key();" class="btn btn-primary btn-sm" target="_blank">
                                        验证令牌
                                    </a>
                                </div>

                            </div>
                            <div class="form-group" style="display: none;">
                                <label class="col-sm-2 control-label">账号绑定</label>
                                <?php if(empty($userrow['alipay_uid'])){ ?>
                                    <div class="col-sm-9">
                                        <a href="oauth.php?bind=true" class="btn btn-primary btn-sm" target="_blank">绑定支付宝账号
                                            一键登录到本站</a>
                                    </div>
                                <?php }else{ ?>
                                    <div class="col-sm-9">
                                        已绑定支付宝UID:<?php echo $userrow['alipay_uid'] ?>&nbsp;<a
                                                href="oauth.php?unbind=true" class="btn btn-danger btn-xs"
                                                onclick="return confirm('解绑后将无法通过支付宝一键登录，是否确定解绑？');">解绑账号</a>
                                    </div>
                                <?php } ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading font-bold">
                        邮箱绑定（一经绑定将不可修改,如需修改请联系管理员）
                    </div>
                    <div class="panel-body">
                        <div style="color: red;">温馨提示：为了安全起见，通过发送邮箱一个令牌，令牌验证通过后方可查看商户秘钥。</div>
                        <form class="form-horizontal devform">
                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">邮箱</label>
                                <div class="col-sm-9">
                                    <input class="form-control" onchange="email_bd(this)" type="text" <?php if(isset($userrow["note2"])){$qq_json = unserialize($userrow["note2"]); echo "value=\"{$qq_json["email"]}\" readonly=\"true\"";} ?> >
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <script>
                                function email_bd(ev) {
                                    var email = ev.value;
                                    if(!confirm("确定要绑定"+email+",这个邮箱吗？\n一经绑定将不可修改,如需修改请联系管理员！！！")){
                                        return;
                                    }
                                    $.ajax({
                                        url:"../api/ajax_api/ajax_set_user_email_bd.php",
                                        type:"POST",
                                        dataType:"json",
                                        data:{
                                            qq_email:email
                                        },
                                        success:function (data) {
                                            if(data.success=="true"){
                                                ev.readOnly=true
                                            }
                                            alert(data.msg);
                                        }
                                    });
                                }

                                function get_user_key() {
                                    $.ajax({
                                        url:"../api/ajax_api/ajax_send_email_token.php",
                                        type:"POST",
                                        dataType:"json",
                                        data:{

                                        },
                                        success:function (data) {
                                            alert(data.msg);
                                        }
                                    });
                                }

                                function validation_key() {
                                    $.ajax({
                                        url:"../api/ajax_api/ajax_validation_email_token.php",
                                        type:"POST",
                                        dataType:"json",
                                        data:{
                                            validation_key:$("#validation_key").val()
                                        },
                                        success:function (data) {
                                            if(data.success=="true"){
                                                alert(data.msg)
                                                $("#user_key").val(data.key);
                                                setTimeout(function (){
                                                    location.reload();
                                                },30000)
                                            }
                                            else{
                                                alert(data.msg);
                                            }

                                        }
                                    });
                                }
                            </script>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>
<script>
    index_load_1();
</script>
