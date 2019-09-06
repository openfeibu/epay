<?php
/**
 * 管理员首页
 **/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '管理员中心';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
//订单总数已经不使用
//$sql = "SELECT * FROM `pay_order`;";
//$orders = $DB->query($sql)->rowCount();
$orders = 0;

//充值账户总额
$pid = 0;
//针对多管理员做修正并优化
//$sql4 = "SELECT SUM(`money`) FROM `pay_recharge_history` WHERE `type` = 1 AND `userid` != '-1' AND `userid` = ".$_SESSION['admin_userid']. " UNION ALL SELECT SUM(`balance`) FROM `pay_recharge` WHERE `id` = ".$_SESSION['admin_userid'];
$sql4 = "SELECT SUM(`money`) FROM `pay_recharge_history` WHERE `type` = 1 AND `userid` != '-1' AND `userid` = '0' UNION ALL SELECT SUM(`balance`) FROM `pay_recharge` WHERE `id` = '0'";
$result=$DB->query($sql4);
$row2 = $result->fetch();
$total_charge = round($row2[0],0) / 100;
$row3 = $result->fetch();
$balance = round($row3['0'],0) / 100;
$payment = $total_charge - $balance;
$payment = round($payment,2);


$lastday = date("Y-m-d",strtotime("-1 day")).' 00:00:00';
$today_middle = date("Y-m-d").' 12:00:00';
$today_begin = date("Y-m-d").' 00:00:00';
$today_end = date("Y-m-d")."23:59:59";
$year_month = date("Y-m");
$firstday = "{$year_month}-01 00:00:00";

//今日收入 昨日收入和当月收入 代码新修正，旧代码参看如下注释
//$row                              = $DB->query("SELECT count(*) as a,sum(money) as b from `pay_order` WHERE `status` = '1' AND `endtime` >= '{$today_begin}'; ")->fetch();
//$order_today                      = $row[1]; //今天收入
//$order_today_num                  = $row[0]; //今天收入笔数


$row = $DB->query("SELECT count(*) as a,sum(money) as b from `pay_order` WHERE `status` = '1' AND type='wxpay' AND `endtime` >= '{$today_begin}'; ")->fetch();
$wx_today = $row[0]; //微信今天收入

$row = $DB->query("SELECT count(*) as a,sum(money) as b from `pay_order` WHERE `status` = '1' AND type='alipay' AND `endtime` >= '{$today_begin}'; ")->fetch();
$alipay_today = $row[0]; //支付宝今天收入

$row = $DB->query("SELECT count(*) as a,sum(money) as b from `pay_order` WHERE `status` = '1' AND type='qqpay' AND `endtime` >= '{$today_begin}'; ")->fetch();
$qqpay_today = $row[0]; //qqpay方式今天收入

$row = $DB->query("SELECT count(*) as a,sum(money) as b from `pay_order` WHERE `status` = '1' AND type='cqpay' AND `endtime` >= '{$today_begin}'; ")->fetch();
$cqpay_today = $row[0]; //cqpay方式今天收入

$QQpay_today = $qqpay_today + $cqpay_today; //QQ今天收入

$order_today_left = $wx_today * (1 - $userrow['wxpay_fee']) + $alipay_today * (1 - $userrow['alipay_fee']) + $qqpay_today * (1 - $userrow['qqpay_fee']) + $cqpay_today * (1 - $userrow['fourpay_fee']); //今天净收入

