<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/common.php";
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='../login.php';</script>");
}
$self_url = $_SERVER['PHP_SELF'];
$pay_helper = $APP_FILE_NAME;
PRINT <<< EOF
<!DOCTYPE html>
<html lang="zh-cn"
      class=" js flexbox flexboxlegacy canvas canvastext webgl no-touch geolocation postmessage websqldatabase indexeddb hashchange history draganddrop websockets rgba hsla multiplebgs backgroundsize borderimage borderradius boxshadow textshadow opacity cssanimations csscolumns cssgradients cssreflections csstransforms csstransforms3d csstransitions fontface generatedcontent video audio localstorage sessionstorage webworkers applicationcache svg inlinesvg smil svgclippaths">
<!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <title>App下载页</title>
    <meta name="language" content="cn">

    <!-- Mobile Meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <!-- Web Fonts -->


    <!-- Bootstrap core CSS -->

    <link href="./wmfAPI_files/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome CSS -->
    <link href="./wmfAPI_files/font-awesome.min.css" rel="stylesheet">

    <!-- Fontello CSS -->
    <link href="./wmfAPI_files/fontello.css" rel="stylesheet">
    <link href="./wmfAPI_files/iconfont.css" rel="stylesheet">

    <!-- Plugins -->
    <link href="./wmfAPI_files/settings.css" media="screen" rel="stylesheet">
    <link href="./wmfAPI_files/extralayers.css" media="screen" rel="stylesheet">
    <link href="./wmfAPI_files/magnific-popup.min.css" rel="stylesheet">
    <link href="./wmfAPI_files/animations.css" rel="stylesheet">
    <link href="./wmfAPI_files/owl.carousel.min.css" rel="stylesheet">
    <link href="./wmfAPI_files/toastr.min.css" rel="stylesheet">

    <link href="./wmfAPI_files/style.css" rel="stylesheet">

    <!-- Color Scheme (In order to change the color scheme, replace the red.css with the color scheme that you prefer)-->
    <link href="./wmfAPI_files/green.css" rel="stylesheet">

    <!-- Custom css -->
    <link href="./wmfAPI_files/custom.css" rel="stylesheet">
	<script type="text/javascript" src="jquery-1.7.2.js"></script>

</head>

<!-- body classes:
        "boxed": boxed layout mode e.g. <body class="boxed">
        "pattern-1 ... pattern-9": background patterns for boxed layout mode e.g. <body class="boxed pattern-1">
-->
<body class="front scroll-spy">

<!-- page-intro end -->
<!-- main-container start -->
<!-- ================ -->
<section class="main-container">

    <div class="container">
        <div class="row">

            <!-- sidebar start -->
            <aside class="col-md-3">
                <div class="sidebar">
                    <div class="block clearfix">
                        <h3 class="title">SDK下载</h3>
                        <div class="separator"></div>
                        <nav>
                            <ul class="nav nav-pills nav-stacked">
                                <li class="active"><a href="index.php">应用下载页</a></li>
                                <li class=""><a href="http://weimifu.net/appcourse2.html" target="_blank">图文教程页</a></li>
                                <li class=""><a href="http://weimifu.net/systemAppcourse2.html" target="_blank">系统图文教程页</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </aside>
            <!-- sidebar end -->

            <!-- main start -->
            <!-- ================ -->
            <div class="main col-md-9">

                <!-- page-title start -->
                <!-- ================ -->
                <h4 class="page-title">App下载页</h4>
                <hr>
                <!-- page-title end -->
                <p>有支付宝和微信专业收款账号下载以下SDK:</p>
                <p>
                    <a href="javascript:open('qpython');" style="color:blue" id="qpython">qpython1.3.0.apk</a>
                    <span style="display:none" id="qpython1">{$website_urls}etc/app/qpython1.3.0.apk</span>
                </p>
                <p>
                    <a href="javascript:open('natapp');" style="color:blue" id="natapp">natapp.py</a>
                    <span style="display:none" id="natapp1">{$website_urls}etc/app/natapp.apk</span>
                </p>
                <p>
                    <a href="javascript:open('VirtualXposed');" style="color:blue" id="VirtualXposed">VirtualXposed_0.12.7.apk</a>
                    <span style="display:none" id="VirtualXposed1">{$website_urls}etc/app/VirtualXposed_0.12.7.apk</span>
                </p>
                <p>
                    <a href="javascript:open('alipay');" style="color:blue" id="alipay">支付宝10.1.22.apk</a>
                    <span style="display:none" id="alipay1">{$website_urls}etc/app/alipay10.1.22.apk</span>
                </p>
                <p>
                    <a href="javascript:open('weixin');" style="color:blue" id="weixin">微信6.6.7.apk</a>
                    <span style="display:none" id="weixin1">{$website_urls}etc/app/weixin6.6.7.apk</span>
                </p>
                <p>
                    <a href="javascript:open('QQ');" style="color:blue" id="QQ">QQ7.6.3.apk</a>
                    <span style="display:none" id="QQ1">{$website_urls}etc/app/qq7.6.3.apk</span>
                </p>
                <p>
                    <a href="javascript:open('GoogleAuth');" style="color:blue" id="GoogleAuth">Google Authenticator_v5.00.apk</a>
                    <span style="display:none" id="GoogleAuth1">{$website_urls}etc/app/GoogleAuthenticator_v5.00.apk</span>
                </p>
                <p>
                    <a href="javascript:open('PayHelper');" style="color:blue;" id="PayHelper">收款精灵-{$pay_helper}.apk</a>
                    <span style="display:none" id="PayHelper1">{$website_urls}etc/app/{$pay_helper}.apk</span>
                </p>


            </div>
            <!-- main end -->

        </div>
    </div>
