<?php
/**
 * 商户管理（含代理）
 **/
require_once __DIR__.DIRECTORY_SEPARATOR."ulist.php";
return;
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$admin_uuid = $_SESSION['admin_uuid'];
if(isset($_REQUEST['agentuuid'])){
    $agentuuid = $_REQUEST['agentuuid'];
}else{
    $agentuuid = "";
}
switch($agentuuid){
    case "0":
        $title = "商户管理（不含代理）";
        $agentuuid_title = "商户";
        break;
    case "1":
        $title = "代理商管理";
        $agentuuid_title = "代理";
        break;
    default:
        $title = "商户管理（含代理）";
        $agentuuid = "2";
        $agentuuid_title = "商户";
        break;
}
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/autoload.php";
//require_once __DIR__."/../includes/api/debug.php";
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
  <h1 class="m-n font-thin h3">添加{$agentuuid_title}</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      添加{$agentuuid_title}
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">{$agentuuid_title}账号 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="account" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">{$agentuuid_title}姓名 <div style="display:inline-block;color: red;">*</div>：</label>
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
            <input value="返回" class="btn btn-primary form-control" onclick="window.location.href='{$self_url}?agentuuid={$agentuuid}'" type="button">
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
        $row = \epay\user::find_user($id);
        if(!$row){
            echo "用户不存在。";
            exit();
        }

        $type = ["","",""];
        $type[$row['type']] = "selected";
        $active = ["",""];
        $active[$row['active']] = "selected";
        $allowmodi = ["",""];
        $allowmodi[$row['allowmodi']] = "selected";
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
        $edit_active = ["","","","",""];
        switch($item){
            case 'password':
                $edit_active[1] = "active";
                $edit_title = "密码";
                $edit_body = <<< EOF
                <input type="hidden" name="item" value="password">
        <div class="form-group">
          <label class="col-sm-2 control-label">修改密码：</label>
          <div class="col-sm-6">
            <input type="password" class="form-control" name="password" value="">
            <span class="help-block m-b-none"></span>
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
          <label class="col-sm-2 control-label">最低限额：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="cash_level_min" value="{$cash_level['min']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">最高限额：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="cash_level_max" value="{$cash_level['max']}">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">是否结算：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="type">
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
          <label class="col-sm-2 control-label">代理商账号：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="account" value="{$row['account']}" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">代理商名称：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="username" value="{$row['username']}">
          </div>
        </div>
        
        <!--<div class="form-group">
          <label class="col-sm-2 control-label">代理商余额：</label>
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
          <label class="col-sm-2 control-label">是否激活：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="active">
              <option value="0" {$active[0]}>0_封禁</option>
              <option value="1" {$active[1]}>1_激活</option>
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
EOF;
                break;
        }
        print <<< EOF
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">编辑{$agentuuid_title}</h1>
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
        $re = $DB->prepare("select * from `pay_user` where `id` = :id AND `adminuuid` = :adminuuid limit 1");
        $re->execute(["id" => $id, "adminuuid" => $admin_uuid]);
        $row = $re->fetch();
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
    <h1 class="m-n font-thin h3">审核{$agentuuid_title}</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      审核{$agentuuid_title}
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=edit_submit&id={$id}" class="form-horizontal" method="post">
        <input type="hidden" name="item" value="base">
        <div class="form-group">
          <label class="col-sm-2 control-label">{$agentuuid_title}账号：</label>
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
          <label class="col-sm-2 control-label">{$agentuuid_title}余额：</label>
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
            <input type="text" class="form-control" name="fee" value="{$row['fee']}" readonly="readonly">
            <span>【如果服务费为2%，则填写小数形式0.02】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">支付宝扫码费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="alipay_fee" value="{$row['alipay_fee']}" readonly="readonly">
            <span>【如果服务费为2%，则填写小数形式0.02】</span>
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">支付宝H5费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="alipayh5_fee" value="{$row['alipayh5_fee']}" readonly="readonly">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">微信扫码费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="wxpay_fee" value="{$row['wxpay_fee']}" readonly="readonly">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">微信H5费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="wxpayh5_fee" value="{$row['wxpayh5_fee']}" readonly="readonly">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">QQ钱包扫码费率：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="qqpay_fee" value="{$row['qqpay_fee']}" readonly="readonly">
          </div>
          <p class="help-block col-sm-4 red" style="color: red;">* 必填</p>
        </div>
        
        <div class="form-group">
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
          <label class="col-sm-2 control-label">是否激活：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="active">
              <option value="0" {$active[0]}>0_封禁</option>
              <option value="1" {$active[1]}>1_激活</option>
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
        
        <div class="form-group">
          <label class="col-sm-2 control-label">营业执照扫描件</label>
          <div class="col-sm-6">
            <img width="200px;" src="../upload/{$row["file_yyzz"]}">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">有效开户许可证</label>
          <div class="col-sm-6">
            <img width="200px;" src="../upload/{$row["file_open"]}">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">身份证正面</label>
          <div class="col-sm-6">
            <img width="200px;" src="../upload/{$row["file_id_z"]}">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">身份证反面</label>
          <div class="col-sm-6">
            <img width="200px;" src="../upload/{$row["file_id_f"]}">
          </div>
        </div>
               
        <div class="form-group">
          <label class="col-sm-2 control-label">{$agentuuid_title}承诺函</label>
          <div class="col-sm-6">
            <img width="200px;" src="../upload/{$row["file_law"]}">
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
            $user['agentuuid'] = $agentuuid == 1 ? 1 : 0;
            $user['adminuuid'] = $conf['uuid'];
            $user['uid'] = $user['agentuuid'];
            $user['key'] = random(32);
            $user['pwd'] = random(10);
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
            if($result['code'] == 1){
                showmsg("添加{$agentuuid_title}成功！<br><br>
<div style='font-size: 18px;color: black;'>登录地址：{$website_urls}user/<br>商户ID：{$result['userid']}<br>商户密码：{$user['pwd']}<br></div>
<br><br>
<a href='{$self_url}'>>>返回{$agentuuid_title}列表</a>",1);
            }else{
                showmsg("添加{$agentuuid_title}失败！<br/>错误信息：".$result['msg'],4);
            }
        }
        break;
    case 'edit_submit':
        $id = daddslashes($_REQUEST['id']);
        $item = daddslashes($_REQUEST['item']);
        $re = $DB->prepare("select * from `pay_user` where `id` = :id AND `adminuuid` = :adminuuid limit 1");
        $re->execute(["id" => $id, "adminuuid" => $admin_uuid]);
        $row = $re->fetch();
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
                $data['active'] = daddslashes($_REQUEST['active']);
                $data['allowmodi'] = daddslashes($_REQUEST['allowmodi']);
                //$data['uid'] = $_REQUEST['uid'] == '' ? 0 : daddslashes($_REQUEST['uid']);
                $data['coundaccept'] = daddslashes($_REQUEST['coundaccept']);
                $data['note1'] = daddslashes($_REQUEST['note1']);
                break;
            case 'password':
                $data = array();
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
                $data['type'] = daddslashes($_REQUEST['type']);
                $cash_level_min = daddslashes($_REQUEST['cash_level_min']);
                $cash_level_max = daddslashes($_REQUEST['cash_level_max']);
                $cash_level_min = round($cash_level_min,2);
                $cash_level_max = round($cash_level_max,2);
                if($cash_level_max >= $cash_level_min && $cash_level_min >= 0){
                    $data['cash_level'] = json_encode(array("min" => $cash_level_min,"max" => $cash_level_max));
                }
                $uid = daddslashes($_REQUEST['uid']);
                if($uid == ''){
                    $uid = 0;
                }
                break;
            default:
                $data = array();
                break;
        }
        if(!empty($data)){
            $result = \epay\user::update($data,['id' => $id]);
            if($result->rowCount() == 1){
                showmsg("修改{$agentuuid_title}信息成功！<br/><br/><a href='{$self_url}'>>>返回{$agentuuid_title}列表</a>",1);
            }else{
                showmsg('修改{$agentuuid_title}信息失败！'.$DB->errorCode(),4);
            }
        }else{
            showmsg('提交信息为空！',4);
        }
        break;
    case 'delete':
        showmsg("该功能暂停使用",3);
        exit();
        $id = daddslashes($_REQUEST['id']);
        $rows = $DB->query("SELECT * FROM `pay_user` WHERE `adminuuid` = '{$admin_uuid}' AND `id` = '{$id}' AND (`uid` = '1' OR `agentuuid` = '1') limit 1")->fetch();
        if(!$rows){
            showmsg('当前记录不存在！',3);
        }
        $urls = explode(',',$rows['url']);
        $sql = "DELETE FROM `pay_user` WHERE `id` = '{$id}'";
        if($DB->exec($sql)){
            showmsg("删除{$agentuuid_title}成功！<br/><br/><a href='{$self_url}'>>>返回{$agentuuid_title}列表</a>",1);
        }else{
            showmsg('删除{$agentuuid_title}失败！'.$DB->errorCode(),4);
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
        if($action == 'search' && $_REQUEST['column'] != "all"){
            $column = daddslashes($_REQUEST['column']);
            $value = daddslashes($_REQUEST['value']);
            $column_selected[$column] = "selected";
            $sql = " `adminuuid` = '{$admin_uuid}' AND `{$column}` = '{$value}' AND (`uid` = '1' OR `agentuuid` = '1')";
            $sql2 = "SELECT * FROM `pay_user` WHERE {$sql}";
            $numrows = $DB->query($sql2)->rowCount();
            $con = "包含 <span style='color: blue'>{$value}</span> 共有 <b>{$numrows}</b> 个代理";
        }else{
            $column = "all";
            $value = "";
            $sql = " `adminuuid` = '{$admin_uuid}' AND (`uid` = '1' OR `agentuuid` = '1') ";
            $sql2 = "SELECT * FROM `pay_user` WHERE {$sql}";
            $numrows = $DB->query($sql2)->rowCount();
            $con = "共有 <b>{$numrows}</b> 个代理";
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
<input type="hidden" name="agentuuid" value="{$agentuuid}">
  <div class="form-group">
    <label>搜索</label>
    <select name="column" class="form-control">
        <option value="all" {$column_selected['all']}>所有</option>
        <option value="id" {$column_selected['id']}>{$agentuuid_title}ID</option>
        <option value="key" {$column_selected['key']}>密钥</option>
        <option value="account" {$column_selected['account']}>结算账号</option>
        <option value="username" {$column_selected['username']}>结算姓名</option>
        <option value="url" {$column_selected['url']}>域名</option>
	</select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索内容" value="{$value}">
  </div>
  <button type="submit" class="btn btn-primary">查询</button>&nbsp;<a href="{$self_url}?agentuuid={$agentuuid}&action=add" class="btn btn-success">添加{$agentuuid_title}</a>&nbsp;<a href="{$self_url}?agentuuid=2" class="btn btn-default">商户管理（含代理）</a>&nbsp;<a href="{$self_url}?agentuuid=1" class="btn btn-default">代理商管理</a>&nbsp;<a href="{$self_url}?agentuuid=0" class="btn btn-default">商户管理（不含代理）</a>
</form>

{$con}

<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>{$agentuuid_title}ID</th><th>密钥</th><th>余额</th><th>结算账号/姓名</th><th>域名/添加时间</th><th>状态</th><th>操作</th></tr></thead>
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
            switch($res['coundaccept']){
                case 0:
                    $coundaccept = "<a href='{$self_url}?action=confirm&id={$res['id']}' style='color: blue;'>资料未提交</a>";
                    break;
                case '1':
                    $coundaccept = "<a href='{$self_url}?action=confirm&id={$res['id']}' style='color: red;'>{$agentuuid_title}待审核</a>";
                    break;
                case 2:
                    $coundaccept = "<a href='{$self_url}?action=confirm&id={$res['id']}' style='color: red;'>审核不通过</a>";
                    break;
                case 3:
                    $coundaccept = "";
                    break;
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

            echo "<tr><td><b>{$res['id']}</b><br>{$coundaccept}</td><td>{$res['key']}</td><td>{$balance}</td><td>{$res['account']}<br/>{$res['username']}</td><td>{$res['url']}<br/>{$res['addtime']}</td><td>{$status}</td><td><a href=\"{$self_url}?action=edit&id={$res['id']}\" class=\"btn btn-xs btn-info\">编辑</a>&nbsp;<a href=\"{$self_url}?action=delete&id={$res['id']}\" class=\"btn btn-xs btn-danger\" onclick=\"return confirm('你确实要删除此{$agentuuid_title}吗？');\" style='display: none;'>删除</a>&ensp;<a href='login_as.php?id={$res['id']}' class='btn btn-xs btn-success' target='_blank'>作为此用户身份登录</a></td></tr>";
        }
        echo "
    </tbody>
  </table>
</div>
";

include_once __DIR__.DIRECTORY_SEPARATOR.'../includes/page.class.php';
#分页
        echo "</div>";
        break;
}
?>


    </div>
  </div>
  <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>