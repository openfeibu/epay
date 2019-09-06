<?php
/**
 * 支付宝转账管理
**/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '支付宝转账管理';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
?>

  <!-- content -->
  <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<?php
isset($_REQUEST['action']) ? $action = daddslashes($_REQUEST['action']) : $action = null;
switch ($action){
    case 'add':
        print <<< EOF_ADD
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加支付宝账号</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      添加支付宝账号
    </div>
    <div class="panel-body">
      <form action="{$self_url}" class="form-horizontal" method="post">
        <input type="hidden" name="action" value="add_submit">
        <div class="form-group">
          <label class="col-sm-2 control-label">支付宝姓名 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="username" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">账号 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="alipay_no" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">备注：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" placeholder="可留空">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
       
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="1">1_是（可用）</option>
              <option value="0">0_否（不可用）</option>
            </select><br>
            <span style="color: red;">【账号设置不可用，即无法转账到该账户。】</span>
          </div>
        </div>
        
        
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
              <input value="确定添加" class="btn btn-success form-control" type="submit">
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
EOF_ADD;
        break;
    case 'add_transfer':
        $id = daddslashes($_GET['id']);
        $results = $DB->query("SELECT * FROM `pay_alipayinfo` WHERE `status` = '1' ");
        $result = $results->fetch();
        $select = "";
        while ($result){
            $id = $result['id'];
            $username = $result['username'];
            $alipay_no = $result['alipay_no'];
            $select .= "<option value='{$id}'>{$id}-{$username}-{$alipay_no}</option>";
            $result = $results->fetch();
        }

        $status = ["",""];
        if($row['status'] == 1){
            $status[1] = "selected";
        }else{
            $status[0] = "selected";
        }

        print <<< EOF_EDIT
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">转账到支付宝</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
        转账到支付宝
    </div>
    <div class="panel-body">
      <form action="{$self_url}" class="form-horizontal" method="post">
        <input type="hidden" name="action" value="transfer">
        <div class="form-group">
          <label class="col-sm-2 control-label">转账金额（最少0.1元）<span style="color: red">*</span>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="amount" value="" required placeholder="0.1">
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">支付宝账号选择<span style="color: red">*</span>：</label>
          <div class="col-sm-6">
              <select ui-jq="chosen" class="w-md" name="alipay_no">
                  <option value="0">请选择</option>
                  {$select}
              </select>
          </div>
        </div>
        
        <div class="form-group" style="display: none;">
          <label class="col-sm-2 control-label">二次验证密码<span style="color: red">*</span>：</label>
          <div class="col-sm-6">
            <input type="password" class="form-control" name="twoauth" value="">
          </div>
        </div>
        
        
        <div class="form-group">
          <label class="col-sm-2 control-label">备注：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
              <input value="确定转账" class="btn btn-success form-control" type="submit">
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

<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
	$(items[i]).val($(items[i]).attr("default")||0);
}
</script>
EOF_EDIT;
        break;
    case 'transfer':
        if(isset($_REQUEST['out_trade_no']) && isset($_REQUEST['twoauth'])){
            $out_trade_no = daddslashes($_REQUEST['out_trade_no']);
            $twoauth = daddslashes($_REQUEST['twoauth']);
            if($out_trade_no != '' && $twoauth == $conf['twoauth']){
                //转账处理
                $sql = "SELECT * FROM `pay_alipay_order` WHERE `out_trade_no` = '{$out_trade_no}' limit 1 ";
                $result = $DB->query($sql)->fetch();
                if($result){
                    $id = $result['id'];
                    $username = $result['username'];
                    $alipay_no = $result['alipay_no'];
                    $account = $alipay_no;
                    $money = $result['money'];
                    $out_trade_no = $result['out_trade_no'];
                    if($result['result'] == ''){
                        var_dump($result['result']);
                        require_once __DIR__."/../includes/alipayTransfer/index.php";
                        $aop = new alipayTransfer\Alipay();
                        // $b = $aop->transfer($alipay_no,$money,$out_trade_no,$username);
                        $b = $aop->transfer($alipay_no,$money,$out_trade_no);

                        //日志
                        require_once __DIR__."/../includes/function.php";
                        $str = $b;
                        file_put_contents(__DIR__."/../etc/log/alipay_transfer.log.php",date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL,FILE_APPEND);

                        var_dump($b);
                        $b = json_decode($b,true);
                        var_dump($b);

                        //日志
                        require_once __DIR__."/../includes/function.php";
                        $str = json_encode($b,JSON_UNESCAPED_UNICODE);
                        file_put_contents(__DIR__."/../etc/log/alipay_transfer.log.php",date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL,FILE_APPEND);

                        if($b['alipay_fund_trans_toaccount_transfer_response']['code'] = 10000){
                            $result2 = $b['alipay_fund_trans_toaccount_transfer_response']['msg'];
                            $sql = "UPDATE `pay_alipay_order` SET `result` = '{$result2}' WHERE `out_trade_no` = '{$out_trade_no}' ";
                            $DB->query($sql);
                            exit("<script language='javascript'>alert('转账成功！');top.location='{$self_url}';;</script>");
                        }else{ 
                            $result2 = $b['alipay_fund_trans_toaccount_transfer_response']['msg'];
                            exit("<script language='javascript'>alert('转账失败，失败原因：{$result2}。');history.go(-1);</script>");
                        }
                    }
                }else{
                    var_dump($sql);
                    exit();
                    exit("<script language='javascript'>alert('未知错误，转账失败。');history.go(-1);</script>");
                }

            }else{
                exit("<script language='javascript'>alert('二次验证密码或其他错误，转账失败。');history.go(-1);</script>");
            }
            break;
        }
        if(isset($_REQUEST['amount']) && isset($_REQUEST['alipay_no']) && isset($_REQUEST['twoauth']) && isset($_REQUEST['note1'])){
            $amount = daddslashes($_REQUEST['amount']);
            $alipay_no = daddslashes($_REQUEST['alipay_no']);
            $twoauth = daddslashes($_REQUEST['twoauth']);
            $note1 = daddslashes($_REQUEST['note1']);
        }else{
            exit("<script language='javascript'>alert('填写错误,请确保加*项都不为空！');history.go(-1);</script>");
        }
        //验证金额是否正确
        if(is_numeric($amount)){
            $amount = round($amount,2);
            if($amount < 0.1){
                exit("<script language='javascript'>alert('金额不能小于0.1元！');history.go(-1);</script>");
            }
        }else{
            exit("<script language='javascript'>alert('金额不正确！');history.go(-1);</script>");
        }

        //验证二次密码
        // if($twoauth != $conf['twoauth']){
        //     exit("<script language='javascript'>alert('二次验证密码错误，转账失败。');history.go(-1);</script>");
        // }

        //查找支付宝账号
        $sql = "SELECT * FROM `pay_alipayinfo` WHERE `id` = '{$alipay_no}' limit 1";
        $result = $DB->query($sql)->fetch();
        if(!$result){
            exit("<script language='javascript'>alert('支付宝账号不存在，转账失败。');history.go(-1);</script>");
        }
        $username = $result['username'];
        $alipay_no = $result['alipay_no'];

        //创建订单
        $time = time();
        $now = date("Y-m-d H:i:s",$time);
        $out_trade_no = date("YmdHis",$time).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        $sql = "INSERT INTO `pay_alipay_order` (`id`, `username`, `money`, `alipay_no`, `out_trade_no`, `trade_no`, `result`, `createtime`, `endtime`, `updatetime`, `note1`, `note2`) VALUES (NULL, '{$username}', '{$amount}', '{$alipay_no}', '{$out_trade_no}', '', '', '{$now}', NULL, CURRENT_TIMESTAMP, '', '');";
        $result = $DB->query($sql);
        if($result){
            $id = $DB->lastInsertId();
            exit("<script language='javascript'>alert('保存成功，请审核！');top.location='{$self_url}?action=confirm_trasfer&id={$id}';;</script>");
        }else{
            exit("<script language='javascript'>alert('保存错误,请重试！');history.go(-1);</script>");
        }
        break;
    case 'confirm_trasfer':
        $id = daddslashes($_REQUEST['id']);
        $results = $DB->query("SELECT * FROM `pay_alipay_order` WHERE `id` = '{$id}' ");
        $result = $results->fetch();
        while ($result){
            $id = $result['id'];
            $username = $result['username'];
            $alipay_no = $result['alipay_no'];
            $money = $result['money'];
            $out_trade_no = $result['out_trade_no'];
            $result = $results->fetch();
        }

        $status = ["",""];
        if($row['status'] == 1){
            $status[1] = "selected";
        }else{
            $status[0] = "selected";
        }

        print <<< EOF_EDIT
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">支付宝转账审核</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
        支付宝转账审核
    </div>
    <div class="panel-body">
      <form action="{$self_url}" class="form-horizontal" method="post">
        <input type="hidden" name="action" value="transfer">
        <input type="hidden" name="out_trade_no" value="{$out_trade_no}">
        <div class="form-group">
          <label class="col-sm-2 control-label">转账金额（最少0.1元）<span style="color: red">*</span>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="amount" value="{$money}" readonly="readonly">
          </div>
        </div>
                <div class="form-group">
          <label class="col-sm-2 control-label">姓名<span style="color: red">*</span>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="username" value="{$username}" readonly="readonly">
          </div>
        </div>
                <div class="form-group">
          <label class="col-sm-2 control-label">支付宝账号<span style="color: red">*</span>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="alipay_no" value="{$alipay_no}" readonly="readonly">
          </div>
        </div>

        
        <div class="form-group">
          <label class="col-sm-2 control-label">二次验证密码<span style="color: red">*</span>：</label>
          <div class="col-sm-6">
            <input type="password" class="form-control" name="twoauth" value="">
          </div>
        </div>
        
        
        <div class="form-group">
          <label class="col-sm-2 control-label">备注：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
              <input value="立即转账" class="btn btn-success form-control" type="submit">
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