</section>
<!-- main-container end -->


<div id="dis"
     style="position: fixed;width:440px;height:240px;border:1px solid #999;z-index: 2;background: #EBEBEB;border-radius:2px;display:none;">
    <div style="width:100%;height:32px;border: 1px solid #ccc;border-style:none none solid none;background: #F6F6F6;font-size:0.8em;">
        <img src="1.png" style="margin:7px 5px;display: inline-block;"><span
            style="display: inline-block;">一键安装到手机</span>
        <img src="2.png" style="margin:7px 5px;display: inline-block;float:right;cursor: pointer;" onclick="de()">
    </div>
    <div style="width:85%;height:200px;margin:0 auto;margin-top:20px;color:#000;font-size:1em;">
        <span style="display: block;">是否将该文件直接安装到手机？</span>
        <span style="display: block;font-size:0.8em;margin-top: 20px;" id="p2"></span>
        <span style="display: block;font-size:0.8em;margin-top: 5px;">apk大小：</span>
        <span style="display: block;font-size:0.8em;margin-top: 25px;color:#65D05A;">扫描右边二维码也可以直接安装</span>
        <img id="img1" style="margin:-122px 260px;">
    </div>
    <div style="width:88%;height:50px;margin:0 auto;text-align: center;line-height: 30px;margin-top: -55px;color:#000;font-size:0.8em;">
        <a id="xz"><span
                style="display: inline-block;width:100px;height:30px;border:1px solid #ccc;float:left;cursor: pointer;background: #fff;border-radius:2px;">仅下载到电脑</span></a>
        <span style="display: inline-block;width:100px;height:30px;border:1px solid #ccc;cursor: pointer;background: #30C76C;color:#fff;margin-left: 50px;border-radius:2px;">一键安装</span>
        <span style="display: inline-block;width:100px;height:30px;border:1px solid #ccc;float:right;cursor: pointer;background: #fff;border-radius:2px;"
              onclick="de()">取消</span>
    </div>
</div>
<div id="cs1" style="position: fixed;width:340px;height:240px;border:1px solid #999;z-index: 2;display:none;">余额不足</div>
</body>
</html>

<script>
    $(function () {
        $('#dis').css({"top": $(window).height() / 2 - 240 / 2, "left": $(window).width() / 2 - 440 / 2});
        $('#cs1').css("left", $(window).width() - 350);
    })

    function open(name) {
        $('#dis').css("display", "block");
        var name1 = $('#' + name + "1").html();
        $('#p2').html($('#' + name).html());
        $('#img1').attr("src", "../../api/qrcode.php?data=" + name1);
        $('#xz').attr("href", name1);
    }

    function de() {
        $('#dis').css("display", "none");
    }
</script>
EOF;
