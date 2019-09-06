<?php
/**
 * 在线充值
 **/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
if($_SESSION['agentuuid'] != '1'){
    exit("<script language='javascript'>window.history.go(-1) ;</script>");
}
$title = '在线充值';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
// require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";

$header = '
  <!-- content -->
  <div id="content" class="app-content" role="main">
      <div class="app-content-body ">
';
isset($msg) ? $msg = "<div class=\"alert alert-info\">{$msg}</div>" : $msg = "";
$trade_no = date("YmdHis").rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
echo $header;
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
      <form action="order_add2.php" class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-sm-3 control-label">充值金额<span style="color: red;">*</span>：</label>
          <div class="col-sm-3">
            <input type="text" class="form-control" name="total_amount" value="100" required>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">支付方式<span style="color: red;">*</span>：</label>
          <div class="col-sm-6">
          
              <div class="col-sm-4">
                <div class="radio">
                  <label class="i-checks">
                    <input name="type" value="alipay" checked="" type="radio">
                    <i></i>
                    支付宝支付
                  </label>
                </div>
              </div>
              
              <div class="col-sm-4" style="display: none;">
                <div class="radio">
                  <label class="i-checks">
                    <input name="type" value="wechat" type="radio">
                    <i></i>
                    微信支付
                  </label>
                </div>
              </div>
              
              <div class="col-sm-4" style="display: none;">
                <div class="radio">
                  <label class="i-checks">
                    <input name="type" value="qq" type="radio">
                    <i></i>
                    QQ钱包支付
                  </label>
                </div>
              </div>

          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">备注：</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="note1" value="">
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-4">
              <input type="hidden" name="out_trade_no" id="out_trade_no" value="{$trade_no}">
    	      <input type="hidden" name="subject" value="在线充值">
    	      <input type="hidden" name="return_url" value="{$website_urls}user/index.php">
    	      <input type="hidden" name="body" value="在线充值">
              <input class="btn btn-success form-control" type="submit" value="立即支付">
              <br>
          </div>
        </div>
        <div class="form-group" style="display: none;">
          <div class="col-sm-offset-3 col-sm-4">
            <input value="返回" class="btn btn-primary form-control" onclick="window.location.href='{$self_url}'" type="button">
            <br>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
EOF;
include 'foot.php';