<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
	$(items[i]).val($(items[i]).attr("default")||0);
}
</script>
EOF_EDIT;
        break;
    case 'edit':
        $id = daddslashes($_GET['id']);
        $row = $DB->query("select * from `pay_alipayinfo` where id='{$id}' limit 1")->fetch();

        $status = ["",""];
        if($row['status'] == 1){
            $status[1] = "selected";
        }else{
            $status[0] = "selected";
        }

        print <<< EOF_EDIT
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">编辑支付宝账号</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      编辑支付宝账号
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=edit_submit&id={$id}" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">姓名：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="username" value="{$row['username']}" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">支付宝账号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="alipay_no" value="{$row['alipay_no']}">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="1" {$status[1]}>1_是</option>
              <option value="0" {$status[0]}>0_否</option>
            </select><br>
            <span style="color: red;">【账号设置不可用，即无法转账到该账户。】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">备注：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="{$row['note1']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
              <input value="确定修改" class="btn btn-success form-control" type="submit">
              <br>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
            <input value="返回" class="btn btn-primary form-control" onclick="window.location.href='{$self_url}?action=alipaylist'" type="button">                 
            <br>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
	$(items[i]).val($(items[i]).attr("default")||0);
}
</script>
EOF_EDIT;
        break;
    case 'confirm':
        $id = daddslashes($_REQUEST['id']);
        $row = $DB->query("select * from `pay_user` where `id` = '{$id}' limit 1")->fetch();
        if ($row['type'] == 1) {
            $type[1] = "selected";
            $type[2] = "";
        } elseif ($row['type'] == 2) {
            $type[1] = "";
            $type[2] = "selected";
        }
        if ($row['active'] == 1) {
            $active[0] = "";
            $active[1] = "selected";
        } else {
            $active[0] = "selected";
            $active[1] = "";
        }
        if ($row['allowmodi'] == 1) {
            $allowmodi[0] = "";
            $allowmodi[1] = "selected";
        } else {
            $allowmodi[0] = "selected";
            $allowmodi[1] = "";
        }
        $coundaccept = ["", "", "", ""];
        switch ($row['coundaccept']){
            case 0:
                $coundaccept[0] = "selected";
                break;
            case 1:
                $coundaccept[1] = "selected";
                break;
            case 2:
                $coundaccept[2] = "selected";
                break;
            case 3:
                $coundaccept[3] = "selected";
                break;
            default:
                $coundaccept[0] = "selected";
                break;
        }
        print <<< EOF_CONFIRM
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">审核商户</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      审核商户
    </div>
    <div class="panel-body">
      <form action="{$self_url}?my=edit_submit&id={$id}" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">商户账号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="account" value="{$row['account']}" required>
          </div>
        </div>
  
        <div class="form-group">
          <label class="col-sm-2 control-label">是否激活：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="active">
              <option value="0" {$active[0]}>0_封禁</option>
              <option value="1" {$active[1]}>1_激活</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">审核</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="coundaccept">
              <option value="0" $coundaccept[0]>0_资料未提交</option>
              <option value="1" $coundaccept[1]>1_待审核</option>
              <option value="2" $coundaccept[2]>2_审核不通过</option>
              <option value="3" $coundaccept[3]>3_审核通过</option>
            </select>
          </div>
        </div>
        

        
        <div class="form-group">
          <label class="col-sm-2 control-label">备注：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="{$row['note1']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
              <input value="确定修改" class="btn btn-success form-control" type="submit">
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

