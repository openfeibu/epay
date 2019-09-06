<?php
/**
 * 商户管理
 **/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
if($_SESSION['agentuuid'] != '1'){
    exit("<script language='javascript'>window.history.go(-1) ;</script>");
}
$title = '商户列表';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/autoload.php";
//require_once __DIR__."/../includes/api/debug.php";
$userid = $_SESSION['userid'];
$sql = "SELECT * FROM `pay_user` WHERE `id` = '{$userid}' && `uid` = '1';";
$user_dl = $DB->query($sql)->fetch();
if(!$user_dl){
    //非代理商用户
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
?>

  <!-- content -->
  <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<?php
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
switch($action){
    case 'add':
        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加商户</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      添加商户
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">商户账号 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="account" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">商户姓名 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="username" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">网站域名：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="url" placeholder="可留空">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">默认服务费 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="fee" value="0.030">
            <span>【如果服务费为2%，则填写小数形式0.02】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否结算：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="type">
              <option value="1">1_是（结算）</option>
              <option value="2">2_否（非结算）</option>
            </select><br>
            <span style="color: red;">【结算商户将计分，无需冲手续费；非结算商户不计分，需要先充值后使用。】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否激活：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="active">
              <option value="0">0_封禁</option>
              <option value="1" selected="selected">1_激活</option>
            </select>
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
    case 'edit':
        $id = isset($_REQUEST['id']) ? daddslashes($_REQUEST['id']) : "";
        $item = isset($_REQUEST['item']) ? $_REQUEST['item'] : "";
        $sql = "SELECT * FROM `pay_user` WHERE `uid` = :uid AND `id` = :id LIMIT 1";
        $conds = ["uid" => $_SESSION['userid'],"id" => $id];
        $row = $DB2->fetchRow($sql,$conds);
        if(!$row){
            var_dump($row);
            var_dump($conds);
            echo "用户不存在。";
            exit();
        }

        $type = ["","",""];
        $type[$row['type']] = "selected";
        $active = ["",""];
        $active[$row['active']] = "selected";
        $allowmodi = ["",""];
        $allowmodi[$row['allowmodi']] = "selected";
        $edit_active = ["","","","",""];
        switch($item){
            case 'password':
                $edit_active[1] = "active";
                $edit_title = "密码";
                $edit_body = <<< EOF
                请联系管理员修改。
EOF;
                break;
            case 'fee':
                $cash_level = json_decode($row['cash_level'],true);
                if(!$cash_level){
                    $cash_level['min'] = 0;
                    $cash_level['max'] = 0;
                }
                $edit_active[2] = "active";
                $edit_title = "费率设置";
                $edit_body = <<< EOF
                <input type="hidden" name="item" value="fee">
                <input type="hidden" class="form-control" name="uid" value="{$row['uid']}">
        <div class="form-group">
          <label class="col-sm-2 control-label">默认服务费：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="fee" value="{$row['fee']}">
            <span>【如果服务费为2%，则填写小数形式0.02】</span>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">支付宝扫码费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="alipay_fee" value="{$row['alipay_fee']}">
            <span>【如果服务费为2%，则填写小数形式0.02】</span>
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">支付宝H5费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="alipayh5_fee" value="{$row['alipayh5_fee']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">微信扫码费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="wxpay_fee" value="{$row['wxpay_fee']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">微信H5费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="wxpayh5_fee" value="{$row['wxpayh5_fee']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">QQ钱包扫码费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="qqpay_fee" value="{$row['qqpay_fee']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        <div class="form-group" style="display: none;">
          <label class="col-sm-2 control-label">最低限额：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="cash_level_min" value="{$cash_level['min']}" disabled="disabled">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        <div class="form-group" style="display: none;">
          <label class="col-sm-2 control-label">最高限额：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="cash_level_max" value="{$cash_level['max']}" disabled="disabled">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">是否结算（您无权限修改此项，如需修改，请联系管理员）：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="type" disabled="disabled">
              <option value="1" {$type[1]}>1_是（结算）</option>
              <option value="2" {$type[2]}>2_否（非结算）</option>
            </select><br>
            <span style="color: red;">【结算商户将计分，无需冲手续费；非结算商户不计分，需要先充值后使用。】</span>
          </div>
        </div>
EOF;
                break;
            case 'base':
            default:
            $edit_active[0] = "active";
            $edit_title = "基本信息";
            $edit_body = <<< EOF
            <input type="hidden" name="item" value="base">
        <div class="form-group">
          <label class="col-sm-2 control-label">商户账号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="account" value="{$row['account']}" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">公司名称：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="username" value="{$row['username']}">
          </div>
        </div>
        
        <!--<div class="form-group">
          <label class="col-sm-2 control-label">商户余额：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="money" value="{$row['money']}">
          </div>
        </div>-->
        
        <div class="form-group">
          <label class="col-sm-2 control-label">公司网站：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="url" value="{$row['url']}" placeholder="可留空">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">ICP备案号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_icp" value="{$row['com_icp']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">营业执照编号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_id" value="{$row['com_id']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">负责人姓名：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_man" value="{$row['com_man']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">联系电话：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_phone" value="{$row['com_phone']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">负责人身份证号码：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_man_id" value="{$row['com_man_id']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
       
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否激活（您无权限修改此项，如需修改，请联系管理员）：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="active" disabled="disabled">
              <option value="0" {$active[0]}>0_封禁</option>
              <option value="1" {$active[1]}>1_激活</option>
            </select>
          </div>
        </div>
EOF;
                break;
        }
        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">编辑商户</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold" style="padding-bottom: 0px;border-bottom: 0px;">
        <div class="tab-container">
          <ul class="nav nav-tabs">
            <li class="{$edit_active[0]}"><a href="{$self_url}?action=edit&id={$id}&item=base">基本信息</a></li>
            <li class="{$edit_active[1]}"><a href="{$self_url}?action=edit&id={$id}&item=password">密码</a></li>
            <li class="{$edit_active[2]}"><a href="{$self_url}?action=edit&id={$id}&item=fee">费率及限额</a></li>
          </ul>
        </div>
    </div>

    <div class="panel-body">
        <form action="{$self_url}?action=edit_submit&id={$id}" class="form-horizontal" method="post">
            {$edit_body}
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
EOF;
        break;
    case 'confirm':
        $id = daddslashes($_REQUEST['id']);
        $row = $DB->query("select * from `pay_user` where `id` = '{$id}' limit 1")->fetch();
        if($row['type'] == 1){
            $type[1] = "selected";
            $type[2] = "";
        }elseif($row['type'] == 2){
            $type[1] = "";
            $type[2] = "selected";
        }
        if($row['active'] == 1){
            $active[0] = "";
            $active[1] = "selected";
        }else{
            $active[0] = "selected";
            $active[1] = "";
        }
        if($row['allowmodi'] == 1){
            $allowmodi[0] = "";
            $allowmodi[1] = "selected";
        }else{
            $allowmodi[0] = "selected";
            $allowmodi[1] = "";
        }
        $coundaccept = ["","","",""];
        switch($row['coundaccept']){
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
        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">审核商户</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      审核商户
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=edit_submit&id={$id}" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">商户账号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="account" value="{$row['account']}" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">公司名称：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="username" value="{$row['username']}">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">商户余额：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="money" value="{$row['money']}">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">公司网站：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="url" value="{$row['url']}" placeholder="可留空">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">ICP备案号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_icp" value="{$row['com_icp']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">营业执照编号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_id" value="{$row['com_id']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">负责人姓名：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_man" value="{$row['com_man']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">联系电话：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_phone" value="{$row['com_phone']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">负责人身份证号码：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="com_man_id" value="{$row['com_man_id']}">
            <span class="help-block m-b-none"></span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">默认服务费：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="fee" value="{$row['fee']}">
            <span>【如果服务费为2%，则填写小数形式0.02】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">支付宝扫码费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="alipay_fee" value="{$row['alipay_fee']}">
            <span>【如果服务费为2%，则填写小数形式0.02】</span>
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">支付宝H5费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="alipayh5_fee" value="{$row['alipayh5_fee']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">微信扫码费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="wxpay_fee" value="{$row['wxpay_fee']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">微信H5费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="wxpayh5_fee" value="{$row['wxpayh5_fee']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">QQ钱包扫码费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="qqpay_fee" value="{$row['qqpay_fee']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否结算（您无权限修改此项，如需修改，请联系管理员）：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="type" disabled="disabled">
              <option value="1" {$type[1]}>1_是（结算）</option>
              <option value="2" {$type[2]}>2_否（非结算）</option>
            </select><br>
            <span style="color: red;">【结算商户将计分，无需冲手续费；非结算商户不计分，需要先充值后使用。】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否激活（您无权限修改此项，如需修改，请联系管理员）：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="active" disabled="disabled">
              <option value="0" {$active[0]}>0_封禁</option>
              <option value="1" {$active[1]}>1_激活</option>
            </select>
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

        <div class="form-group">
          <label class="col-sm-2 control-label">允许前台修改？</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="allowmodi">
              <option value="0" $allowmodi[0]>0_禁止</option>
              <option value="1" $allowmodi[1]>1_允许</option>
            </select>
          </div>
        </div>
        
        <div class="form-group" style="display: none;">
          <label class="col-sm-2 control-label">修改密码：</label>
          <div class="col-sm-6">
            <input type="password" class="form-control" name="password" value="">
            <span class="help-block m-b-none"></span>
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
EOF;
        break;
    case 'add_submit':
        if(isset($_REQUEST['account']) && isset($_REQUEST['username']) && isset($_REQUEST['fee']) && isset($_REQUEST['type']) && isset($_REQUEST['active'])){
            $account = daddslashes($_REQUEST['account']);
            $username = daddslashes($_REQUEST['username']);
            $fee = daddslashes($_REQUEST['fee']);
            $type = daddslashes($_REQUEST['type']);
            $active = daddslashes($_REQUEST['active']);
        }else{
            showmsg('保存错误,请确保加*项都不为空!',3);
            break;
        }
        $money = '0.00';
        isset($_REQUEST['url']) ? $url = daddslashes($_REQUEST['url']) : $url = "";
        if($account == NULL or $username == NULL){
            showmsg('保存错误,请确保加*项都不为空!',3);
        }else{
            $user = array();
            $user['uuid'] = \Webpatser\Uuid\Uuid::generate(4)->string;
            $user['agentuuid'] = $userrow['uuid'];
            $user['adminuuid'] = $conf['uuid'];
            $user['uid'] = $userrow['id'];
            $user['key'] = random(32);
            $zifusj=rand(0,7);
            $user['pwd'] = random(6).substr("!@#$%^&*",$zifusj,1).substr("HIJKLMNX",$zifusj,1).substr("opqrstuv",$zifusj,1).substr("85236974",$zifusj,1);
            $user['account'] = $account;
            $user['username'] = $username;
            $user['money'] = $money;
            $user['url'] = $url;
            $user['addtime'] = date("Y-m-d H:i:s");
            $user['type'] = $type;
            $user['active'] = $active;
            $user['fee'] = $fee;
            $user['alipay_fee'] = $fee;
            $user['wxpay_fee'] = $fee;
            $user['qqpay_fee'] = $fee;
            $user['fourpay_fee'] = $fee;
            $user['alipayh5_fee'] = $fee;
            $user['wxpayh5_fee'] = $fee;
            $result = \epay\user::create($user);
            //检查费率
            if($fee < $userrow['fee'] || $fee < $userrow['alipay_fee'] || $fee < $userrow['alipayh5_fee'] || $fee < $userrow['wxpay_fee'] || $fee < $userrow['wxpayh5_fee'] || $fee < $userrow['qqpay_fee']){
                $result = "<script>alert('商户的费率不能低于代理的费率');history.go(-1);</script>";
                exit($result);
            }
            if($result['code'] == 1){
                showmsg("添加商户成功！<br><br>
<div style='font-size: 18px;color: black;'>登录地址：{$website_urls}user/<br>商户ID：{$result['userid']}<br>商户密码：{$user['pwd']}<br></div>
<br><br>
<a href='{$self_url}'>>>返回商户列表</a>",1);
            }else{
                showmsg("添加商户失败！<br/>错误信息：".$result['msg'],4);
            }
        }
        break;
    case 'edit_submit':
        $id = daddslashes($_REQUEST['id']);
        $item = daddslashes($_REQUEST['item']);
        $sql = "SELECT * FROM `pay_user` WHERE `uid` = '{$userid}' AND `id` = '{$id}' LIMIT 1";
        $rows = $DB->query($sql)->fetch();
        if(!$rows){
            showmsg('当前记录不存在！',3);
        }
        switch($item){
            case 'base':
                $data = array();
                $data['account'] = daddslashes($_REQUEST['account']);
                $data['username'] = daddslashes($_REQUEST['username']);
                //$data['money'] = daddslashes($_REQUEST['money']);
                $data['url'] = daddslashes($_REQUEST['url']);
                $data['com_icp'] = daddslashes($_REQUEST['com_icp']);
                $data['com_id'] = daddslashes($_REQUEST['com_id']);
                $data['com_man'] = daddslashes($_REQUEST['com_man']);
                $data['com_phone'] = daddslashes($_REQUEST['com_phone']);
                $data['com_man_id'] = daddslashes($_REQUEST['com_man_id']);
                //$data['active'] = daddslashes($_REQUEST['active']);
                //$data['allowmodi'] = daddslashes($_REQUEST['allowmodi']);
                //$data['password'] = daddslashes($_REQUEST['password']);
                //$note1 = daddslashes($_REQUEST['note1']);
                break;
            case 'password':
                $data = array();
                break;
                if(daddslashes($_REQUEST['password']) != ''){
                    $data['pwd'] = daddslashes($_REQUEST['password']);
                }
                if($_REQUEST['resetkey'] == 1){
                    $data['key'] = random(32);
                }
                break;
            case 'fee':
                $data = array();
                $data['fee'] = daddslashes($_REQUEST['fee']);
                $data['alipay_fee'] = daddslashes($_REQUEST['alipay_fee']);
                $data['alipayh5_fee'] = daddslashes($_REQUEST['alipayh5_fee']);
                $data['wxpay_fee'] = daddslashes($_REQUEST['wxpay_fee']);
                $data['wxpayh5_fee'] = daddslashes($_REQUEST['wxpayh5_fee']);
                $data['qqpay_fee'] = daddslashes($_REQUEST['qqpay_fee']);
                //$data['type'] = daddslashes($_REQUEST['type']);
                //$cash_level_min = daddslashes($_REQUEST['cash_level_min']);
                //$cash_level_max = daddslashes($_REQUEST['cash_level_max']);
                //$cash_level_min = round($cash_level_min,2);
                //$cash_level_max = round($cash_level_max,2);
                //if($cash_level_max >= $cash_level_min && $cash_level_min >= 0){
                //    $data['cash_level'] = json_encode(array("min" => $cash_level_min,"max" => $cash_level_max));
                //}
                //检查费率是否低于代理商费率
                if($data['fee'] < $user_dl['fee'] || $data['alipayh5_fee'] < $user_dl['alipay_fee'] || $data['alipayh5_fee'] < $user_dl['alipayh5_fee'] || $data['wxpay_fee'] < $user_dl['wxpay_fee'] || $data['wxpayh5_fee'] < $user_dl['wxpayh5_fee'] || $data['qqpay_fee'] < $user_dl['qqpay_fee']){
                    $result = "<script>alert('商户的费率不能低于代理的费率');history.go(-1);</script>";
                    exit($result);
                }
                break;
            default:
                $data = array();
                break;
        }
        if(!empty($data)){
            $conds = ["id" => $id, "uid" => $userid];
            $result = $DB2->update("pay_user",$conds,$data);
            if($result){
                showmsg("修改商户信息成功！<br/><br/><a href='{$self_url}'>>>返回商户列表</a>",1);
            }else{
                showmsg('修改商户信息失败！',4);
            }
        }else{
            showmsg('提交信息为空！',4);
        }
        break;
    case 'delete':
        showmsg("该功能暂停使用",3);
        exit();
        $id = daddslashes($_REQUEST['id']);
        $rows = $DB->query("select * from `pay_user` where `uid` = '{$userid}' and `id` = '{$id}' limit 1")->fetch();
        if(!$rows){
            showmsg('当前记录不存在！',3);
        }
        $urls = explode(',',$rows['url']);
        $sql = "DELETE FROM `pay_user` WHERE `uid` = '{$userid}' AND `id` = '{$id}'";
        if($DB->query($sql)){
            showmsg("删除商户成功！<br/><br/><a href='{$self_url}'>>>返回商户列表</a>",1);
        }else{
            showmsg('删除商户失败！'.$DB->errorCode(),4);
        }
        break;
    default:
        $column_selected = [
            'all' => '',
            'id' => '',
            'key' => '',
            'account' => '',
            'username' => '',
            'url' => '',
            'money' => '',
            'type' => '',
        ];
        if($action == 'search' && $_REQUEST['value'] != ""){
            $column = daddslashes($_REQUEST['column']);
            $value = daddslashes($_REQUEST['value']);
            $column_selected[$column] = "selected";
            $sql = " `uid` = '{$userid}' AND `{$column}` = '{$value}'";
            $sql2 = "SELECT * FROM `pay_user` WHERE {$sql}";
            $numrows = $DB->query($sql2)->rowCount();
            $con = "包含 <span style='color: blue'>{$value}</span> 共有 <b>{$numrows}</b> 个商户";
        }else{
            $column = "all";
            $value = "";
            $sql = " `uid` = '{$userid}'";
            $sql2 = "SELECT * FROM `pay_user` WHERE {$sql}";
            $numrows = $DB->query($sql2)->rowCount();
            $con = "共有 <b>{$numrows}</b> 个商户";
        }
        $link = $_REQUEST;
        unset($link['page']);
        $link = http_build_query($link);
        $link = "&".$link;
        print <<< EOF
    <div class="bg-light lter b-b wrapper-md">
        <h1 class="m-n font-thin h3">{$title}</h1>
    </div>
    <div class="wrapper-md" ng-controller="FormDemoCtrl">
    <div class="panel panel-default">
    <div class="panel-heading font-bold">
        {$title}
    </div>

<form action="{$self_url}" method="GET" class="form-inline">
<input type="hidden" name="action" value="search">
  <div class="form-group">
    <label>搜索</label>
    <select name="column" class="form-control">
        <option value="id" {$column_selected['id']}>商户ID</option>
        <option value="key" {$column_selected['key']}>密钥</option>
        <option value="account" {$column_selected['account']}>结算账号</option>
        <option value="username" {$column_selected['username']}>结算姓名</option>
        <option value="url" {$column_selected['url']}>域名</option>
	</select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索内容" value="{$value}">
  </div>
  <button type="submit" class="btn btn-primary">查询</button>&nbsp;<a href="{$self_url}?action=add" class="btn btn-success">添加商户</a>&nbsp;<a href="./plist.php" class="btn btn-default">合作者商户管理</a>
</form>

{$con}

<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>商户ID</th><th>密钥</th><th>余额</th><th>结算账号/姓名</th><th>域名/添加时间</th><th>状态</th><th>操作</th></tr></thead>
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

        $sql3 = "SELECT * FROM `pay_user` WHERE {$sql} order by `id` DESC limit $offset,$pagesize";
        $rs = $DB->query($sql3);
        while($res = $rs->fetch()){
            if($res['active'] == 1){
                $status = "<font color=green>正常</font>";
            }else{
                $status = "<font color=red>封禁</font>";
            }
            //查询商户余额
            $userid = $res['id'];
            $sql = "SELECT * FROM `pay_balance` WHERE `id` = '{$userid}' LIMIT 1";
            $balance = $DB->query($sql)->fetch();
            if($balance){
                $balance = round($balance['balance']/100,2);
            }else{
                $balance = '0.00';
            }

            echo "<tr><td><b>{$res['id']}</b></td><td>{$res['key']}</td><td>{$balance}</td><td>{$res['account']}<br/>{$res['username']}</td><td>{$res['url']}<br/>{$res['addtime']}</td><td>({$status})</td><td><a href=\"{$self_url}?action=edit&id={$res['id']}\" class=\"btn btn-xs btn-info\">编辑</a>&nbsp;<a href=\"{$self_url}?action=delete&id={$res['id']}\" class=\"btn btn-xs btn-danger\" onclick=\"return confirm('你确实要删除此商户吗？');\" style='display: none;'>删除</a>&ensp;</td></tr>";
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