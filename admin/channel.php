<?php
	/**
	 * 通道管理
	 **/
	include("../includes/common.php");
	if(!$_SESSION['is_admin']){
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
	$title = '通道管理';
	if(!isset($_REQUEST['excel'])){
		include './head.php';
	}
	$self_url = $_SERVER['PHP_SELF'];
	//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";

	$header = '
  <!-- content -->
  <div id="content" class="app-content" role="main">
      <div class="app-content-body ">
';

	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
	require_once __DIR__.DIRECTORY_SEPARATOR."channel_config.php";
    require_once __DIR__.DIRECTORY_SEPARATOR."../config/config_base.php";

	switch($action){
		case 'add':
			if(isset($_REQUEST['type']) && $_REQUEST['type'] != ''){
				$type = daddslashes($_REQUEST['type']);
				$type_selected[$type] = "selected";
			}else{
				$type = "";
			}

			$type_option = "";
			foreach($type_name as $key => $value){
				$type_option .= "<option value='{$key}' {$type_selected[$key]}>{$value}</option>";
			}
			$uuid_option = "";
			foreach($all_uuid as $key => $value){
				if($conf['uuid'] == $value['uuid']){
					$selected = "selected";
				}else{
					$selected = "";
				}
				$uuid_option .= "<option value='{$value['uuid']}' {$selected}>{$value['id']}-{$value['type']}-{$value['uuid']}</option>";
			}
			echo $header;
			switch($type){
				case 'alipay':
				case 'alipayh5':
				case 'yimei':
				case 'paiyi':
				case 'ousmd':
				case 'maicheng':
					print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      添加通道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">通道类型：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" id="type" disabled="disabled">
            {$type_option}
            </select>
            <input type="hidden" id="typeid" name="type">
            <script type="text/javascript">
                window.onload = function() {
                  var ossel = document.getElementById("type");
                  var osid = document.getElementById("typeid");
                  osid.value = ossel.value;
                }
            </script>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">归属商户ID：</label>
          <div class="col-sm-10">
            <select ui-jq="chosen" class="w-md" name="uuid" id="uuid" style="width: auto;">
                {$uuid_option}
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">APPID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="appid" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">商户私钥 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="8" class="form-control" name="private_key" value="" required></textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">网站公钥 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="5" class="form-control" name="public_key" value="" required></textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">商品名称 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="subject" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">商品描述（可不填）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="body" value="">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">收款额度 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="total_amount" value="0">
            <span>【单位：元，0表示不限制收款额度】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="0">0_不可用</option>
              <option value="1" selected="selected">1_可用</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">备注（建议填写公司名）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
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
EOF;
					break;
				case 'alipay2':
				case 'wechat2':
				case 'qqpay2':
                case 'yunshanpay':
                case 'ddpay':
                case 'yinshengpay':
					$private_key = random(32);
					print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      添加通道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">通道类型：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" id="type" disabled="disabled">
            {$type_option}
            </select>
            <input type="hidden" id="typeid" name="type">
            <script type="text/javascript">
                window.onload = function() {
                  var ossel = document.getElementById("type");
                  var osid = document.getElementById("typeid");
                  osid.value = ossel.value;
                }
            </script>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">归属商户ID：</label>
          <div class="col-sm-10">
            <select ui-jq="chosen" class="w-md" name="uuid" id="uuid" style="width: auto;">
                {$uuid_option}
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">收款精灵ID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="appid" value="99@" required>
          </div>
        </div>
        <div class="form-group" style="">
          <label class="col-sm-2 control-label">收款精灵密钥 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea rows="2" class="form-control" name="private_key">{$private_key}</textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">userId <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="2" class="form-control" name="public_key" onchange="bank_msg(this,1)" value="" required></textarea>
          </div>
          <label id="bank_msg_1" style="font-size: 20px;margin-top: 10px;"></label>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">商品名称 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="subject" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">商品描述（可不填）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="body" value="">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">收款额度 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="total_amount" value="0">
            <span>【单位：元，0表示不限制收款额度】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="0">0_不可用</option>
              <option value="1" selected="selected">1_可用</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">备注（建议填写公司名）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
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
EOF;
					break;
                case 'bank':
                    print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      添加通道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">通道类型：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" id="type" disabled="disabled">
            {$type_option}
            </select>
            <input type="hidden" id="typeid" name="type">
            <script type="text/javascript">
                window.onload = function() {
                  var ossel = document.getElementById("type");
                  var osid = document.getElementById("typeid");
                  osid.value = ossel.value;
                }
            </script>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">归属商户ID：</label>
          <div class="col-sm-10">
            <select ui-jq="chosen" class="w-md" name="uuid" id="uuid" style="width: auto;">
                {$uuid_option}
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">APPID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="appid" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">商户私钥 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="8" class="form-control" name="private_key" value="111111" required></textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">网站公钥 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="5" class="form-control" name="public_key" value="222222" required></textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">隧道名称 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="subject" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">商品描述（可不填）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="body" value="">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">收款额度 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="total_amount" value="0">
            <span>【单位：元，0表示不限制收款额度】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="0">0_不可用</option>
              <option value="1" selected="selected">1_可用</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">备注（建议填写公司名）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
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
EOF;
                    break;
				case 'kuaikuai':
					print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      添加通道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">通道类型：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" id="type" disabled="disabled">
            {$type_option}
            </select>
            <input type="hidden" id="typeid" name="type">
            <script type="text/javascript">
                window.onload = function() {
                  var ossel = document.getElementById("type");
                  var osid = document.getElementById("typeid");
                  osid.value = ossel.value;
                }
            </script>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">归属商户ID：</label>
          <div class="col-sm-10">
            <select ui-jq="chosen" class="w-md" name="uuid" id="uuid" style="width: auto;">
                {$uuid_option}
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">APPID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="appid" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">md5 key <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="8" class="form-control" name="private_key" value="" required></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">快快商户号 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="5" class="form-control" name="public_key" value="" required></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">商品名称 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="subject" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">商品描述（可不填）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="body" value="">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">收款额度 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="total_amount" value="0">
            <span>【单位：元，0表示不限制收款额度】</span>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="0">0_不可用</option>
              <option value="1" selected="selected">1_可用</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">备注（建议填写公司名）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
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
EOF;
					break;
                case 'ddqr':
                    print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
     钉钉 添加通道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">通道类型：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" id="type" disabled="disabled">
            {$type_option}
            </select>
            <input type="hidden" id="typeid" name="type">
            <script type="text/javascript">
                window.onload = function() {
                  var ossel = document.getElementById("type");
                  var osid = document.getElementById("typeid");
                  osid.value = ossel.value;
                }
            </script>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">归属商户ID：</label>
          <div class="col-sm-10">
            <select ui-jq="chosen" class="w-md" name="uuid" id="uuid" style="width: auto;">
                {$uuid_option}
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">APPID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="appid" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">收款钉钉ID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="8" class="form-control" name="private_key" value="" required></textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">付款钉钉ID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="5" class="form-control" name="public_key" value="" required></textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">商品名称 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="subject" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">钉钉群id<div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="body" value="">
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">收款额度 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="total_amount" value="0">
            <span>【单位：元，0表示不限制收款额度】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="0">0_不可用</option>
              <option value="1" selected="selected">1_可用</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">备注（建议填写公司名）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
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
EOF;
                    break;

                case 'wuyoupay':
                    $private_key_wy = random(32);
                    print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      添加通道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">通道类型：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" id="type" disabled="disabled">
            {$type_option}
            </select>
            <input type="hidden" id="typeid" name="type">
            <script type="text/javascript">
                window.onload = function() {
                  var ossel = document.getElementById("type");
                  var osid = document.getElementById("typeid");
                  osid.value = ossel.value;
                }
            </script>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">归属商户ID：</label>
          <div class="col-sm-10">
            <select ui-jq="chosen" class="w-md" name="uuid" id="uuid" style="width: auto;">
                {$uuid_option}
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">APPID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="appid" value="99@" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">私钥 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="8" class="form-control" name="private_key" value="" required>{$private_key_wy}</textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">无忧商户号 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="5" class="form-control" name="public_key" value="" required>21021</textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">商品名称 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="subject" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">商品描述（可不填）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="body" value="">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">收款额度 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="total_amount" value="0">
            <span>【单位：元，0表示不限制收款额度】</span>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="0">0_不可用</option>
              <option value="1" selected="selected">1_可用</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">备注（建议填写公司名）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
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
EOF;
                    break;
                case 'cntpay':
                    $private_key_wy = random(32);
                    print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      添加通道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=add_submit" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">通道类型：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" id="type" disabled="disabled">
            {$type_option}
            </select>
            <input type="hidden" id="typeid" name="type">
            <script type="text/javascript">
                window.onload = function() {
                  var ossel = document.getElementById("type");
                  var osid = document.getElementById("typeid");
                  osid.value = ossel.value;
                }
            </script>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">归属商户ID：</label>
          <div class="col-sm-10">
            <select ui-jq="chosen" class="w-md" name="uuid" id="uuid" style="width: auto;">
                {$uuid_option}
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">APPID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="appid" value="99@" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">私钥 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="8" class="form-control" name="private_key" value="" required>{$private_key_wy}</textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">cnt用户ID <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="5" class="form-control" name="public_key" value="" required>M1904120104590148</textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">商品名称 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="subject" value="" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">商品描述（可不填）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="body" value="">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">收款额度 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="total_amount" value="0">
            <span>【单位：元，0表示不限制收款额度】</span>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="0">0_不可用</option>
              <option value="1" selected="selected">1_可用</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">备注（建议填写公司名）：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
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
EOF;
                    break;
				case '':
				default:
					print <<< EOF
<div class="bg-light lter b-b wrapper-md">
  <h1 class="m-n font-thin h3">添加通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      请选择您要添加的通道类型
    </div>
    <div class="panel-body">
      <form action="{$self_url}" class="form-horizontal" method="get">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
          <label class="col-sm-2 control-label">通道类型：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="type">
            {$type_option}
            </select>
          </div>
        </div>

        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-4">
              <input value="确定" class="btn btn-success form-control" type="submit">
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
			}

			break;
		case 'edit':
			echo $header;
			$id = daddslashes($_REQUEST['id']);
			$sql = "SELECT * FROM `pay_channel` WHERE `id` = '{$id}' LIMIT 1";
			$row = $DB->query($sql)->fetch();
			//反串note2
			$note2=unserialize($row[note2]);
			$wait_time=isset($note2[wait_time])?$note2[wait_time]:"";
            $ish5=isset($note2[ish5]) && $note2[ish5]==1?"checked='checked'":"";
			//验证二次密码
			if(isset($_REQUEST['twoauth'])){
//				$twoauth = daddslashes($_REQUEST['twoauth']);
//				// 验证二次密码
//				if($twoauth != $conf['twoauth']){
//					exit("<script language='javascript'>alert('二次验证密码错误，请重试。');history.go(-1);</script>");
//				}

                //判断是不是短信验证
                if($sms_on_off == true){
                    $twoauth = daddslashes($_REQUEST['twoauth']);
                    $sms_key = daddslashes($_REQUEST['sms_key']);
                    $file_name = "../config/cache/".md5("sms_code1").".php";
                    $ser = unserialize(str_replace("<?php exit(); ?>","",file_get_contents($file_name)) );
                    // 验证二次密码
                    if($twoauth != $conf['twoauth'] || $sms_key != $ser["token"]){
                        exit("<script language='javascript'>alert('二次验证密码或者短信验证码错误，请重试。');history.go(-1);</script>");
                    }
                }
                else if($email_switch == true){//判断是不是邮箱验证
                    //通过邮箱令牌验证方法
                    $email = daddslashes($_REQUEST["email"]);
                    $twoauth = daddslashes($_REQUEST['twoauth']);
                    $file = "../config/cache/".md5('Channel_Update_Token');
                    $data = unserialize(file_get_contents($file));
                    //var_dump($data);
                    if($data["email"] != $email){
                        exit("<script language='javascript'>alert('有人同时在进行修改操作，请耐心等待几分钟重发令牌。');history.go(-1);</script>");
                    }
                    elseif ($data["token"] != $twoauth){
                        exit("<script language='javascript'>alert('令牌验证失败,请重试。');history.go(-1);</script>");
                    }
                }
                else{//都不是走最初的模式
                    $twoauth = daddslashes($_REQUEST['twoauth']);
                    // 验证二次密码
                    if($twoauth != $conf['twoauth']){
                        exit("<script language='javascript'>alert('二次验证密码错误，请重试。');history.go(-1);</script>");
                    }
                }
			}else{
				print <<< EOF
            <div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">编辑通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      编辑通道
    </div>
    <div class="panel-body">
EOF;
				if($sms_on_off == true){
                    echo "<form action=\"{$self_url}?action=edit&id={$id}\" class=\"form-horizontal\" method=\"post\">
          <div class=\"form-group\" style=\"display: block;\">
          <label class=\"col-sm-2 control-label\">二次验证密码<span style=\"color: red\">*</span>：</label>
          <div class=\"col-sm-6\">
            <input type=\"password\" class=\"form-control\" name=\"twoauth\" value=\"\">
          </div>
        </div>
        
        <script>
                        function send_sms() {
                            $(\"#send_sms_key\").attr(\"disabled\",\"disabled\");
                            $.ajax({
                                url:\"../api/ajax_api/ajax_send_sms_api1.php\",
                                type:\"get\",
                                dataType:\"json\",
                                data:{},
                                success:function(data){
                                    if(data.success == \"true\"){
                                        //$(\"#send_sms_key\").removeAttr(\"disabled\");
                                        alert(data.msg);
                                    }
                                    else{
                                        alert(data.msg);
                                    }
                                }
                            })
                        }
                    </script>
                    <div class=\"form-group\">
                        <label class=\"col-sm-2 control-label\">短信验证码<span style=\"color: red\">*</span>：</label>
                        <div>
                            <input type=\"text\" name=\"sms_key\" ng-model=\"user.password\" style=\"width:490px;height:34px;margin-left: 15px;border: 1px solid #ccc;padding: 6px 12px;border-radius: 3px;\">&nbsp;&nbsp;
                            <button class=\"btn btn-primary\" onclick=\"send_sms()\" id=\"send_sms_key\" type=\"button\"><span id=\"fasanniu\">获取短信验证码</span></button>
                        </div>
                    </div>
        
        <div class=\"form-group\">
          <div class=\"col-sm-offset-2 col-sm-4\">
              <input value=\"确定\" class=\"btn btn-success form-control\" type=\"submit\">
              <br>
          </div>
        </div>
        <div class=\"form-group\">
          <div class=\"col-sm-offset-2 col-sm-4\">
            <input value=\"返回\" class=\"btn btn-primary form-control\" onclick=\"window.location.href='{$self_url}'\" type=\"button\">                 
            <br>
          </div>
        </div>
        
      </form>";
                }
                else if($email_switch == true){
                    echo "<form action=\"{$self_url}?action=edit&id={$id}\" class=\"form-horizontal\" method=\"post\">
      
        <div class=\"form-group\" style=\"display: block;\">
          <label class=\"col-sm-2 control-label\">邮箱<span style=\"color: red\">*</span>：</label>
          <div class=\"col-sm-6\">
            <input type=\"text\" style=\"width:450px;height:34px;border: 1px solid #ccc;padding: 6px 12px;border-radius: 3px;\" name=\"email\" id=\"email\">&nbsp;&nbsp;
            <input value=\"获取令牌\" id='but_email' class=\"btn btn-primary\" style=\"margin-top:10px;padding: 5px 50px;\" onclick=\"send_channel_email_token()\" type=\"button\">  
            <input type=\"hidden\" class=\"form - control\" name=\"back_id\" id=\"back_id\" value=\"\">  
          </div>
        </div>
        
          <div class=\"form-group\" style=\"display: block;\">
          <label class=\"col-sm-2 control-label\">令牌<span style=\"color: red\">*</span>：</label>
          <div class=\"col-sm-6\">
            <input type=\"password\" class=\"form-control\" name=\"twoauth\" value=\"\">
          </div>
        </div>
        
        <div class=\"form-group\">
          <div class=\"col-sm-offset-2 col-sm-4\">
              <input value=\"确定\" class=\"btn btn-success form-control\" type=\"submit\">
              <br>
          </div>
        </div>
        <div class=\"form-group\">
          <div class=\"col-sm-offset-2 col-sm-4\">
            <input value=\"返回\" class=\"btn btn-primary form-control\" onclick=\"window.location.href='{$self_url}'\" type=\"button\">                 
            <br>
          </div>
        </div>
        
      </form>
        <script>
            function send_channel_email_token(){
                if($(\"#email\").val() == \"\"){
                    $(\"#email\").focus();
                    return;
                }
                $('#but_email').attr('disabled','disabled')
                $.ajax({
                    url:\"../api/ajax_api/ajax_send_channel_email_token.php\",
                    type:\"GET\",
                    dataType:\"json\",
                    data:{
                        email:$(\"#email\").val()
                    },
                    success:function (data){
                        if(data.success==\"true\"){
                            $(\"#back_id\").val(data.back_id);
                            $(\"#but_email\").removeAttr(\"disabled\");
                            alert(data.msg);
                        }
                        else{
                            alert(data.msg);
                        }
                    }
                })
            }
        </script>";
                }
                else{
                    echo "<form action=\"{$self_url}?action=edit&id={$id}\" class=\"form-horizontal\" method=\"post\">
          <div class=\"form-group\" style=\"display: block;\">
          <label class=\"col-sm-2 control-label\">二次验证密码<span style=\"color: red\">*</span>：</label>
          <div class=\"col-sm-6\">
            <input type=\"password\" class=\"form-control\" name=\"twoauth\" value=\"\">
          </div>
        </div>
        
        <div class=\"form-group\">
          <div class=\"col-sm-offset-2 col-sm-4\">
              <input value=\"确定\" class=\"btn btn-success form-control\" type=\"submit\">
              <br>
          </div>
        </div>
        <div class=\"form-group\">
          <div class=\"col-sm-offset-2 col-sm-4\">
            <input value=\"返回\" class=\"btn btn-primary form-control\" onclick=\"window.location.href='{$self_url}'\" type=\"button\">                 
            <br>
          </div>
        </div>
        
      </form>";
                }
				print <<< EOF
    </div>
  </div>
</div>
EOF;
				include_once __DIR__."/foot.php";
				exit();
			}

            $back_id = isset($_REQUEST["back_id"])?$_REQUEST["back_id"]:"";
			$type = $row['type'];
			$uuid = $row['uuid'];
			//$type_selected[$type] = "selected";
			$type_option = "";
			$uuid_option = "";
			foreach($type_name as $key => $value){
				if($key == $type){
					$selected = "selected";
				}else{
					$selected = "";
				}
				$type_option .= "<option value='{$key}' {$selected}>{$value}</option>";
			}
			foreach($all_uuid as $key => $value){
				if($value['uuid'] == $uuid){
					$selected = "selected";
				}else{
					$selected = "";
				}
				$uuid_option .= "<option value='{$value['uuid']}' {$selected}>{$value['id']}-{$value['type']}-{$value['uuid']}</option>";
			}
			$status = array("","");
			$status[$row['status']] = "selected";
			//$total_amount = round($row['total_amount'] / 100,2);
			$total_amount = $row['total_amount'];
			print <<< EOF
<div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">编辑通道</h1>
</div>
<div class="wrapper-md" ng-controller="FormDemoCtrl">
  <div class="panel panel-default">
    <div class="panel-heading font-bold">
      编辑通道
    </div>
    <div class="panel-body">
      <form action="{$self_url}?action=edit_submit&id={$id}" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-2 control-label">通道类型：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="type">
            {$type_option}
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">归属商户ID：</label>
          <div class="col-sm-10">
            <select ui-jq="chosen" class="w-md" name="uuid" id="uuid" style="width: auto;">
                {$uuid_option}
            </select>
          </div>
        </div>
        
          <div class="form-group">
              <lable class="col-sm-2 control-label">APPID：</lable>
              <div class="col-sm-9">
                  <input class="form-control" type="text" name="appid" value="{$row['appid']}">
              </div>
          </div>
          
        <div class="form-group">
          <label class="col-sm-2 control-label">商户私钥 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="8" class="form-control" name="private_key" required>{$row['private_key']}</textarea>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">网站公钥 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <textarea type="text" rows="5" class="form-control" onchange="bank_msg(this,2)" name="public_key" required>{$row['public_key']}</textarea>
          </div>
          <label id="bank_msg_2" style="font-size: 20px;margin-top: 10px;"></label>
        </div>
        <div class="form-group">
              <lable class="col-sm-2 control-label">网关地址 <div style="display:inline-block;color: red;">*</div>：</lable>
              <div class="col-sm-9">
                  <input class="form-control" type="text" name="gatewayUrl" value="{$row['gatewayUrl']}">
              </div>
        </div>
        <div class="form-group">
              <lable class="col-sm-2 control-label">异步回调（后台） <div style="display:inline-block;color: red;">*</div>：</lable>
              <div class="col-sm-9">
                  <input class="form-control" type="text" name="notify_url" value="{$row['notify_url']}">
              </div>
        </div>
        <div class="form-group">
              <lable class="col-sm-2 control-label">同步回调（前台） <div style="display:inline-block;color: red;">*</div>：</lable>
              <div class="col-sm-9">
                  <input class="form-control" type="text" name="return_url" value="{$row['return_url']}">
              </div>
        </div>
        <div class="form-group">
              <lable class="col-sm-2 control-label">商品名称 <div style="display:inline-block;color: red;">*</div>：</lable>
              <div class="col-sm-9">
                  <input class="form-control" type="text" name="subject" value="{$row['subject']}">
              </div>
        </div>
        <div class="form-group">
              <lable class="col-sm-2 control-label">描述（可不填）：</lable>
              <div class="col-sm-9">
                  <input class="form-control" type="text" name="body" value="{$row['body']}">
              </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">收款额度 <div style="display:inline-block;color: red;">*</div>：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="total_amount" value="{$total_amount}">
            <span>【单位：元，0表示不限制收款额度】</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="col-sm-2 control-label">是否可用：</label>
          <div class="col-sm-6">
            <select ui-jq="chosen" class="w-md" name="status">
              <option value="0" {$status[0]}>0_不可用</option>
              <option value="1" {$status[1]}>1_可用</option>
            </select>
          </div>
        </div>
        
         <!--加入延时设定-->
         <div class="form-group">
              <lable class="col-sm-2 control-label">延时（可不填）：</lable>
              <div class="col-sm-9">
                  <input class="form-control" type="text" name="wait_time" value="{$wait_time}">
              </div>
        </div>
        
       <!--加入h5设定-->
         <div class="form-group">
              <lable class="col-sm-2 control-label">是否启用h5：</lable>
              <div class="col-sm-9">
                        <label class="i-switch m-t-xs m-r">
                          <input {$ish5} type="checkbox" name="ish5" id="ish5" value="1">
                        </label>
              </div>
        </div>
        
        
        <div class="form-group">
          <label class="col-sm-2 control-label">备注：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="{$row['note1']}">
            <input type="hidden" name="back_id" value="{$back_id}">
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
			//加入对修改通道的监控
			include("../includes/log.php");
			if(isset($_REQUEST['type']) && isset($_REQUEST['uuid']) && isset($_REQUEST['appid']) && isset($_REQUEST['private_key']) && isset($_REQUEST['public_key']) && isset($_REQUEST['subject']) && isset($_REQUEST['total_amount']) && isset($_REQUEST['status'])){
				$data = array();
				$data['type'] = daddslashes($_REQUEST['type']);
				$data['uuid'] = daddslashes($_REQUEST['uuid']);
				$data['appid'] = daddslashes($_REQUEST['appid']);
				$data['private_key'] = daddslashes($_REQUEST['private_key']);
				$data['public_key'] = daddslashes($_REQUEST['public_key']);
				$data['subject'] = daddslashes($_REQUEST['subject']);
				$data['total_amount'] = daddslashes($_REQUEST['total_amount']);
				$data['status'] = daddslashes($_REQUEST['status']);
			}else{
				//var_dump($_REQUEST);
				showmsg('保存错误,请确保加*项都不为空!',3);
				break;
			}
			//var_dump($_REQUEST);
			isset($_REQUEST['body']) ? $data['body'] = daddslashes($_REQUEST['body']) : $body = "";
			isset($_REQUEST['url']) ? $data['url'] = daddslashes($_REQUEST['url']) : $url = "";
			isset($_REQUEST['note1']) ? $data['note1'] = daddslashes($_REQUEST['note1']) : $note1 = "";
			if(empty($data['type']) || empty($data['private_key']) || empty($data['public_key'])){
				showmsg('保存错误,请确保加*项都不为空!',3);
			}else{
				require_once __DIR__."/../config_base.php";
				//$data['total_amount'] = round($data['total_amount'] * 100,0);
				$data['charset'] = "UTF-8";
				$data['sign_type'] = "RSA2";
				$data['createtime'] = date("Y-m-d H:i:s");
				//$sql = "INSERT INTO `pay_channel` (`id`, `type`, `status`, `total_amount`, `appid`, `private_key`, `public_key`, `subject`, `body`, `gatewayUrl`, `return_url`, `notify_url`, `charset`, `sign_type`, `createtime`, `note1`) VALUES (NULL , '{$type}', '{$status}', '{$total_amount}', '{$appid}', '{$private_key}', '{$public_key}','{$subject}','{$body}','{$gatewayUrl}','{$return_url}','{$notify_url}','{$charset}','{$sign_type}','{$createtime}','{$note1}')";
				//$sds = $DB->query($sql);
				$pid = $DB2->insert("pay_channel",$data);
				if($pid){
					showmsg("添加通道成功！通道ID：{$pid}<br/><br/><a href='{$self_url}'>>>返回通道列表</a>",1);
				}else{
					showmsg("添加通道失败！<br/>错误信息：".$DB->errorCode(),4);
				}
			}
			break;
		case 'edit_submit':
			//加入对修改通道的监控
			include("../includes/log.php");
			$id = daddslashes($_REQUEST['id']);
			$row = $DB->query("SELECT * FROM `pay_channel` WHERE `id` = '{$id}' limit 1")->fetch();
			if(!$row){
				showmsg('当前记录不存在！',3);
			}
			//反串note2
			$note2=unserialize($row[note2]);
			if(isset($_REQUEST['id']) && isset($_REQUEST['uuid']) && isset($_REQUEST['type']) && isset($_REQUEST['appid']) && isset($_REQUEST['private_key']) && isset($_REQUEST['public_key']) && isset($_REQUEST['subject']) && isset($_REQUEST['gatewayUrl']) && isset($_REQUEST['return_url']) && isset($_REQUEST['notify_url']) && isset($_REQUEST['total_amount']) && isset($_REQUEST['status'])){
				$data = array();
				$data['id'] = daddslashes($_REQUEST['id']);
				$data['uuid'] = daddslashes($_REQUEST['uuid']);
				$data['type'] = daddslashes($_REQUEST['type']);
				$data['appid'] = daddslashes($_REQUEST['appid']);
				$data['private_key'] = daddslashes($_REQUEST['private_key']);
				$data['public_key'] = daddslashes($_REQUEST['public_key']);
				$data['subject'] = daddslashes($_REQUEST['subject']);
				$data['gatewayUrl'] = daddslashes($_REQUEST['gatewayUrl']);
				$data['return_url'] = daddslashes($_REQUEST['return_url']);
				$data['notify_url'] = daddslashes($_REQUEST['notify_url']);
				$data['total_amount'] = daddslashes($_REQUEST['total_amount']);
				$data['status'] = daddslashes($_REQUEST['status']);

				if(!is_numeric($data['status'])){
					$data['status'] = 1;
				}
				if(isset($_REQUEST['wait_time'])){
					//有延时参数就加入要保存的列表，只取整数
                    $note2['wait_time']=intval(daddslashes($_REQUEST['wait_time']));
                }
                if(isset($_REQUEST['ish5'])){
                    //有延时参数就加入要保存的列表，只取整数
                    $note2['ish5']=intval(daddslashes($_REQUEST['ish5']));
                }else{
                    $note2['ish5']=0;
                }
                $data['note2']=serialize($note2);
				//可选参数
				$data['body'] = isset($_REQUEST['body']) ? daddslashes($_REQUEST['body']) : "";
				$data['note1'] = isset($_REQUEST['note1']) ? daddslashes($_REQUEST['note1']) : "";
			}else{
				showmsg("保存错误。",3);
				break;
			}
			//$sql = "UPDATE `pay_channel` SET `type` = '{$type}', `appid` = '{$appid}',`private_key` ='{$private_key}', `public_key` = '{$public_key}',`subject` = '{$subject}',`body` = '{$body}', `gatewayUrl` = '{$gatewayUrl}', `return_url` = '{$return_url}', `notify_url` = '{$notify_url}', `total_amount` = '{$total_amount}',`status` = '{$status}', `note1` = '{$note1}' WHERE `id`='{$id}'";
			$re = $DB2->update("pay_channel",["id" => $data['id']],$data);
			//写入更改日志
            if(isset($_REQUEST["back_id"])&&$_REQUEST["back_id"]!=""){
                $id = $_REQUEST["back_id"];
                $log_time = date("Y-m-d H:i:s");
                if($re){
                    $data["update_success"] = $re;
                }
                else{
                    $data["update_success"] = "false";
                }
                $note1 = json_encode($_REQUEST,JSON_UNESCAPED_UNICODE );
                $note2 = json_encode($data,JSON_UNESCAPED_UNICODE );
                $sql_log = "UPDATE `pay_smslog` SET `updatetime`='{$log_time}',`note1`='$note1',`note2`='$note2' WHERE `id`='$id'";
                if($re){
                    $DB->query($sql_log);
                }
            }

			if($re){
				showmsg("修改通道信息成功！<br/><br/><a href='{$self_url}'>>>返回通道列表</a>",1);
			}else{
				showmsg('修改通道信息失败！'.$DB->errorCode(),4);
			}
			break;
		case 'delete':
			showmsg("该功能暂停使用",3);
			exit();
			$id = daddslashes($_GET['id']);
			$rows = $DB->query("select * from `pay_channel` where `id` = '{$id}' limit 1")->fetch();
			if(!$rows){
				showmsg('当前记录不存在！',3);
			}
			$urls = explode(',',$rows['url']);
			$sql = "DELETE FROM `pay_channel` WHERE `id` = '{$id}'";
			if($DB->exec($sql)){
				showmsg("删除通道成功！<br/><br/><a href='{$self_url}'>>>返回通道列表</a>",1);
			}else{
				showmsg('删除通道失败！'.$DB->errorCode(),4);
			}
			break;
		default:
			if($action == 'search'){
				$column = daddslashes($_REQUEST['column']);
				$value = daddslashes($_REQUEST['value']);
				$sql = "SELECT * FROM `pay_channel` WHERE `{$column}` LIKE '%{$value}%' ";
				//修正旧代码统计性能BUG
				//$numrows = $DB->query($sql)->rowCount();
				$re=$DB->query("SELECT count(*) as a FROM `pay_channel` WHERE `{$column}` LIKE '%{$value}%' ")->fetch();
				$numrows=$re[0];
				$con = "包含 <span style='color: blue'>{$value}</span> 的共有 <b>{$numrows}</b> 个通道";
			}else{
			    $sql777 = "";
			    $value = "";
			    if(!isset($_REQUEST["select_status"])||$_REQUEST["select_status"]!="all"){
                    $sql777 = " WHERE `status`='1'";
                }

				$sql = "SELECT * FROM `pay_channel` {$sql777}";
				//修正旧代码统计性能BUG
				//$numrows = $DB->query($sql)->rowCount();
				$re=$DB->query("SELECT count(*) as a FROM `pay_channel` {$sql777}")->fetch();
				$numrows=$re[0];
				$con = "共有 <b>{$numrows}</b> 个通道";
			}
			$link = $_REQUEST;
			unset($link['page']);
			$link = http_build_query($link);
			$link = "&".$link;
			echo $header;
			print <<< EOF
    <div class="bg-light lter b-b wrapper-md">
        <h1 class="m-n font-thin h3">通道列表</h1>
    </div>
    <div class="wrapper-md" ng-controller="FormDemoCtrl">
    <div class="panel panel-default">
    <div class="panel-heading font-bold">
        通道列表
    </div>

<form action="{$self_url}" method="GET" class="form-inline">
<input type="hidden" name="action" value="search">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" class="form-control">
	    <option value="id">通道ID</option>
	    <option value="type">通道类型</option>
	    <option value="appid">APPID</option>
	    <option value="uuid">归属商户ID</option>
	    <option value="note1">备注</option>
	</select>
  </div>
  <div class="form-group">
      <input type="text" class="form-control" name="value" value="{$value}" placeholder="搜索内容">
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>&nbsp;
  <a href="{$self_url}?action=add" class="btn btn-success">添加通道</a>&nbsp;
  <a href="./ulist.php" class="btn btn-default">商户管理</a>&nbsp;
  <a href="./channel.php?select_status=all" class="btn btn-default">显示所有通道</a>
</form>

{$con}

<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>通道ID(归属商户)</th><th>类型</th><th>APPID</th><th>总额度</th><th>已用额度</th><th>创建时间/更新时间</th><th>状态</th><th>备注</th><th>APP公钥一键配置</th><th>操作</th><th>测试</th></tr></thead>
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
            $kkk = 0;
			$rs = $DB->query("{$sql} order by id desc limit $offset,$pagesize");
			while($res = $rs->fetch()){
                $kkk++;
				if($res['status'] == 1){
					$status = "<font color=green>可用</font>";
				}else{
					$status = "<font color=red>不可用</font>";
				}
				foreach($all_uuid as $value){
					if($res['uuid'] == $value['uuid']){
						$channel_user = $value['id']."-".$value['type'];
						break;
					}else{
						$channel_user = "暂无";
						$channel_user = "暂无";
					}
				}
				$coundaccept = "";
				$APP_PUBLIC_KEY = array();//数组初始化
				$APP_PUBLIC_KEY["VERSION2"] = $VERSION2;
				$APP_PUBLIC_KEY["VERSIONV1"] = $VERSIONV1;
				$APP_PUBLIC_KEY["VERSIONVKEY"] = $VERSIONVKEY;
				$APP_PUBLIC_KEY["VERSION_RETURN_URL"] = $VERSION_RETURN_URL;
				$APP_PUBLIC_KEY["userids"] = $res["appid"];
				$APP_PUBLIC_KEY["usernames"] = $res["subject"];
				$APP_PUBLIC_KEY["signkey"] = $res["private_key"];
				$APP_PUBLIC_KEY["UPDATE_URL"] = $UPDATE_URL;
                $APP_PUBLIC_KEY["channel_id"] = $res["id"];
                if(substr($res['body'],0,1) == "@"){
                    $APP_PUBLIC_KEY["SOCKET_URL"] = $SOCKET_URL;
                }
				require_once '../includes/AES.class.php';
				$aes = new \com\weimifu\AES\AES("TfbRnWw7WpjtT2SG");
				$APP_PUBLIC_KEY = json_encode($APP_PUBLIC_KEY,JSON_UNESCAPED_UNICODE);
				$APP_PUBLIC_KEY = $aes->encrypt($APP_PUBLIC_KEY);
				$note1 = array();//用于存储公司名称，支付宝名字，支付宝账号
				echo "<tr><td><b>{$res['id']}({$channel_user})</b><br>{$coundaccept}</td><td>{$res['type']}</td><td>{$res['appid']}</td>";
				echo "<td>{$res['total_amount']}</td><td>".($res['used_amount']/100)."</td><td>{$res['createtime']}<br>{$res['updatetime']}</td>";
				echo "<td>{$status}</td><td ><input type='text' readonly style='overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 200px;' value='".$res["note1"]."'></td>";
				echo "<td><input id='APP_PUBLIC_KEY$kkk' readonly='readonly' onmouseover='APP_PUBLIC_KEY_SHOW(event,this)' style='overflow: hidden;text-overflow:ellipsis;white-space: nowrap;width: 200px;' value='{$APP_PUBLIC_KEY}' >&nbsp;&nbsp;<a href=\"javascript:copyAPP_PUBLIC_KEY($kkk);\"  class=\"btn btn-xs btn-info\">一键复制</a><a href='https://www.kuaizhan.com/common/encode-png?large=true&data=".rawurlencode($APP_PUBLIC_KEY)."' title='扫码获取' target='_blank' class=\"btn btn-xs btn-info\">扫码获取</a></td>";
				echo "<td><a href=\"{$self_url}?action=edit&id={$res['id']}\" class=\"btn btn-xs btn-info\">编辑</a>&nbsp;<a href=\"{$self_url}?action=delete&id={$res['id']}\" class=\"btn btn-xs btn-danger\" onclick=\"return confirm('你确实要删除此通道吗？');\" style='display: none;'>删除</a>&ensp;</td>";
				echo "<td>";
				echo "<a href='../api/qrcode.php?data=alipays%3a%2f%2fplatformapi%2fstartapp%3fappId%3d09999988%26actionType%3dtoAccount%26%26goBack%3dYES%26%26userId%3d2088{$res['public_key']}%26%26amount%3d1%26%26memo%3d' title='测试1元' target='_blank' class=\"btn btn-xs btn-info\">¥1</a></td>";
				echo "</tr>";
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
<!-- 加入一键复制js -->
<script type="text/javascript">
    function bank_msg(ev,i){
        var no = ev.value;
        if(no.length==16 || no.length==19){
            $.ajax({
                url:"../api/ajax_api/ajax_Bank_verify_api.php",
                type:"POST",
                dataType:"JSON",
                data:{
                    cardNo:ev.value
                },
                success:function(data){
                    if(data["validated"]=="true"){
                        $("#bank_msg_"+i).css("color","#000000");
                        $("#bank_msg_"+i).html(data["msg"]);
                    }
                    else {
                        $("#bank_msg_"+i).css("color","red");
                        $("#bank_msg_"+i).html(data["msg"]);
                    }
                }
            })
        }

    }

    function copyAPP_PUBLIC_KEY(i) {
        var APP_PUBLIC_KEY = document.getElementById("APP_PUBLIC_KEY"+i);
        APP_PUBLIC_KEY.select(); // 选择对象
        document.execCommand("Copy"); // 执行浏览器复制命令
        alert("已复制好，可贴粘。");
    }

    function mousePos(e){
        var x,y;
        var e = e||window.event;
        return {
            x:e.clientX+document.body.scrollLeft+document.documentElement.scrollLeft,
            y:e.clientY+document.body.scrollTop+document.documentElement.scrollTop
        };
    };

    function APP_PUBLIC_KEY_SHOW(e,ev){
        var div=document.createElement("b");
        var div1=document.createElement("div");
        div.innerHTML = ev.value;
        div1.setAttribute("style","position: absolute;width: 0;height: 0;left:"+(mousePos(e).x-20)+"px;top:"+(mousePos(e).y+10)+"px;border: 10px solid red;border-bottom-color: #35dbf1;border-top: none;border-left-color: transparent;border-right-color: transparent;");
        div.setAttribute("style", "position: absolute;border:1px solid #b7f3fb;left:"+(mousePos(e).x-200)+"px;top:"+(mousePos(e).y+20)+"px;background-color: #ffffff;border-radius:5px;width:400px;padding:15px;word-wrap:break-word; word-break:break-all;");

        ev.parentNode.appendChild(div1);
        ev.parentNode.appendChild(div);
        ev.onmouseout = function() {
            ev.parentNode.removeChild(div);
            ev.parentNode.removeChild(div1);
        }
        //alert(mousePos(e).x+','+mousePos(e).y);
    }

    window.onload = function () {
        var ossel = document.getElementById("type");
        var osid = document.getElementById("typeid");
        osid.value = ossel.value;
    }
</script>