<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
	$(items[i]).val($(items[i]).attr("default")||0);
}
</script>
EOF_CONFIRM;
        break;
    case 'add_submit':
        if(isset($_REQUEST['username']) && isset($_REQUEST['alipay_no']) && isset($_REQUEST['status'])){
            $username = daddslashes($_REQUEST['username']);
            $alipay_no = daddslashes($_REQUEST['alipay_no']);
            $status = daddslashes($_REQUEST['status']);
        }else{
            exit("<script language='javascript'>alert('保存错误,请确保加*项都不为空！');history.go(-1);</script>");
        }
        if ($alipay_no == NULL || $username == NULL) {
            exit("<script language='javascript'>alert('保存错误,请确保加*项都不为空！');history.go(-1);</script>");
        } else {
            $now = date("Y-m-d H:i:s");
            $sql = "INSERT INTO `pay_alipayinfo` (`id`, `username`, `alipay_no`, `status`, `createtime`, `updatetime`, `note1`) VALUES (null , '{$username}', '{$alipay_no}', '{$status}', '{$now}', CURRENT_TIMESTAMP, '{$note1}')";
            $sds = $DB->exec($sql);
            $pid = $DB->lastInsertId();
            if ($sds) {
                showmsg("添加支付宝账号成功！编号：{$pid}<br/><br/><a href='{$self_url}?action=alipaylist'>>>返回支付宝账号列表</a>", 1);
            } else{
                showmsg("添加支付宝账号失败！<br/>错误信息：".$DB->errorCode(), 4);
            }
        }
        break;
    case 'edit_submit':
        $id = daddslashes($_GET['id']);
        $rows = $DB->query("select * from `pay_alipayinfo` where id='{$id}' limit 1")->fetch();
        if (!$rows){
            showmsg('当前记录不存在！', 3);
        }
        $username = daddslashes($_REQUEST['username']);
        $alipay_no = daddslashes($_REQUEST['alipay_no']);
        $status = daddslashes($_REQUEST['status']);
        $note1 = daddslashes($_REQUEST['note1']);
        if (empty($username) || empty($alipay_no)) {
            showmsg('保存错误,请确保加*项都不为空!', 3);
        } else {
            $sql = "UPDATE `pay_alipayinfo` SET `username` ='{$username}', `alipay_no` = '{$alipay_no}', `status` = '{$status}', `note1` = '{$note1}' WHERE `id` = '{$id}'";
            if ($DB->exec($sql) || $sqs) {
                showmsg("修改支付宝账号信息成功！<br/><br/><a href='{$self_url}?action=alipaylist'>>>返回支付宝账号列表</a>", 1);
            } else {
                showmsg('修改支付宝账号信息失败！' . $DB->errorCode(), 4);
            }
        }
        break;
    case 'delete':
        showmsg("该功能暂停使用", 3);
        exit();
        $id = daddslashes($_GET['id']);
        $rows = $DB->query("select * from `pay_user` where `id` = '{$id}' limit 1")->fetch();
        if (!$rows){
            showmsg('当前记录不存在！', 3);
        }
        $urls = explode(',', $rows['url']);
        $sql = "DELETE FROM `pay_user` WHERE `id` = '{$id}'";
        if ($DB->exec($sql)){
            showmsg("删除商户成功！<br/><br/><a href='{$self_url}'>>>返回商户列表</a>", 1);
        }else{
            showmsg('删除商户失败！' . $DB->errorCode(), 4);
        }
        break;
    case 'alipaylist':
        $sql = "SELECT * FROM `pay_alipayinfo`";
        $results = $DB->query($sql);
        $result = $results->fetch();
        print <<< EOF_ALIPAYLIST_HEAD
    <div class="bg-light lter b-b wrapper-md">
        <h1 class="m-n font-thin h3">支付宝列表</h1>
    </div>
    <div class="wrapper-md" ng-controller="FormDemoCtrl">
    <div class="panel panel-default">
    <div class="panel-heading font-bold">
        支付宝列表
    </div>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>编号ID</th><th>姓名</th><th>账号</th><th>添加时间/最近修改时间</th><th>状态</th><th>操作</th></tr></thead>
    <tbody>
