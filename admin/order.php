<?php
	/**
	 * 订单记录
	 **/
	include("../includes/common.php");
	if(!$_SESSION['is_admin']){
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
	$admin_uuid = $_SESSION['admin_uuid'];
	$title = '订单记录';
	if(!isset($_REQUEST['excel'])){
		include './head.php';
	}
	$self_url = $_SERVER['PHP_SELF'];
	//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";

	$header = <<< EOF
  <!-- content -->
  <div id="content" class="app-content" role="main">
      <div class="app-content-body ">
      <script>
      var on = 0;//0=隐藏
function open2(){
	if(on == 0){
		$('.s1').css('display','block');
		$('#o1').attr('value','隐藏回调');
		on = 1;
	}else if(on == 1){
		$('.s1').css('display','none');
		$('#o1').attr('value','显示回调');
		on = 0;
	}
}
        </script>
EOF;
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
			$status = daddslashes($_REQUEST['status']);
			$trade_no = daddslashes($_REQUEST['trade_no']);
			$endtime = daddslashes($_REQUEST['endtime']);
			$buyer = date("Y-m-d H:i:s")."@".real_ip()."@manual";
			$twoauth = daddslashes($_REQUEST['twoauth']);
			if($twoauth != $conf['twoauth']){
				exit("<script language='javascript'>alert('二次验证密码错误，修改订单状态失败。');history.go(-1);</script>");
			}
			$sql = "UPDATE {$table_name} SET `status` = '{$status}', `endtime` = '{$endtime}', `buyer` = '{$buyer}' WHERE `trade_no` = '{$trade_no}'; ";
			if($DB->query($sql)){
				exit("<script language='javascript'>alert('保存成功。');history.go(-1);</script>");
			}else{
				exit("<script language='javascript'>alert('保存失败。');history.go(-1);</script>");
			}
			break;
		case 'add_submit':
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
					$sql = " `money`-`money2` = '{$value}' AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' ";
				}else{
					//采用模糊搜索
					$sql = " `{$column}` LIKE '%{$value}%' AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' ";
				}

				if($column == 'mobile_url' && $value == ''){
					$sql = " `{$column}` is null AND `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' ";
				}
				$sql2 = "SELECT *,money-money2 as truemoney FROM {$table_name} WHERE `status` != 9 AND {$sql}";
				//旧方法
				//$numrows = $DB->query($sql2)->rowCount();
				$result=$DB->query("SELECT count(*) FROM {$table_name} WHERE `status` != 9 AND {$sql}")->fetch();
				$numrows =$result[0];
				$con = "包含 <span style='color: blue'>{$value}</span> 共有 <b>{$numrows}</b> 条订单";
			}else{
				$column = "all";
				$value = "";
				$sql = " `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}'";
				$sql2 = "SELECT *,money-money2 as truemoney FROM {$table_name} WHERE `status` != 9 AND {$sql}";
				//旧方法
				//$numrows = $DB->query($sql2)->rowCount();
				$result=$DB->query("SELECT count(*) FROM {$table_name} WHERE `status` != 9 AND {$sql}")->fetch();
				$numrows =$result[0];
				$con = "共有 <b>{$numrows}</b> 条订单";
			}
			$link = $_REQUEST;
			unset($link['page']);
			$link = http_build_query($link);
			$link = "&".$link;
			//导出excel表格
			if(isset($_REQUEST['excel']) && $_REQUEST['excel'] == 'yes'){
				require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/autoload.php";
				$csv_title = ["订单号","商户订单号","商户号","商品名称","金额","支付方式","隧道地址","浏览器类型","创建时间","完成时间","状态"];
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
			$result=$DB->query("SELECT sum(money),status FROM {$table_name} WHERE {$sql} AND status=0")->fetch();
			$tj['unpay'] =$result[0]==null?0:$result[0]; //总未付款订单金额
			$result=$DB->query("SELECT sum(money),status FROM {$table_name} WHERE {$sql} AND status=1")->fetch();
			$tj['pay'] =$result[0]==null?0:$result[0]; //总付款订单金额
			$tj['total']=$tj['unpay']+$tj['pay']; //订单总金额
			$result=$DB->query("SELECT sum(money),status FROM {$table_name} WHERE {$sql} AND status=1 AND (`type`='alipay2' OR `type`='alipay2_url'OR `type`='tonglian3' OR `type`='alipay2qr')")->fetch();
			$tj['alipay']=$result[0]==null?0:$result[0]; //支付宝订单总金额
			$result=$DB->query("SELECT sum(money),status FROM {$table_name} WHERE {$sql} AND status=1 AND (`type`='wechat2' OR `type`='wechat2_url'OR `type`='tonglian2' OR `type`='wechat2qr')")->fetch();
			$tj['wechat']=$result[0]==null?0:$result[0]; //微信订单总金额
			$result=$DB->query("SELECT sum(money),status FROM {$table_name} WHERE {$sql} AND status=1 AND (`type`='qqpay2' OR `type`='qqpay2_url' OR `type`='qqpay2qr')")->fetch();
			$tj['qqpay']=$result[0]==null?0:$result[0]; //QQ订单总金额
			$tj['others']=$tj['pay']-$tj['alipay']-$tj['wechat']-$tj['qqpay']; //其它付款方式
			$tj['fee']=$tj['alipay']*$user['alipay_fee']+$tj['wechat']*$user['wxpay_fee']+$tj['qqpay']*$user['qqpay_fee'];
			//统计旧方法
			/*$results = $DB->query($sql2);
			$result = $results->fetch();
			while($result){
				$tj['total'] += $result['money'];
				if($result['status'] == 1){
					$tj['pay'] += $result['money'];
					switch($result['type']){
						case 'alipay2':
						case 'alipay2_url':
						case 'alipay2qr':
							$tj['alipay'] += $result['money'];
							$tj['fee'] += $result['money'] * $user['alipay_fee'];
							break;
						case 'wechat2':
						case 'wechat2_url':
						case 'wechat2qr':
							$tj['wechat'] += $result['money'];
							$tj['fee'] += $result['money'] * $user['wxpay_fee'];
							break;
						case 'qqpay2':
						case 'qqpay2_url':
						case 'qqpay2qr':
							$tj['qqpay'] += $result['money'];
							$tj['fee'] += $result['money'] * $user['qqpay_fee'];
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
			*/


			$tj_echo = "<div style='padding-left: 20px;'><span style='color: blue;font-size: 14px;'>总发起支付金额：{$tj['total']}元。<br>
已支付：{$tj['pay']}元，其中支付宝付款：{$tj['alipay']}元，微信付款：{$tj['wechat']}元，其他支付方式付款：{$tj['others']}元。<br>
未支付：{$tj['unpay']}<br>
手续费：{$tj['fee']}元<br></span></div>";
			$tj_echo = "<div style='padding-left: 20px;'><span style='color: #bbb;font-size: 12px;'>总发起支付金额：{$tj['total']}元。<br>
已支付：{$tj['pay']}元，其中支付宝付款：{$tj['alipay']}元，微信付款：{$tj['wechat']}元，其他支付方式付款：{$tj['others']}元。<br>
未支付：{$tj['unpay']}<br>
手续费：{$tj['fee']}元<br></span></div>";
            $sid=session_id();
            $scancode="https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=2019022163313167&scope=auth_base&redirect_uri=".urlencode($website_urls)."zfbsj.php%3ftrade_no%3d{$sid}%26type%3d4";

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
        <form action="{$self_url}" method="GET" class="form-inline">
            <input type="hidden" name="action" value="search">
            <div class="form-group">
                <label>选择搜索范围</label>
                <select name="column" class="form-control">
                    <option value="trade_no" {$column_selected['trade_no']}>订单号</option>
                    <option value="out_trade_no" {$column_selected['out_trade_no']}>商户订单号</option>
                    <option value="pid" {$column_selected['pid']}>商户号</option>
                    <option value="mobile_url" {$column_selected['mobile_url']}>隧道地址</option>
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

      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>订单信息</th><th>商品名称/金额</th><th>创建时间</th><th>状态</th><th>操作</th><th>风险测评</th></tr></thead>
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
			$i = 0;
			while($res = $rs->fetch()){
			    $i++;
				$url = getdomain($res['notify_url']);
				$url = "";
				switch($res['status']){
					case 0:
						$status = "<font color='blue'>等待支付</font>";
                        $update_data_list = $res['trade_no']."@".$res["out_trade_no"]."@".$res["name"]."@".$res["type"]."@".$res["addtime"];
						$operate = "<a href=\"javascript:showSelectBox1({$i});\" id='update_data_list_{$i}' title='{$update_data_list}'><button>我已收款</button></a>";
						break;
					case 1:
						$status = "<font color='green'>已完成【已通知次数：$res[order_one]】</font>";
						$operate = "
                        <span>
							<a href='../api/return_url.php?trade_no={$res['trade_no']}&notify=yes' style='color: green;' target='_blank'>重发通知（异步）</a>
							<br><a href='../api/return_url.php?trade_no={$res['trade_no']}' style='color: green;' target='_blank'>重发通知（同步）</a>
						</span> ";
						break;
					case 2:
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

				if(!empty($res[buyer])){
					//处理下
					$useridinfo=substr($res[buyer],0,strpos($res[buyer],"@"));
				}else{
					$useridinfo=$res[buyer];
				}
                //判断是不是补单，是就显示@补
                $is_manual = "";
                if(strpos($res["buyer"],"@manual") !== false && !empty($res["buyer"])){
                    $is_manual = "@补";
                    if(strpos($res["buyer"],"@admin") !== false && !empty($res["buyer"])){
                        $is_manual .= "@管理员";
                    }
                    else if(strpos($res["buyer"],"@agent") !== false && !empty($res["buyer"])){
                        $is_manual .= "@代理";
                    }
                }
				print <<< EOF
<tr>
    <td>
        商户号：{$res['pid']}<br/>
        订单号：<b>{$res['trade_no']}{$is_manual}</b><br/>
        收款账号：【{$mobile_url}】<br/>
        商户订单号：{$res['out_trade_no']}
    </td>
    <td>
        {$res['name']}<br/>
        ￥{$res['truemoney']}<br>
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
    <td>
    <a class="btn btn-xs btn-info" onclick="risk_evaluation({$mobile_url})">一键测评</a> {$useridinfo}
    </td>
</tr>
EOF;
			}
			echo "
    </tbody>
  </table>
    <!-- 风险测评html   start   -->
    <div id=\"selectBox\" class=\"selectBox\">
        <div id=\"box_top\"><img src=\"./assets/img/close_btn.png\" width=\"20px\" onclick=\"cancel()\"></div>
        <div id=\"box_content\" >
        </div>
    </div>
        <!-- 我已收款html   start   -->
        <div id=\"get_manual\" style='display: none;'>
            <div id=\"box_top\"><img src=\"./assets/img/close_btn.png\" width=\"20px\" onclick=\"cancel1()\"></div>
            <div id=\"box_content\" style='text-align: center;font-size: 25px;'>
            <label>订单修改</label>";
              if($scan_code_login = true){
            echo " <br><span style='font-size: 20px;'>使用管理员支付宝扫码验证身份</span><br><img src='../api/qrcode.php?data=" .urlencode($scancode). " width='300'
                       height='210' style=' width: 310px; height: 270px;'>
            <input type='hidden' id='order_update_pass' style='border-radius: 5px;width: 300px;margin-top: 20px;margin-bottom: 20px;'><br>";
            }else{
           echo  "<input type='password' id='order_update_pass' style='border-radius: 5px;width: 300px;margin-top: 20px;margin-bottom: 20px;' placeholder='请输入二次验证密码:'><br>";
           }

                if($sms_on_off == true){

                    echo "
                    <script>
                        function send_sms() {
                            $(\"#send_sms_key\").attr(\"disabled\",\"disabled\");
                            var id = $(\"#order_update_submit\").attr(\"title\");
                            var data = $(\"#update_data_list_\"+id).attr(\"title\");
                            data = data.split(\"@\");
                            $.ajax({
                                url:\"../api/ajax_api/ajax_send_sms_api1.php?trade_no=\"+data[0],
                                type:\"get\",
                                dataType:\"json\",
                                data:{},
                                success:function(data){
                                    if(data.success == \"true\"){
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
                    </script>
                    <div style=\"width: 100%;height:34px;margin-bottom: 20px;\">
                        <input type=\"text\" id=\"sms_key\" placeholder=\"短信验证码\" ng-model=\"user.password\" style=\"width:160px;height:34px;padding:6px 12px;border: 1px ;border: 1px solid #ccc;padding: 6px 12px;border-radius: 3px;\">&nbsp;&nbsp;
                        <button class=\"btn btn-primary\" onclick=\"send_sms()\" id=\"send_sms_key\" type=\"button\"><span id=\"fasanniu\">获取短信验证码</span></button>
                    </div>
                    ";
                }


            echo "<a type='button' id='order_update_submit' onclick='get_manual()' style='border-radius: 5px;background-color: #f9961c;padding: 3px 125px;box-shadow: 1px 1px 6px #f76600;'>确定</a>
            </div>
        </div>
        <!-- 我已收款html   end   -->
    <div id=\"shelter\" class=\"shelter\"></div>
    <!-- 风险测评html   end   -->
</div>
";

			require '../includes/page.class.php';
            echo $tj_echo;
#分页
			echo "</div>";
			break;
	}
?>

<script>
    var myTimer;
    //风险测评js
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
    function showSelectBox() {
        var left = 0;
        var top = 0;
        if(IsPC()){
            left = (document.body.clientWidth-400)/2;
            $("#selectBox").css("left",left+"px");
        }
        else{
            left = (document.body.clientWidth-350)/2;
            top = (document.body.clientHeight-350)/2;
            $("#selectBox").css("width","350px");
            $("#selectBox").css("top","15%");
            $("#selectBox").css("left",left+"px");
            $("#box_img_1").css("marginLeft","15px");
        }
        document.getElementById("selectBox").style.display = "block";
        document.getElementById("shelter").style.display = "block";
        sjjzz(1);
    }
    function cancel() {
        document.getElementById("selectBox").style.display = "none";
        document.getElementById("shelter").style.display = "none";
    }
    function cancel1() {
        clearInterval(myTimer);
        $.ajax({
            url:"./clear.php",
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
    function sjjzz(i){
        switch (i) {
            case 1:$("#sjjzz").html("账号检测中.");break;
            case 2:$("#sjjzz").html("账号检测中..");break;
            case 3:$("#sjjzz").html("账号检测中...");break;
            case 4:$("#sjjzz").html("账号检测中....");break;
            case 5:$("#sjjzz").html("账号检测中.....");break;
            case 6:$("#sjjzz").html("账号检测中......");break;
            default:i=0;break;
        }
        i++;
        setTimeout("sjjzz("+i+")",200);
    }
    function risk_evaluation(i) {
        showSelectBox();
        $("#box_content").html('<img id="box_img_1" src="./assets/img/risk_detection_loading.gif" height="287" style="margin: 0 50px;">\n<div id="sjjzz">账号检测中</div>');
        var timestamp = Date.parse(new Date());
        $.ajax({
            url:"../api/ajax_api/ajax_risk_evaluation.php",
            type:"GET",
            data:{
                token:"get_risk_evaluation",
                mobile_url:i
            },
            success:function(data){
                var timestamp1 = Date.parse(new Date());

                data = eval('('+data+')');
                var all_num = parseInt(data["success_order_num"])+parseInt(data["error_order_num"]);
                var all_money = parseFloat(data["success_order_money"])+parseFloat(data["error_order_money"]);
                var cgl = parseFloat(data["success_order_num"])/all_num*100;
                var jeb = parseFloat(data["success_order_money"])/all_money*100;
                var str = "";
                str = "<div style=\"font-size: 30px;color: #9bd9c7;text-align: center;\">账号检测结果</div>\n" +
                    "            <p style=\"float: left;margin-left: 10%; color: #8a7e7e;\">\n" +
                    "                <b style=\"color: dodgerblue;\">--今日订单--</b><br><br>\n" +
                    "                <b>交易成功："+data["success_order_num"]+"</b><br>\n" +
                    "                <b>交易失败："+data["error_order_num"]+"</b><br>\n" +
                    "                <b>总订单："+all_num+"</b><br>\n" +
                    "                <b>成功率：<label style=\"color: #FF5722;\">"+cgl.toFixed(2) +"%</label></b><br>\n" +
                    "            </p>\n" +
                    "            <p  style=\"float: right;margin-right: 10%; color: #8a7e7e;\">\n" +
                    "                <b  style=\"color: dodgerblue;\">--今日流水--</b><br><br>\n" +
                    "                <b>已支付："+data["success_order_money"]+"</b><br>\n" +
                    "                <b>未支付："+data["error_order_money"]+"</b><br>\n" +
                    "                <b>总金额："+all_money+"</b><br>\n" +
                    "                <b>金额比：<label style=\"color: #FF5722;\">"+jeb.toFixed(2)+"%</label></b><br>\n" +
                    "            </p>\n" +
                    "            <div style=\"clear: both;padding: 0 40px;font-size: 15px;color: red;\">温馨提示：如有异常请到通道管理去暂停当前通道的使用！！！</div>";
                if(timestamp-timestamp1<=2000){
                    setTimeout(function(){
                        $("#box_content").html(str);
                    },2000);
                }
                else{
                    $("#box_content").html(str);
                }
            }
        });
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
                "../api/getlogin3.php",
                {
                    userid: '<?php echo $sid;?>',
                },
                function (data) {
                    if (data != '0') {
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
                url:"./manual.php",
                type:"GET",
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
                    if(data.indexOf("二次验证密码错误，修改订单状态失败。")>=0){
                        alert("二次验证密码错误，修改订单状态失败。");
                        $("#order_update_pass").val("");
                        $("#sms_key").val("");
                    }
                    else if(data.indexOf("二次验证密码或者短信验证码错误，修改订单状态失败。")>=0){
                        alert("二次验证密码或者短信验证码错误，修改订单状态失败。");
                        $("#order_update_pass").val("");
                        $("#sms_key").val("");
                    }
                    else if(data.indexOf("保存成功。")>=0){
                        alert("保存成功。");
                        location.reload();
                    }
                    else{
                        alert("保存失败！");
                        $("#order_update_pass").val("");
                        $("#sms_key").val("");
                        cancel1();
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
