<?php
header("Content-Type: text/html; charset=utf-8");
if(isset($_REQUEST['userid']) && $_REQUEST['userid'] != ''){
    $userid = $_REQUEST['userid'];
}else{
    print <<< EOF
    <!DOCTYPE html>
    <html>
    <head>
    <title>在线支付Demo</title>
</head>
<body>
<div style="margin: 20px auto;width: 350px;">
<form action='' method='get'>
请输入商户ID：
<input type="text" name="userid">
<input type="submit" value="提交">
</form>
</div>
</body>
</html>
EOF;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>在线支付Demo</title>

    <link rel="stylesheet" href="button.css">
    <link rel="stylesheet" type="text/css" href="pay.css">
    <script type="text/javascript">
        window.onload = function() {
            $("input[name='pay'][value=1]").attr("checked", true);
        }
        function to_change() {
            var obj = document.getElementsByName('pay');
            for ( var i = 0; i < obj.length; i++) {
                if (obj[i].checked == true) {
                    if (obj[i].value == '1') {
                        document.getElementById('type').value = 'alipay';
                        document.getElementById('Submit').value = '支付宝支付';
                    } else if (obj[i].value == '4') {
                        document.getElementById('type').value = 'qq';
                        document.getElementById('Submit').value = 'QQ钱包支付';
                    } else if (obj[i].value == '5') {
                        document.getElementById('type').value = 'wechat';
                        document.getElementById('Submit').value = '微信支付';
                    }
                }
            }
        }
    </script>
</head>
<body>
<form name="form1" id="form1" method="post" action="topay2.php" target="_blank">

    <div1="">
    <table width="550" border="0" align="center" cellpadding="8"
           cellspacing="1" bgcolor="#ffffff">
        <tbody>
        <tr>
            <td colspan="2"><div align="center">
                <strong>请输入支付金额</strong>
            </div>
            </td>
        </tr>
        <tr>
            <td>
                <div align="right" style="">充值金额：</div>
            </td>
            <td>
                <label>
                    <div style="width: 80px">
                        <input type="text" id="money" name="money" value="1.00"/>
                    </div>
                </label>

            </td>
        </tr>

        <tr>
            <td><div align="right">支付方式：</div>
            </td>
            <td>
                <label onclick="to_change()">
                    <div class="fenlei">
                        <input name="pay" type="radio" value="1" checked="checked">支付宝支付
                    </div>
                </label>
                <label onclick="to_change()">
                    <div class="fenleiwx">
                        <input type="radio" name="pay" value="5">微信支付
                    </div>
                </label>
                <label onclick="to_change()" style="visibility: hidden">
                    <div class="fenleicf">
                        <input type="radio" name="pay" value="4">QQ钱包支付
                    </div>
                </label>
            </td>
        </tr>

        <!--
      <tr>
        <td><div align="right">注意：</div></td>
        <td>提交后请不要修改任何数据否则将不能自动<span class="STYLE1">到</span>账</td>
      </tr>
      -->
        <tr>
            <td>
                <div align="right"></div>
            </td>
            <td>
                <label>
                    <input type="hidden" name="type" id="type" value="alipay">
                    <input type="submit" name="Submit" id="Submit" class="button button-pill button-primary" value="支付宝支付">
                </label>
            </td>
        </tr>
        <tr style="display: none;">
            <td>
                <div align="right"></div>
            </td>
            <td>
                <br><br>
                <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=123456&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:123456:51" alt="购买本程序请联系作者QQ:123456" title="购买本程序请联系作者QQ:123456"/></a>     <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=123456&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:123456:51" alt="购买本程序请联系作者QQ:123456" title="购买本程序请联系作者QQ:123456"/></a>     <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=123456&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:123456:51" alt="购买本程序请联系作者QQ:123456" title="购买本程序请联系作者QQ:123456"/></a>     <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=123456&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:123456:51" alt="购买本程序请联系作者QQ:123456" title="购买本程序请联系作者QQ:123456"/></a>     <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=123456&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:123456:51" alt="购买本程序请联系作者QQ:123456" title="购买本程序请联系作者QQ:123456"/></a>
            </td>
        </tr>

        </tbody>
    </table>

    </div>
</form>
</body>
</html>