EOF_ALIPAYLIST_HEAD;

        while ($result){
            $res = $result;
            if($res['status'] == 1){
                $status = "<font color='blue'>可用</font>";
            }else{
                $status = "<font color='red'>禁用</font>";
            }
            echo "<tr><td><b>{$res['id']}</b></td><td>{$res['username']}</td><td>{$res['alipay_no']}</td><td>{$res['createtime']}<br/>{$res['updatetime']}</td><td>{$status}</td><td><a href='{$self_url}?action=edit&id={$res['id']}'>修改</a></td></tr>";
            $result = $results->fetch();
        }
        print "
            </tbody>
  </table>
</div>
        ";
        break;
    default:
        if ($action == 'search' && $_REQUEST['column'] != "all") {
            $column = daddslashes($_REQUEST['column']);
            $value = daddslashes($_REQUEST['value']);
            $sql = " `{$column}` = '{$value}' ";
            $sql2 = "SELECT * FROM `pay_alipay_order` WHERE {$sql}";
            $numrows = $DB->query($sql2)->rowCount();
            $con = "包含{$_GET['value']}的共有 <b>{$numrows}</b> 个商户";
        } else {
            $numrows = $DB->query("SELECT * FROM `pay_alipay_order` WHERE 1")->rowCount();
            $sql = " 1";
            $con = "共有 <b>{$numrows}</b> 个商户";
        }
        print <<< EOF_DEFAULT
    <div class="bg-light lter b-b wrapper-md">
        <h1 class="m-n font-thin h3">支付宝转账列表</h1>
    </div>
    <div class="wrapper-md" ng-controller="FormDemoCtrl">
    <div class="panel panel-default">
    <div class="panel-heading font-bold">
        支付宝转账列表
    </div>

