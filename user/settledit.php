<?php
/**
 * 修改结算信息
 **/
include("../includes/common.php");
if(!$_SESSION['userid']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '修改结算信息';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";

$header = '
  <!-- content -->
  <div id="content" class="app-content" role="main">
      <div class="app-content-body ">
';
$msg = isset($msg) ? "<div class='alert alert-info'>{$msg}</div>" : "";
echo $header;
isset($_GET['action']) ? $action = daddslashes($_GET['action']) : $action = null;
switch($action){
    case 'edit':
        $id = daddslashes($_REQUEST['id']);
        $pid = daddslashes($_REQUEST['pid']);
//        $yesterday = daddslashes($_GET['yesterday']);
        $sql = "select * from `pay_user` where `id` = '{$id}' limit 1";
        $row = $DB->query($sql)->fetch();
        $sql2 = "SELECT * FROM `pay_settle` WHERE pid = '{$row['id']}' AND (time between '".$yesterday." 00:00:00' AND '{$yesterday} 23:59:59')";
        $sql2 = "SELECT * FROM `pay_apply` WHERE `id` = '{$id}' AND `pid` = '{$pid}';";
        $row_settle = $DB->query($sql2)->fetch();
        $settle = strval($row_settle['money'] - $row_settle['fee']);
        $topay = $row_settle['cash'] / 100;
        $topay_this_time = $topay;
        $row_settle_status = ["","","",""];
        switch($row_settle['status']){
            case '0':
                $row_settle_status[0] = "checked";
                break;
            case '1':
                $row_settle_status[1] = "checked";
                $row_settle_status[0] = "disabled=\"disabled\"";
                $row_settle_status[2] = "disabled=\"disabled\"";
                $row_settle_status[3] = "disabled=\"disabled\"";
                break;
            case '2':
                $row_settle_status[2] = "checked";
                break;
            case '3':
                $row_settle_status[3] = "checked";
                $row_settle_status[0] = "disabled=\"disabled\"";
                $row_settle_status[1] = "disabled=\"disabled\"";
                $row_settle_status[2] = "disabled=\"disabled\"";
                break;
            default:
                break;
        }
        $endtime = $row_settle['endtime'];
        if($endtime == '' || $endtime == '0000-00-00 00:00:00'){
            $endtime = date("Y-m-d H:i:s");
        }
//        $topay =  strval(round($row_settle['alipay']*(1-$row['alipay_fee']),2)+round($row_settle['wxpay']*(1-$row['wxpay_fee']),2)+round($row_settle['qqpay']*(1-$row['qqpay_fee']),2)+round($row_settle['cqpay']*(1-$row['fourpay_fee']),2));
//        $topay_alipay = strval(round($row_settle['alipay']*(1-$row['alipay_fee']),2));
//        $topay_wxpay = strval(round($row_settle['wxpay']*(1-$row['wxpay_fee']),2));
//        $topay_qqpay = strval(round($row_settle['qqpay']*(1-$row['qqpay_fee']),2));
//        $topay_other = strval(round($row_settle['cqpay']*(1-$row['fourpay_fee']),2));
//        $topay_this_time = $row_settle['money'] - $row_settle['fee'];

        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">{$title}</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      {$title}
    </div>
    <div class="panel-body">
        <form action="./settlesave.php?action=edit_submit" class="form-horizontal" method="POST">
            <input type="hidden" name="pid" value="{$pid}">
            <input type="hidden" name="id" value="{$id}">
            <div class="form-group">
                <label class="col-sm-3 control-label">结算日期：</label>
                <div class="col-sm-6">
                    <input type="datetime-local" class="form-control" value="{$endtime}" name="endtime">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">商户号：</label>
                <div class="col-sm-6 control-label" style="text-align: left;">
                    {$row_settle['pid']}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">银行名称：</label>
                 <div class="col-sm-6 control-label" style="text-align: left;">
                    {$row_settle['bank_name']}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">户主姓名：</label>
                <div class="col-sm-6 control-label" style="text-align: left;">
                    {$row_settle['bank_user']}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">银行卡号：</label>
                <div class="col-sm-6 control-label" style="text-align: left;">
                    {$row_settle['bank_no']}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">应付金额：</label>
                <div class="col-sm-6 control-label" style="text-align: left;">
                    {$topay}元。
                </div>
            </div>
            <div class="form-group"><br>
                <label class="col-sm-3 control-label">是否结算：</label>
                <div class="col-sm-9">
                  <div class="col-sm-2"><div class="radio">
                      <label class="i-checks">
                        <input name="status" value="0" {$row_settle_status[0]} type="radio">
                        <i></i>
                        0_待结算
                      </label>
                  </div></div>
                  <div class="col-sm-2"><div class="radio">
                      <label class="i-checks">
                        <input name="status" value="1" {$row_settle_status[1]} type="radio">
                        <i></i>
                        1_已结算
                      </label>
                  </div></div>
                  <div class="col-sm-2"><div class="radio">
                      <label class="i-checks">
                        <input name="status" value="2" {$row_settle_status[2]} type="radio">
                        <i></i>
                        2_已拒绝
                      </label>
                  </div></div>
                  <div class="col-sm-6"><div class="radio">
                      <label class="i-checks">
                        <input name="status" value="3" {$row_settle_status[3]} type="radio">
                        <i></i>
                        3_已拒绝并退回到商户余额
                      </label>
                  </div></div>
                </div>
            </div>
            <div class="form-group"><br>
                <label class="col-sm-3 control-label">本次提现金额：</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="rmb" value="{$topay_this_time}" readonly>
                </div>
                
            </div>
            <div class="form-group"><br>
                <label class="col-sm-3 control-label">结算账户：</label>
                <div class="col-sm-6">
                    <select class="form-control" name="whichcard" default="{$row_settle['whichcard']}">
                        <option value="阿里巴巴_支付宝_{$row['alipay_uid']}_">阿里巴巴_支付宝_{$row['alipay_uid']}_</option>
                        <option value="银行卡1_{$row['bankname']}_{$row['bankcardid']}_{$row['bankxinming']}">银行卡1_{$row['bankname']}_{$row['bankcardid']}_{$row['bankxinming']}</option>
                        <option value="银行卡2_{$row['bankname2']}_{$row['bankcardid2']}_{$row['bankxinming2']}">银行卡2_{$row['bankname2']}_{$row['bankcardid2']}_{$row['bankxinming2']}</option>
                    </select>
                </div>

            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">备注：</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="memos" value="{$row_settle['note2']}" required>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-4">
                    <input type="submit" class="btn btn-primary btn-block" value="确定修改">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-4">
                    <input type="button" class="btn form-control" value="返回" onclick="window.location.href='{$website_urls}admin/slist.php'">
                </div>
            </div>
        </form>
        <br/>
    </div>
  </div>
</div>

<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
	$(items[i]).val($(items[i]).attr("default")||0);
}
</script>
EOF;
        break;
    case 'add_submit':
        $account = $_POST['account'];
        $username = $_POST['username'];
        $money = '0.00';
        $url = $_POST['url'];
        $type = $_POST['type'];
        $active = $_POST['active'];
        if($account == NULL or $username == NULL){
            showmsg('保存错误,请确保加*项都不为空!',3);
        }else{
            $key = random(32);
            $sds = $DB->exec("INSERT INTO `pay_user` (`key`, `account`, `username`, `money`, `url`, `addtime`, `type`, `active`) VALUES ('{$key}', '{$account}', '{$username}', '{$money}', '{$url}', '{$date}', '{$type}', '{$active}')");
            $pid = $DB->lastInsertId();
            if($sds){
                showmsg('添加商户成功！商户ID：'.$pid.'<br/><br/><a href="./ulist.php">>>返回商户列表</a>',1);
            }else
                showmsg('添加商户失败！<br/>错误信息：'.$DB->errorCode(),4);
        }
        break;
    case 'edit_submit':
        $id = daddslashes($_GET['id']);
        $rows = $DB->query("select * from `pay_user` where `id` = '{$id}' limit 1")->fetch();
        if(!$rows){
            showmsg('当前记录不存在！',3);
        }
        $account = daddslashes($_POST['account']);
        $username = daddslashes($_POST['username']);
        $money = $_POST['money'];
        $url = $_POST['url'];
        $type = $_POST['type'];
        $active = $_POST['active'];
        if($account == NULL or $username == NULL){
            showmsg('保存错误,请确保加*项都不为空!',3);
        }else{
            $sql = "update `pay_user` set `account` ='{$account}',`username` ='{$username}',`money` ='{$money}',`url` ='{$url}',`type` ='$type',`active` ='$active' where `id`='$id'";
            if($_POST['resetkey'] == 1){
                $key = random(32);
                $sqs = $DB->exec("update `pay_user` set `key` ='{$key}' where `id`='$id'");
            }
            if($DB->exec($sql) || $sqs) showmsg('修改商户信息成功！<br/><br/><a href="./ulist.php">>>返回商户列表</a>',1);else
                showmsg('修改商户信息失败！'.$DB->errorCode(),4);
        }
        break;
    case 'delete':
        $id = $_GET['id'];
        $rows = $DB->query("select * from pay_user where id='$id' limit 1")->fetch();
        if(!$rows) showmsg('当前记录不存在！',3);
        $urls = explode(',',$rows['url']);
        $sql = "DELETE FROM pay_user WHERE id='$id'";
        if($DB->exec($sql)) showmsg('删除商户成功！<br/><br/><a href="./ulist.php">>>返回商户列表</a>',1);else
            showmsg('删除商户失败！'.$DB->errorCode(),4);
        break;
    default:
        echo '<form action="ulist.php" method="GET" class="form-inline"><input type="hidden" name="action" value="search">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" class="form-control"><option value="id">商户号</option><option value="key">密钥</option><option value="account">结算账号</option><option value="username">结算姓名</option><option value="url">域名</option></select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索内容">
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>&nbsp;<a href="./ulist.php?action=add" class="btn btn-success">添加商户</a>&nbsp;<a href="./plist.php" class="btn btn-default">合作者商户管理</a>
</form>';

        if($action == 'search'){
            $sql = " `{$_GET['column']}`='{$_GET['value']}'";
            $numrows = $DB->query("SELECT * from pay_user WHERE{$sql}")->rowCount();
            $con = '包含 '.$_GET['value'].' 的共有 <b>'.$numrows.'</b> 个商户';
        }else{
            $numrows = $DB->query("SELECT * from pay_user WHERE 1")->rowCount();
            $sql = " 1";
            $con = '共有 <b>'.$numrows.'</b> 个商户';
        }
        echo $con;
        ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>商户号</th>
                    <th>密钥</th>
                    <th>余额</th>
                    <th>结算账号/姓名</th>
                    <th>域名/添加时间</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $pagesize = 30;
                $pages = intval($numrows / $pagesize);
                if($numrows % $pagesize){
                    $pages++;
                }
                if(isset($_GET['page'])){
                    $page = intval($_GET['page']);
                }else{
                    $page = 1;
                }
                $offset = $pagesize * ($page - 1);

                $rs = $DB->query("SELECT * FROM pay_user WHERE{$sql} order by id desc limit $offset,$pagesize");
                while($res = $rs->fetch()){
                    echo '<tr><td><b>'.$res['id'].'</b></td><td>'.$res['key'].'</td><td>'.$res['money'].'</td><td>'.$res['account'].'<br/>'.$res['username'].'</td><td>'.$res['url'].'<br/>'.$res['addtime'].'</td><td>'.($res['active'] == 1 ? '<font color=green>正常</font>' : '<font color=red>封禁</font>').'</td><td><a href="./ulist.php?action=edit&id='.$res['id'].'" class="btn btn-xs btn-info">编辑</a>&nbsp;<a href="./ulist.php?action=delete&id='.$res['id'].'" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此商户吗？\');">删除</a></td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
        echo '<ul class="pagination">';
        $first = 1;
        $prev = $page - 1;
        $next = $page + 1;
        $last = $pages;
        if($page > 1){
            echo '<li><a href="ulist.php?page='.$first.$link.'">首页</a></li>';
            echo '<li><a href="ulist.php?page='.$prev.$link.'">&laquo;</a></li>';
        }else{
            echo '<li class="disabled"><a>首页</a></li>';
            echo '<li class="disabled"><a>&laquo;</a></li>';
        }
        for($i = 1; $i < $page; $i++) echo '<li><a href="ulist.php?page='.$i.$link.'">'.$i.'</a></li>';
        echo '<li class="disabled"><a>'.$page.'</a></li>';
        for($i = $page + 1; $i <= $pages; $i++) echo '<li><a href="ulist.php?page='.$i.$link.'">'.$i.'</a></li>';
        echo '';
        if($page < $pages){
            echo '<li><a href="ulist.php?page='.$next.$link.'">&raquo;</a></li>';
            echo '<li><a href="ulist.php?page='.$last.$link.'">尾页</a></li>';
        }else{
            echo '<li class="disabled"><a>&raquo;</a></li>';
            echo '<li class="disabled"><a>尾页</a></li>';
        }
        echo '</ul>';
#分页
        break;
}
?>


    </div>
    </div>
    <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>