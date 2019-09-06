<?php
include("../includes/common.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!--	<head>-->
	<title><?php echo $website_name?> - 在线测试</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
  <link rel="icon" href="//www.71idc.cn/favicon.ico"  type="image/x-icon">
  <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <div class="container" style="padding-top:70px;">
    <div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
      <div class="panel panel-primary">
        <div class="panel-body">
        <form name=alipayment action=epayapi.php method=post target="_blank">
            <div class="input-group">			 
              <span class="input-group-addon"><span class="glyphicon glyphicon-barcode"></span></span>
			   <input size="30" name="WIDout_trade_no" value="<?php echo date("YmdHis").mt_rand(100,999); ?>"  class="form-control" placeholder="商户订单号" />
			   </div>
			<br/>
			<div class="input-group">
              <span class="input-group-addon"><span class="glyphicon glyphicon-shopping-cart"></span></span>
              <input size="30" name="WIDsubject" value="" class="form-control" placeholder="商品名称" required="required" />			   
            </div>
			<br/>
			<div class="input-group">
              <span class="input-group-addon"><span class="glyphicon glyphicon-yen"></span></span>
              <input size="30" name="WIDtotal_fee" readonly value="0.02" class="form-control" placeholder="付款金额" required="required"/>			        
            </div>        			
<br/> 
<center>
<div class="btn-group btn-group-justified" role="group" aria-label="...">
  <!--
  <div class="btn-group" role="group">
    <button type="radio" name="type" value="alipay" class="btn btn-primary">企业支付宝</button>
  </div>
  -->

    <!--
  <div class="btn-group" role="group">
    <button type="radio" name="type" value="wxpay" class="btn btn-info">微信</button>
  </div>
  -->
  <div class="btn-group" role="group">
    <button type="radio" name="type" value="personpay_wx" class="btn btn-primary">个人微信PC免签版</button>
  </div>
  <!--
  <div class="btn-group" role="group">
    <button type="radio" name="type" value="cqpay" class="btn btn-success">第4方京东支付</button>
  </div>  
  -->  
</div>
<p style="text-align:center"><br>&copy; Powered by <a href="wxp://f2f1GiQTEusby6WfQBYkdBBrn66qbaDiLZCb"><?php echo $conf['web_name']?></a>!</p></div>
</center> </div>
          </form>
        </div>
      </div>      
    </div>
  </div>