<form action="{$self_url}" method="GET" class="form-inline">
  <input type="hidden" name="action" value="search">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" class="form-control">
	    <option value="all">所有</option>
	    <option value="trade_no">订单号</option>
	    <option value="out_trade_no">支付宝交易号</option>
	    <option value="username">支付宝姓名</option>
	    <option value="account">支付宝账号</option>
	</select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索内容">
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>&nbsp;
  <a href="{$self_url}?action=add" class="btn btn-success">添加支付宝账号</a>&nbsp;
  <a href="{$self_url}?action=add_transfer" class="btn btn-default">支付宝转账</a>&nbsp;
  <a href="{$self_url}?action=alipaylist" class="btn btn-default">支付宝转账号列表</a>&nbsp;
</form>

{$con}

<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>转账ID</th><th>收款方<br>账号</th><th>金额</th><th>创建时间/完成时间</th><th>订单号/支付宝交易号</th><th>结果</th><th>操作</th></tr></thead>
    <tbody>
EOF_DEFAULT;

        $pagesize = 30;
        $pages = intval($numrows / $pagesize);
        if ($numrows % $pagesize) {
            $pages++;
        }
        if (isset($_GET['page'])) {
            $page = intval($_GET['page']);
        } else {
            $page = 1;
        }
        $offset = $pagesize * ($page - 1);

        $rs = $DB->query("SELECT * FROM `pay_alipay_order` WHERE{$sql} order by id desc limit $offset,$pagesize");
        while ($res = $rs->fetch()) {
            if ($res['active'] == 1) {
                $status = "<font color=green>正常</font>";
            } else {
                $status = "<font color=red>封禁</font>";
            }
            switch ($res['coundaccept']){
                case 0:
                    $coundaccept = "<a href='{$self_url}?my=confirm&id={$res['id']}' style='color: blue;'>资料未提交</a>";
                    break;
                case '1':
                    $coundaccept = "<a href='{$self_url}?my=confirm&id={$res['id']}' style='color: red;'>商户待审核</a>";
                    break;
                case 2:
                    $coundaccept = "<a href='{$self_url}?my=confirm&id={$res['id']}' style='color: red;'>审核不通过</a>";
                    break;
                case 3:
                    $coundaccept = "";
                    break;
            }
            //查询商户余额

            echo "<tr><td><b>{$res['id']}</b><br>{$coundaccept}</td><td>{$res['username']}<br>{$res['alipay_no']}</td><td>{$res['money']}</td><td>{$res['createtime']}<br/>{$res['endtime']}</td><td>{$res['out_trade_no']}<br/>{$res['trade_no']}</td><td>{$res['result']}</td><td><a href=\"{$self_url}?action=confirm_trasfer&id={$res['id']}\" class=\"btn btn-xs btn-info\">编辑</a>&nbsp;</td></tr>";
        }
        echo "
    </tbody>
  </table>
</div>
";
        require '../includes/page.class.php';
#分页
        echo "</div>";
        break;
}
?>


    </div>
  </div>
  <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>