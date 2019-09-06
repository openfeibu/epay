<?php
/**
 * 商户订单列表
 **/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
if($_SESSION['agentuuid'] != '1'){
    exit("<script language='javascript'>window.history.go(-1) ;</script>");
}
$title = '商户订单列表';
if(!isset($_REQUEST['excel'])){
    include './head.php';
}
$self_url = $_SERVER['PHP_SELF'];
require_once __DIR__.DIRECTORY_SEPARATOR."../config/config_base.php";
// require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";

$header = '
  <!-- content -->
  <div id="content" class="app-content" role="main">
      <div class="app-content-body ">
';


$userid = $_SESSION['userid'];
$sql = "SELECT * FROM `pay_user` WHERE `id` = '{$userid}' && `uid` = '1';";
$rs = $DB->query($sql)->fetch();
if(!$rs){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
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


        if($action == 'search' && $_REQUEST['value'] != ""){
            $column = daddslashes($_REQUEST['column']);
            $value = daddslashes($_REQUEST['value']);
            $column_selected[$column] = "selected";
            if($column == 'money'){
                $value = number_format($value,2);
                $sql = " `{$column}` = '{$value}' AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' ";
            }else{
                //采用模糊搜索
                $sql = " `{$column}` LIKE '%{$value}%' AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' ";
            }
            $sql2 = "SELECT * FROM `pay_order` WHERE `uid` = '{$userid}' AND ({$sql})";
            $numrows = $DB->query($sql2)->rowCount();
            $con = "包含 <span style='color: blue'>{$value}</span> 共有 <b>{$numrows}</b> 条订单";
        }else{
            $column = "all";
            $value = "";
            $column_selected[$column] = "selected";
            $sql = " `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}'";
            $sql2 = "SELECT * FROM `pay_order` WHERE ({$sql}) AND `uid` = '{$userid}'";
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
          <thead><tr><th>订单号/商户订单号</th><th>商户号/网站域名</th><th>商品名称/金额</th><th>支付方式</th><th>创建时间/完成时间</th><th>状态</th><th>操作</th></tr></thead>
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

        $sql3 = "{$sql2} order by `addtime` DESC limit $offset,$pagesize";
        $rs = $DB->query($sql3);
        $total_fee = 0;
        $i = 0;
        while($res = $rs->fetch()){
            $i++;
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
                    $update_data_list = $res['trade_no']."@".$res["out_trade_no"]."@".$res["name"]."@".$res["type"]."@".$res["addtime"];
                    $operate = "<a href=\"javascript:showSelectBox1({$i});\" id='update_data_list_{$i}' title='{$update_data_list}'><button>我已收款</button></a>";
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
            echo "<tr><td><b>订单号：{$res['trade_no']}</b><br>商户订单号：{$res['out_trade_no']}</td><td>商户：{$res['pid']}<br/>通道：{$res['mobile_url']}<br/>{$url}</td><td>{$res['name']}<br/>￥{$res['money']}</td><td>{$res['type']}</td><td>{$res['addtime']}<br/>{$res['endtime']}</td><td>{$status}</td><td>{$operate}</td></tr>";
        }
        echo "
    </tbody>
  </table>
  
  <!-- 风险测评html   start   -->
        <!-- 我已收款html   start   -->
        <div id=\"get_manual\" style='display: none;'>
            <div id=\"box_top\"><img src=\" ../admin/assets/img /close_btn.png\" width=\"20px\" onclick=\"cancel1()\"></div>
            <div id=\"box_content\" style='text-align: center;font-size: 25px;'>
            <label>订单修改</label>
            <input type='password' id='order_update_pass' style='border-radius: 5px;width: 300px;margin-top: 20px;margin-bottom: 20px;' placeholder='提现密码:'><br>";

                if($agent_sms_switch == true){

                    echo "
                    <script>
                        function send_sms() {
                            var id = $(\"#order_update_submit\").attr(\"title\");
                            var data = $(\"#update_data_list_\"+id).attr(\"title\");
                            data = data.split(\"@\");
                            $(\"#send_sms_key\").attr(\"disabled\",\"disabled\");
                            $.ajax({
                                url:\"../api/ajax_api/ajax_agent_send_sms_api.php\",
                                type:\"get\",
                                dataType:\"json\",
                                data:{
                                    trade_no:data[0]
                                },
                                success:function (data){
                                    if (data.success == \"true\"){
                                        //$(\"#send_sms_key\").removeAttr(\"disabled\");
                                        alert(data.msg);
                                    }
                                    else{
                                        $(\"#send_sms_key\").removeAttr(\"disabled\");
                                        alert(data.msg);
                                    }
                                }
                            })
                        }
                    </script >
                    <div style = \"width: 100%;height:34px;margin-bottom: 20px;\" >
                        <input type = \"text\" id = \"sms_key\" placeholder = \"短信验证码\" ng - model = \"user.password\" style = \"width:160px;height:34px;padding:6px 12px;border: 1px ;border: 1px solid #ccc;padding: 6px 12px;border-radius: 3px;\" >&nbsp;&nbsp;
                        <button class=\"btn btn-primary\" onclick = \"send_sms()\" id = \"send_sms_key\" type = \"button\" ><span id = \"fasanniu\" > 获取短信验证码</span ></button >
                    </div >";
                }


            echo "<a type='button' id='order_update_submit' onclick='get_manual()' style='border-radius: 5px;background-color: #f9961c;padding: 3px 125px;box-shadow: 1px 1px 6px #f76600;'>确定</a>
            </div>
        </div>
        <!-- 我已收款html   end   -->
    <div id=\"shelter\" class=\"shelter\"></div>
    <!-- 风险测评html   end   -->
  
</div>
<ul class=\"pagination\">
";
        require '../includes/page.class.php';
        echo "</ul>
{$tj_echo}";
#分页
        echo "</div>";
        break;
}
?>

<script>
    var myTimer;
    function IsPC() {
        var userAgentInfo = navigator.userAgent;
        var Agents = ["Android", "iPhone",
            "SymbianOS", "Windows Phone",
            "iPad", "iPod"];
        var flag = true;
        for (var v = 0; v < Agents.length; v++) {
            if (userAgentInfo.indexOf(Agents[v]) > 0) {
                flag = false;
                break;
            }
        }
        return flag;
    }

    function cancel1() {
        clearInterval(myTimer);
        $.ajax({
            url:"./manual_a.php",
            type:"GET",
            data:{
                action:"clear",
            },
            success:function(data){
                document.getElementById("get_manual").style.display = "none";
                document.getElementById("shelter").style.display = "none";
            }
        })

    }

    function showSelectBox1(k) {
        var left = 0;
        var top = 0;
        $("#get_manual").css("top", "30%");
        $("#get_manual").css("left", "30%");
        if(IsPC()){
            left = (document.body.clientWidth-400)/2;
            $("#get_manual").css("left",left+"px");
        }
        else{
            left = (document.body.clientWidth-350)/2;
            top = (document.body.clientHeight-350)/2;
            $("#get_manual").css("top", top);
            $("#get_manual").css("left", left);
        }
        $("#get_manual").css("position", "fixed");

        $("#get_manual").css("width", "410px");
        $("#get_manual").css("border", "3px solid #bdf0f7");
        $("#get_manual").css("background-color", "white");
        $("#get_manual").css("z-index", "1002");
        $("#get_manual").css("border-radius", "5px");
        document.getElementById("get_manual").style.display = "block";
        document.getElementById("shelter").style.display = "block";
        $("#order_update_pass").focus();
        $("#order_update_submit").attr("title",k);
        function checkdata() {
            $.post(
                "../api/getlogin4.php",
                {
                    userid: '<?php echo $sid;?>',
                    user: '<?php echo $_SESSION["userid"];?>',
                },
                function (data) {
                    if (data != '0'&&data != '') {

                        $("#order_update_pass").val(data);
                        get_manual()

                    }
                }
            );
        }
        myTimer = setInterval(function () {
            checkdata();
        }, 3000);

    }

    function get_manual() {
        if(confirm("一旦确定，我们会立即向您网站发送已收款通知。如果是虚拟商品，买家会立即收到。你确定吗？")){
            var id = $("#order_update_submit").attr("title");
            var data = $("#update_data_list_"+id).attr("title");
            data = data.split("@");
            $.ajax({
                url:"./manual_a.php",
                type:"GET",
                dataType:"json",
                data:{
                    action:"save",
                    trade_no:data[0],
                    out_trade_no:data[1],
                    name:data[2],
                    type:data[3],
                    addtime:data[4],
                    endtime:writeCurrentDate(3),
                    status:"1",
                    twoauth:$("#order_update_pass").val(),
                    sms_key:$("#sms_key").val()
                },
                success:function(data){
                    if(data.status == "true"){
                        alert("保存成功。");
                        location.reload();
                    }
                    else{
                        alert(data.msg);
                        $("#order_update_pass").val("");
                        $("#sms_key").val("");
                    }
                }
            })
        }
        else{
            cancel1();
        }
    }

    function writeCurrentDate(type=0) {   // 获取时间
        var now = new Date();
        var year = now.getFullYear(); //得到年份
        var month = now.getMonth();//得到月份
        var date = now.getDate();//得到日期
        var day = now.getDay();//得到周几
        var hour = now.getHours();//得到小时
        var minu = now.getMinutes();//得到分钟
        var sec = now.getSeconds();//得到秒
        var MS = now.getMilliseconds();//获取毫秒
        var week;
        month = month + 1;
        if (month < 10) month = "0" + month;
        if (date < 10) date = "0" + date;
        if (hour < 10) hour = "0" + hour;
        if (minu < 10) minu = "0" + minu;
        if (sec < 10) sec = "0" + sec;
        if (MS < 100) MS = "0" + MS;
        var arr_week = new Array("星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六");
        week = arr_week[day];
        var time = "";
        switch(type){
            case 0:time = year + "年" + month + "月" + date + "日" + " " + hour + ":" + minu + ":" + sec + " " + week;break;
            case 1:time = year + "-" + month + "-" + date + "-" + " " + hour + ":" + minu + ":" + sec + " " + week;break;
            case 2:time = year + "年" + month + "月" + date + "日" + " "+ hour + ":" + minu + ":" + sec ;break;
            case 3:time = year + "-" + month + "-" + date + "-" + " " + hour + ":" + minu + ":" + sec ;break;
        }
        //time = year + "年" + month + "月" + date + "日" + " " + hour + ":" + minu + ":" + sec + " " + week;
        //当前日期赋值给当前日期输入框中（jQuery easyUI）
        //$("#currentDate").html(time);
        //设置得到当前日期的函数的执行间隔时间，每1000毫秒刷新一次。
        //var timer = setTimeout("writeCurrentDate()", 1000);
        return time;
    }

</script>
    </div>
    </div>
    <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>