//$row                              = $DB->query("SELECT count(*) as a,sum(money) as b from pay_order where `status` = 1 and endtime>='$lastday' and endtime<'{$today_begin}' limit 1")->fetch();
//$order_lastday                    = $row[1]; //昨天收入
//$order_lastday_num                = $row[0]; //昨天收入笔数
//$row                              = $DB->query("SELECT count(*) as a,sum(money) as b from `pay_order` WHERE `status` = '1' AND `endtime` >= '{$firstday}'; ")->fetch();
//$order_firstday                   = $row[1]; //本月收入
//$order_firstday_num               = $row[0]; //本月收入笔数
?>

    <script>
        function index_load_1() {
            var order_today = 0;
            var order_today_num = 0;
            $.ajax({                                                                    //获取今天收入
                type: "GET",
                url: "../api/ajax_index_api.php",
                data: {
                    token: "order_today"
                },
                success: function (data) {
                    data = eval('(' + data + ')');
                    order_today = data["order_today"];
                    order_today_num = data["order_today_num"];
                    $("#order_today").html("￥" + data["order_today"]);
                    $("#order_today_num").html(data["order_today_num"] + "笔");
                }
            });
            $.ajax({                                                                    //获取昨天收入
                type: "GET",
                url: "../api/ajax_index_api.php",
                data: {
                    token: "order_lastday"
                },
                success: function (data) {
                    data = eval('(' + data + ')');
                    $("#order_lastday").html("￥" + data["order_lastday"]);
                    $("#order_lastday_num").html(data["order_lastday_num"] + "笔");
                }
            });
            $.ajax({                                                                    //获取当月收入
                type: "GET",
                url: "../api/ajax_index_api.php",
                data: {
                    token: "order_firstday"
                },
                success: function (data) {
                    data = eval('(' + data + ')');
                    var order_firstday = parseFloat(data["order_firstday"]) + parseFloat(order_today);
                    var order_firstday_num = data["order_firstday_num"]+order_today_num;
                    $("#order_firstday").html("￥" + order_firstday.toFixed(2));
                    $("#order_firstday_num").html(order_firstday_num + "笔");
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
                <div class="row" style="display: none;">
                    <div class="col-md-5">
                        <div class="row row-sm text-center">
                            <div class="col-xs-6">
                                <div class="panel padder-v item">
                                    <div class="h1 text-info font-thin h1"><?php echo $orders ?>个</div>
                                    <span class="text-muted text-xs">订单总数</span>
                                    <div class="top text-right w-full">
                                        <i class="fa fa-caret-down text-warning m-r-sm"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="block panel padder-v bg-primary item">
                                    <span class="text-white font-thin h1 block">￥<?php echo $total_charge ?></span>
                                    <span class="text-muted text-xs">服务预存总额</span>
                                    <span class="bottom text-right w-full">
                                    <!--<i class="fa fa-caret-down text-muted m-r-sm"></i>-->
                                    </span>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="block panel padder-v bg-info item">
                                    <span class="text-white font-thin h1 block">￥<?php echo $order_today; ?></span>
                                    <span class="text-muted text-xs">今日收入</span>
                                    <span class="top">
                  <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                </span>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="panel padder-v item">
                                    <div class="font-thin h1">￥<?php echo $order_lastday ?></div>
                                    <span class="text-muted text-xs">昨日收入</span>
                                    <div class="bottom">
                                        <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                    </div>
                                </div>
                            </div>

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
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="row row-sm text-left" style="height: 91px;line-height: 50px;">
                                <div class="col-xs-12">
                                    <div style="font-size: 2.3em;font-weight: bold;display: block;width: 100%;">
                                        账户余额：￥<?php echo $balance; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-xs-12">
                                <a href="./recharge.php" style=""><span
                                            style="display:inline-block;background: linear-gradient(to right,#00b4ff 0, #5893df 100%);width:35%;height:60px;text-align: center;line-height: 60px;color:#fff;font-size:1.5em;float:right;cursor: pointer;">
									充值
								</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">

                        <div class="col-md-6">
                            <div class="row row-sm text-center">
                                <div class="col-xs-6">
                                    <div class="panel padder-v item">
                                        <div class="font-thin h1" id="order_today">
                                            ￥&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="./assets/img/loading.gif"
                                                                                      style="width: 80px;position: absolute;margin-top: -7%;margin-left: -20%;">
                                        </div>
                                        <span class="text-muted text-xs">今日收入</span>
                                        <div class="bottom">
                                            <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="panel padder-v item">
                                        <div class="font-thin h1" id="order_lastday">
                                            ￥&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="./assets/img/loading.gif"
                                                                                      style="width: 80px;position: absolute;margin-top: -7%;margin-left: -20%;">
                                        </div>
                                        <span class="text-muted text-xs">昨日收入</span>
                                        <div class="bottom">
                                            <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="panel padder-v item">
                                        <div class="font-thin h1" id="order_today_num">
                                            <img src="./assets/img/loading.gif"
                                                 style="width: 80px;position: absolute;margin-top: -7%;margin-left: -8%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;笔
                                        </div>
                                        <span class="text-muted text-xs">今日订单</span>
                                        <div class="bottom">
                                            <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="panel padder-v item">
                                        <div class="font-thin h1" id="order_lastday_num">
                                            <img src="./assets/img/loading.gif"
                                                 style="width: 80px;position: absolute;margin-top: -7%;margin-left: -8%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;笔
                                        </div>
                                        <span class="text-muted text-xs">昨日订单</span>
                                        <div class="bottom">
                                            <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row row-sm text-center">
                                <div class="col-xs-6">
                                    <div class="panel padder-v item">
                                        <div class="font-thin h1" id="order_firstday">
                                            ￥&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="./assets/img/loading.gif"
                                                                                      style="width: 80px;position: absolute;margin-top: -7%;margin-left: -20%;">
                                        </div>
                                        <span class="text-muted text-xs">当月收入</span>
                                        <div class="bottom">
                                            <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="panel padder-v item">
                                        <div class="font-thin h1">￥<?php echo $total_charge ?></div>
                                        <span class="text-muted text-xs">服务预存总额</span>
                                        <div class="bottom">
                                            <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="panel padder-v item">
                                        <div class="font-thin h1" id="order_firstday_num">
                                            <img src="./assets/img/loading.gif"
                                                 style="width: 80px;position: absolute;margin-top: -7%;margin-left: -8%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;笔
                                        </div>
                                        <span class="text-muted text-xs">当月订单</span>
                                        <div class="bottom">
                                            <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="panel padder-v item">
                                        <div class="font-thin h1">￥<?php echo $payment ?></div>
                                        <span class="text-muted text-xs">已消费金额</span>
                                        <div class="bottom">
                                            <i class="fa fa-caret-up text-warning m-l-sm m-r-sm"></i>
                                        </div>
                                    </div>
                                </div>
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
                                <label class="col-sm-2 control-label">管理员ID</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text"
                                           value="<?php echo $_SESSION['admin_userid'] ?>" readonly="true">
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">管理员名称</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text"
                                           value="<?php echo $_SESSION['admin_user']; ?>" readonly="true">
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
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
                        绑定发送主邮箱（为避免出错！绑定后不可修改）
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal devform">
                            <div class="form-group">
                                <div style="color: red;margin-left: 15px;">温馨提示：绑定邮箱是用于商户查看商户秘钥时，发送令牌所用</div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">QQ邮箱</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" id="QQEmail"
                                           value="">
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">QQ邮箱授权码</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" id="QQCode"
                                           value="">
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">发件人昵称（自定义）</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="text" id="QQNmae"
                                           value="">
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"></label>
                                <div class="col-sm-9">
                                    <a href="javascript:set_email_code();" id="email_code_btn" class="btn btn-primary btn-sm" target="_blank">
                                        保存
                                    </a>
                                </div>
                            </div>
                        </form>
                        <script>
                            function set_email_code() {
                                $.ajax({
                                    url:"../api/ajax_set_email_code.php",
                                    type:"GET",
                                    dataType:"json",
                                    data:{
                                        t:"set",
                                        QQCode:$("#QQCode").val(),
                                        QQEmail:$("#QQEmail").val(),
                                        QQName:$("#QQNmae").val()
                                    },
                                    success:function (data) {
                                        if(data.success=="true"){
                                            $("#QQCode").val(data.QQCode);
                                            $("#QQEmail").val(data.QQEmail);
                                            $("#QQNmae").val(data.QQName);
                                            $("#QQCode").attr("readonly","true");
                                            $("#QQEmail").attr("readonly","true");
                                            $("#QQNmae").attr("readonly","true");
                                            $("#email_code_btn").attr("disabled","disabled");
                                        }
                                        alert(data.msg);
                                    }
                                });
                            }
                            function get_email_code() {
                                $.ajax({
                                    url:"../api/ajax_set_email_code.php",
                                    type:"POST",
                                    dataType:"json",
                                    data:{
                                        t:"get",
                                        QQCode:$("#QQCode").val(),
                                        QQEmail:$("#QQEmail").val()
                                },
                                success:function (data) {
                                    if(data.success=="true"){
                                        $("#QQCode").val(data.QQCode);
                                        $("#QQEmail").val(data.QQEmail);
                                        $("#QQNmae").val(data.QQName);
                                        $("#QQCode").attr("readonly","true");
                                        $("#QQEmail").attr("readonly","true");
                                        $("#QQNmae").attr("readonly","true");
                                        $("#email_code_btn").attr("disabled","disabled");
                                    }
                                }
                                });
                            }
                        </script>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- /content -->
<?php include_once __DIR__.DIRECTORY_SEPARATOR."foot.php" ?>
<script>
    index_load_1();
    get_email_code();
</script>
