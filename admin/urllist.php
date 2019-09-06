<?php
/**
 * 隧道管理
**/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '隧道管理';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__."/../includes/api/debug.php";
?>

  <!-- content -->
  <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<?php
isset($_REQUEST['action']) ? $action = daddslashes($_REQUEST['action']) : $action = "";
switch($action){
    case 'add':
        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">初始化隧道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      初始化隧道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">要初始化的商户ID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="id" value="" required>
          </div>
        </div>
        
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
              <input value="确定初始化" class="btn btn-success form-control" type="submit">
              <br>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
            <input value="返回" class="btn btn-primary form-control" onclick="window.location.href='{$self_url}'" type="button"><br>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
EOF;
        break;
    case 'edit':
        $id = daddslashes($_REQUEST['id']);
        $row = $DB->query("SELECT * FROM `pay_user_others` WHERE id='$id' limit 1")->fetch();
        $userrow = $row;
        $type = array("","","");
        $allowmodi = array("","");
//        if($row['type'] == 1){
//            $type[1] = "selected";
//            $type[2] = "";
//        } elseif ($row['type'] == 2) {
//            $type[1] = "";
//            $type[2] = "selected";
//        }

        print <<< EOF_EDIT
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">编辑隧道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      编辑隧道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=edit_submit&id={$id}" class="form-horizontal" method="post">
          <div class="form-group">
              <lable class="col-sm-2 control-label">商户ID：</lable>
              <div class="col-sm-9">
                  <input class="form-control" type="text" value="{$userrow['id']}" disabled>
              </div>
          </div>
          
          <div class="form-group">
              <lable class="col-sm-2 control-label">助手编号：</lable>
              <div class="col-sm-9">
                  <input class="form-control" type="text" name="name" value="{$userrow['name']}">
              </div>
          </div>
          
                <div class="form-group">
                    <label class="col-sm-2 control-label">隧道地址(一行一个)（过渡栏，主要以各通道隧道地址为准）</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" type="text" name="mobile_url" rows="10">{$userrow['mobile_url']}</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">支付宝隧道地址(一行一个)</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" type="text" name="mobile_alipay_url" rows="10">{$userrow['mobile_alipay_url']}</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">微信隧道地址(一行一个)</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" type="text" name="mobile_wxpay_url" rows="10">{$userrow['mobile_wxpay_url']}</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">QQ隧道地址(一行一个)</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" type="text" name="mobile_qqpay_url" rows="10">{$userrow['mobile_qqpay_url']}</textarea>
                    </div>
                </div>

        <div class="form-group" style="display: none;">
          <label class="col-sm-2 control-label">是否结算：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="type">
              <option value="1" {$type[1]}>1_是</option>
              <option value="2" {$type[2]}>2_否</option>
            </select><br>
            <span style="color: red;">【结算商户将计分，无需冲手续费；非结算商户不计分，需要先充值后使用。】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否重置密钥？</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="resetkey">
              <option value="0" selected="selected">0_否</option>
              <option value="1" >1_是</option>
            </select>
          </div>
        </div>
    
        
        <div class="form-group" style="display:none;">
          <label class="col-sm-2 control-label">允许前台修改？</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="allowmodi">
              <option value="0" $allowmodi[0]>0_禁止</option>
              <option value="1" $allowmodi[1]>1_允许</option>
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
EOF_EDIT;
        break;
    case 'add_submit':
        if(isset($_REQUEST['id'])){
            $id = daddslashes($_REQUEST['id']);
        }else{
            showmsg('保存错误,请确保加*项都不为空!', 3);
            break;
        }

        //查看商户是否存在
        $sql = "SELECT * FROM `pay_user` WHERE `id` = '{$id}' LIMIT 1; ";
        $result = $DB->query($sql)->fetch();
        if(!$result){
            showmsg('商户ID不存在。', 3);
            break;
        }
        $sql = "INSERT INTO `pay_user_others` (`id`,`return_url`,`callback_url`, `mobile_url`, `note1`, `note2`) VALUES 
('{$id}','','','','','') ;";
        //$sql = "INSERT INTO `pay_user_others` (`key`, `account`, `username`, `money`, `url`, `addtime`, `type`, `active`, `fee`, `alipay_fee`, `wxpay_fee`, `qqpay_fee`, `fourpay_fee`, `alipayh5_fee`, `wxpayh5_fee`) VALUES ('{$key}', '{$account}', '{$username}', '{$money}', '{$url}', '{$date}', '{$type}', '{$active}','{$fee}','{$fee}','{$fee}','{$fee}','{$fee}','{$fee}','{$fee}')";
        $sds = $DB->exec($sql);
        $pid = $DB->lastInsertId();
        if ($sds) {
            showmsg("初始化隧道成功！商户ID：{$pid}<br/><br/><a href='{$self_url}'>>>返回隧道列表</a>", 1);
        } else {
            showmsg("初始化隧道失败！<br/>错误信息：" . $DB->errorCode(), 4);
        }
        break;
    case 'edit_submit':
        $id = daddslashes($_REQUEST['id']);
        $rows = $DB->query("SELECT * FROM `pay_user_others` WHERE `id` = '{$id}' limit 1")->fetch();
        if (!$rows){
            showmsg('当前记录不存在！', 3);
        }
        if(isset($_REQUEST['id']) && isset($_REQUEST['name']) && isset($_REQUEST['mobile_url']) && isset($_REQUEST['mobile_url']) && isset($_REQUEST['mobile_alipay_url']) && isset($_REQUEST['mobile_wxpay_url']) && isset($_REQUEST['mobile_qqpay_url'])){
            $id = daddslashes($_REQUEST['id']);
            $name = daddslashes($_REQUEST['name']);
            $mobile_url = daddslashes($_REQUEST['mobile_url']);
            $mobile_alipay_url = daddslashes($_REQUEST['mobile_alipay_url']);
            $mobile_wxpay_url = daddslashes($_REQUEST['mobile_wxpay_url']);
            $mobile_qqpay_url = daddslashes($_REQUEST['mobile_qqpay_url']);
        }else{
            showmsg("保存错误。",3);
            break;
        }
        $note1 = daddslashes($_REQUEST['note1']);
        $sql = "UPDATE `pay_user_others` SET `name` = '{$name}', `mobile_url` ='{$mobile_url}',`mobile_alipay_url` ='{$mobile_alipay_url}', `mobile_wxpay_url` = '{$mobile_wxpay_url}', `mobile_qqpay_url` = '{$mobile_qqpay_url}', `note1` = '{$note1}' where `id`='{$id}'";

        if ($_POST['resetkey'] == 1) {
            $key = random(32);
            $sqs = $DB->exec("update `pay_user_others` set `key` ='{$key}' where `id`='{$id}'");
        }else{
            $sqs = false;
        }

        if ($DB->exec($sql) || $sqs) {
            showmsg("修改隧道信息成功！<br/><br/><a href='{$self_url}'>>>返回隧道列表</a>", 1);
        } else {
            showmsg('修改隧道信息失败！' . $DB->errorCode(), 4);
        }
        break;
    case 'delete':
        showmsg("该功能暂停使用", 3);
        exit();
        $id = daddslashes($_REQUEST['id']);
        $rows = $DB->query("select * from `pay_user_others` where `id` = '{$id}' limit 1")->fetch();
        if (!$rows){
            showmsg('当前记录不存在！', 3);
        }
        $urls = explode(',', $rows['url']);
        $sql = "DELETE FROM `pay_user_others` WHERE `id` = '{$id}'";
        if ($DB->exec($sql)){
            showmsg("删除隧道成功！<br/><br/><a href='{$self_url}'>>>返回隧道列表</a>", 1);
        }else{
            showmsg('删除隧道失败！' . $DB->errorCode(), 4);
        }
        break;
    default:
        if ($action == 'search') {
            $column = daddslashes($_REQUEST['column']);
            $value = daddslashes($_REQUEST['value']);
            $sql = " `{$column}` = '{$value}' ";
            $numrows = $DB->query("SELECT * from pay_user_others WHERE{$sql}")->rowCount();
            $con = '包含 ' . $_REQUEST['value'] . ' 的共有 <b>' . $numrows . '</b> 个商户';
        } else {
            $numrows = $DB->query("SELECT * from pay_user_others WHERE 1")->rowCount();
            $sql = " 1";
            $con = "共有 <b>{$numrows}</b> 个商户";
        }
        print <<< EOF_DEFAULT
    <div class="bg-light lter b-b wrapper-md">
        <h1 class="m-n font-thin h3">隧道列表</h1>
    </div>
    <div class="wrapper-md" ng-controller="FormDemoCtrl">
    <div class="panel panel-default">
    <div class="panel-heading font-bold">
        隧道列表
    </div>

<form action="{$self_url}" method="GET" class="form-inline">
<input type="hidden" name="action" value="search">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" class="form-control"><option value="id">商户号</option><option value="key">密钥</option><option value="account">结算账号</option><option value="username">结算姓名</option><option value="url">域名</option></select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索内容">
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>&nbsp;<a href="{$self_url}?action=add" class="btn btn-success">初始化商户隧道</a>&nbsp;<a href="./ulist.php" class="btn btn-default">商户管理</a>
</form>

{$con}

<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>商户ID</th><th style="display: none">默认隧道</th><th>助手编号</th><th>密钥</th><th>支付宝隧道</th><th>微信隧道</th><th>QQ隧道</th><th>状态</th><th>操作</th></tr></thead>
    <tbody>
EOF_DEFAULT;

        $pagesize = 30;
        $pages = intval($numrows / $pagesize);
        if ($numrows % $pagesize) {
            $pages++;
        }
        if (isset($_REQUEST['page'])) {
            $page = intval($_REQUEST['page']);
        } else {
            $page = 1;
        }
        $offset = $pagesize * ($page - 1);

        $rs = $DB->query("SELECT * FROM `pay_user_others` WHERE{$sql} order by id desc limit $offset,$pagesize");
        while ($res = $rs->fetch()) {
            if ($res['note1'] == 1) {
                $status = "<font color=green>正常</font>";
            } else {
                $status = "<font color=red>封禁</font>";
            }
            $status = "";
            $coundaccept = "";
            echo "<tr><td><b>{$res['id']}</b><br>{$coundaccept}</td><td style='display: none;'>{$res['mobile_url']}</td><td>{$res['name']}</td><td>{$res['key']}</td><td>{$res['mobile_alipay_url']}</td><td>{$res['mobile_wxpay_url']}</td><td>{$res['mobile_qqpay_url']}</td><td>{$status}</td><td><a href=\"{$self_url}?action=edit&id={$res['id']}\" class=\"btn btn-xs btn-info\">编辑</a>&nbsp;<a href=\"{$self_url}?action=delete&id={$res['id']}\" class=\"btn btn-xs btn-danger\" onclick=\"return confirm('你确实要删除此商户吗？');\" style='display: none;'>删除</a>&ensp;<a href='login_as.php?id={$res['id']}' class='btn btn-xs btn-success'>作为此用户身份登录</a></td></tr>";
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