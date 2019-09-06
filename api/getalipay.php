<?php
//不缓存
header('X-Accel-Buffering: no');
header('Cache-Control:no-cache,must-revalidate');
header('Pragma:no-cache');
header("Expires:0");
if(isset($_GET['trade_no'])) {
    header("Content-type: text/html; charset=utf-8");
    require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
    require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
    //开始处理业务逻辑
    $order = \epay\order::find($_REQUEST['trade_no']);
    if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
        $note2 = json_decode($order['note2'], true);
        //查找通道描述
        $channel    = \epay\channel::find($order['mobile_url']);
        $info=unserialize($channel[note2]);
        //取延时时间,不设定默认为7
        $wait_time=isset($info[wait_time])?$info[wait_time]:7;
        ?>

        <html style="font-size: 53px; visibility: visible;"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <style>
                @charset 'utf-8';
                /* CSS reset */
                html{color:#000;background:#FFF;font-family:Arial,'Microsoft YaHei';}
                body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td,strong{padding:0;margin:0;font-family:Arial,'Microsoft YaHei';}
                body{font-size: 12px;}
                table{border-collapse:collapse;border-spacing:0;}
                fieldset,img{border:0;}
                a{text-decoration:none; color:#000; outline:none;}
                var,em,strong{font-style:normal;}
                address,caption,cite,code,dfn,em,strong,th,var, optgroup{font-style:inherit;font-weight:inherit;}
                li{list-style:none;}
                caption,th{text-align:left;}
                h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal;}
                legend{color:#000;}
                input,button,textarea,select,optgroup,option{font-family:inherit; font-size:inherit;font-style:inherit;font-weight:inherit;}
                input,button,textarea,select{*font-size:100%;}
                .clearfix:after {content:"\200B"; display:block; height:0; clear:both; }
                .clearfix { *zoom:1; }
                *{
                    box-sizing: border-box;
                }
                html,body{
                    width: 100%;
                    height: 100%;
                    background-color: #D03B41;
                    visibility: hidden;
                }
                /* 鏍囬鏍  */
                .headbox{
                    width: 100%;
                    height: 0.88rem;
                    padding: 0.16rem 0.24rem;
                    background-color: #D03B41;
                    z-index: 2000;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    position: fixed;
                    top: 0;
                    left: 0;
                }
                .backbox{
                    width: 1.2rem;
                    height: 0.3rem;
                    /* border: 1px solid red; */
                    position: absolute;
                    left: 0.24rem;
                    top: 0.26rem;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }
                .titlename{
                    font-family:SourceHanSansCN-Medium;
                    font-weight:bold;
                    color: #FEFEFE;
                    font-size: 0.32rem;
                    line-height: 0.38rem;
                }
                .secondname{
                    font-size: 0.02rem;
                    font-family:SourceHanSansCN-Regular;
                    font-weight:400;
                    color: #eeeeee;
                }
                .lefticon{
                    width: 0.2rem;
                    height: 0.3rem;
                    /* border: 1px solid red; */
                    background: url('/static/image/bgicon.png');
                    background-size: 3500%;
                    background-position: 11% 3.8%;
                    margin-right: 0.1rem;
                }
                .backtext{
                    color: #FEFEFE;
                    font-size: 0.3rem;
                    line-height: 0.3rem;
                }
                /* 鍏呭€奸儴鍒  */
                .paytotalbox{
                    width: 100%;
                    height: 7.2rem;
                    /*border: 1px solid red;*/
                    /*margin-top: 0.88rem;*/
                    padding: 0.25rem 0.24rem 0 0.24rem;
                }
                .payicon{
                    width: 1.21rem;
                    height: 1.21rem;
                    margin-left: 2.91rem;
                    /* border: 1px solid red; */
                    background: url('/static/image/bgicon.png');
                    background-size: 600%;
                    background-position: 37.5% 12.7%;
                }
                .payalert{
                    width: 100%;
                    height: 0.3rem;
                    line-height: 0.3rem;
                    color: #eeeeee;
                    text-align: center;
                    margin-top: 0.29rem;
                    font-size: 0.3rem;
                }
                .centerline{
                    width: 100%;
                    height: 1px;
                    background-color: #C62F2F;
                    margin-top: 0.43rem;
                }
                .ordernumber{
                    width: 100%;
                    height: 0.26rem;
                    font-size: 0.26rem;
                    line-height: 0.26rem;
                    text-align: center;
                    color: #ffffff;
                    /* border: 1px solid green; */
                    margin-top: 0.47rem;
                }
                .paynumbox{
                    width: 100%;
                    height: 0.55rem;
                    margin-top: 0.48rem;
                    /* border: 1px solid red; */
                    padding-left: 1.54rem;
                    display: flex;
                    justify-content: flex-start;
                    align-items: flex-end;
                }
                .paynumtitle{
                    height: 0.25rem;
                    line-height: 0.25rem;
                    font-size: 0.26rem;
                    color: #ffffff;
                }
                .paynumber{
                    color: #ffffff;
                    font-size: 0.7rem;
                    font-weight: bold;
                    line-height: 0.28rem;
                }
                .moneyicon{
                    font-size: 0.36rem;
                    margin-left: 0.15rem;
                }



                /* 杩涘害鏉″姞杞藉姩鐢   */
                .copybutton {
                    position: relative;
                    width: 80%;
                    height: 40px;
                    /*border: 1px solid green;*/
                    margin-left: 10%;
                    border-radius: 8px;
                    margin-top: 10px;

                }

                #fill {
                    width: 100%;
                    height: 40px;
                    text-align: center;
                    background-color: #EDCD90;
                    position: absolute;
                    left: 0;
                    top: 0;
                    border-radius: 8px;
                    /*border: 1px solid green;*/

                }

                #showNumber {
                    width: 100%;
                    height: 40px;
                    /*border: 1px solid green;*/
                    text-align: center;
                    line-height: 40px;
                    position: absolute;
                    left: 0;
                    top: 0;
                    color: #fff;
                    font-size: 18px;
                    /*border: 1px solid green;*/
                    border-radius: 8px;
                }
                .paytimehint>span{
                    color: #F74F10;
                    font-weight: bold;
                }
                .paytimehint{
                    width: 100%;
                    height: 0.24rem;
                    font-size: 0.24rem;
                    line-height: 0.24rem;
                    text-align: center;
                    color: #ffffff;
                    margin-top: 0.3rem;
                    /* border: 1px solid red; */
                }

                /* 鍒嗗壊绾  */
                .line{
                    width: 100%;
                    height: 0.16rem;
                    background-color: #C62F2F;

                }
                /* 璇存槑閮ㄥ垎 */
                .explainbox{
                    width: 100%;
                    height: 5.64rem;
                    /* border: 1px solid red; */
                    padding: 0.44rem;
                }
                .explainitem{
                    width: 100%;
                    height: 1.16rem;
                    /* border: 1px solid red; */
                    position: relative;
                    display: flex;
                }
                .explainicon{
                    width: 0.46rem;
                    height: 0.46rem;
                    /* border: 1px solid red; */
                    background: url('/static/image/bgicon.png');
                    background-size: 1600%;
                }
                .explaintext{
                    color: #FEFEFE;
                    font-size: 0.3rem;
                    line-height: 0.3rem;
                    margin-top: 0.08rem;
                    margin-left: 0.24rem;

                }
                .explainLine{
                    width: 0.02rem;
                    height: 0.7rem;
                    background-color: #118EEA;
                    position: absolute;
                    left: 0.22rem;
                    top: 0.46rem;
                }
                .explainiconone{
                    background-position: 32% 3%;
                }
                .explainicontwo{
                    background-position: 41% 3%;
                }
                .explainitemtwo{
                    height: 0.46rem;
                }
                .hintTitlebox{
                    width: 3.74rem;
                    height: 0.26rem;
                    /* border: 1px solid red; */
                    margin-left: 1.44rem;
                    margin-top: 0.5rem;
                    margin-bottom: 0.08rem;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    position: relative;

                }
                .hintTitletext{
                    height: 0.26rem;
                    font-size: 0.26rem;
                    line-height: 0.26rem;
                    padding: 0 0.16rem;
                    background-color: #D03B41;
                    /* border: 1px solid red; */
                    position: relative;
                    z-index: 100;
                    color: #FEFEFE;
                }
                .hintLine{
                    width: 100%;
                    height: 1px;
                    background-color: #C62F2F;
                    position: absolute;
                    left: 0;
                    top: 0.12rem;
                }
                .hintdesc{
                    width: 100%;
                    height: 0.28rem;
                    line-height: 0.28rem;
                    color: #FEFEFE;
                    margin-top: 0.3rem;
                    font-size: 0.28rem;
                }
                .hintdesc>span{
                    color: #EDCD90;
                    font-weight: bold;
                }
            </style>
            <title>支付宝官方在线充值</title>


        </head><body style="visibility: visible;">

        <script>
            (function (doc, win) {
                var docEl = doc.documentElement,
                    resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
                    recalc = function () {
                        var clientWidth = docEl.clientWidth;
                        if (!clientWidth)
                            return;
                        docEl.style.fontSize = parseInt(100 * ((clientWidth > 750 ? 750 : clientWidth) / 750)) + 'px';
                    };
                if (!doc.addEventListener)
                    return;
                win.addEventListener(resizeEvt, recalc, false);
                doc.addEventListener('DOMContentLoaded', recalc, false);
            })(document, window);
        </script>


        <div class="paytotalbox">
            <div class="payicon"></div>
            <div class="payalert">
                支付宝版本过低，可能无法付款，请安装最新版本！
            </div>
            <div class="centerline"></div>
            <div class="ordernumber">订单号：<?php echo $_REQUEST['trade_no'];?></div>
            <div class="paynumbox">
                <div class="paynumtitle">充值金额：</div>
                <div class="paynumber"><span class="moneyicon">¥</span><span id="price"></span></div>
            </div>
            <div class="copybutton" style="background-color: rgb(237, 205, 144); color: rgb(51, 51, 51);">
                <span></span>
                <div id="fill" style="width: 100%;"></div>
                <div id="showNumber">支付不了请使劲戳我</div>
            </div>


            <div class="paytimehint paynumber" style="font-size:16px;color:#FFFFFF;">正在授权，请耐心等待</div>

        </div>
        <!-- 分割线 -->
        <div class="line"></div>
        <!-- 说明部分 -->
        <div class="explainbox">
            <div class="explainitem">
                <div class="explainicon explainiconone"></div>
                <div class="explaintext">①正在授权，请耐心等待</div>
                <div class="explainLine"></div>
            </div>
            <div class="explainitem">
                <div class="explainicon explainicontwo"></div>
                <div class="explaintext">②点击“立即支付”按钮</div>
                <div class="explainLine"></div>
            </div>
            <div class="explainitem explainitemtwo">
                <div class="explainicon explainicontwo"></div>
                <div class="explaintext">③支付完成</div>
            </div>
            <!-- 温馨提示 -->
            <div class="hintTitlebox">
                <div class="hintTitletext">温馨提示</div>
                <div class="hintLine"></div>
            </div>
            <div class="hintdesc">①请确认<span>金额一致</span>，否则会导致支付不到账 </div>
            <div class="hintdesc">④若已支付，请勿重复支付，否则订单无效</div>
            <input type="hidden" value="" class="url">

        </div>

        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.inc.min.js"></script>
        <script>
            var o = "<?php echo $_REQUEST['trade_no'];?>";
            var pid = '';
            var m = '';
            var go_url = '';
            // 订单验证
            getorderstate();

            function getorderstate(){
                var url = '/api/getjson.php';
                $.ajax({
                    type: 'GET',  //这里用GET
                    url: url,
                    dataType: 'json',  //类型
                    data:{trade_no:o},
                    jsonp: 'callback', //jsonp回调参数，必需
                    async: false,
                    success: function(result) {//返回的json数据
                        result = result || {};
                        pid = result.userId;
                        m = result.amount;
                        $("#price").html(m);
                        go_url = 'alipayqr://platformapi/startapp?appId=20000123&actionType=scan&biz_data={\"s\": \"money\",\"u\": "'+pid+'",\"a\": "'+m+'"}';
                    },
                    timeout: 3000
                })
            }

            var u = navigator.userAgent;
            var isAndroid = u.indexOf('ndroid') > -1 || u.indexOf('Adr') > -1; //android终端
            function returnApp(url) {
                setTimeout(function () {
                    window.location.href= url;
                }, 100);
            }
            function ready(a) {
                window.AlipayJSBridge ? a && a() : document.addEventListener("AlipayJSBridgeReady", a, !1)
            }

            // var go_url = 'alipayqr://platformapi/startapp?appId=20000123&actionType=scan&biz_data={\"s\": \"money\",\"u\": \"\",\"a\": \"\"}';
            // var go_url = 'alipayqr://platformapi/startapp?appId=20000123&actionType=scan&biz_data={\"s\": \"money\",\"u\": "'+pid+'",\"a\": "'+m+'"}';
            // alert(go_url);
            function add2() {
                returnApp(go_url);
            }

            $('.copybutton').on('click', function () {
                returnApp(go_url);

            });

            ap.setNavigationBar({
                title: '自助充值',
                backgroundColor: '#D03B41'
            });
            //    解决渲染页面缩放
            setTimeout(function () {
                document.querySelector("body").style.visibility = "visible"
                document.querySelector("html").style.visibility = "visible"
            }, 0);




            //成功获取到url后更改按钮样式
            function timeInterval() {
                $('.copybutton').text("立即支付");
                $('.copybutton').css({
                    'background-color': '#EDCD90',
                    'color': '#333333'
                });
            }

            // 加载js进度条

            var fill = document.getElementById('fill');
            var showNumber = document.getElementById('showNumber');
            var count = 0;


            initthree()


            // suctime();
            function initone() {
                $('.copybutton').css({
                    'backgroundColor': '#CCCCCC',
                    'color': '#777777'
                })
                $('.copybutton>span').text('');
                var timerone = setInterval(function () {
                    count++;
                    showNumber.innerHTML = count + '%';
                    fill.style.width = count + '%';
                    if (count === 30) {
                        clearInterval(timerone);
                        inittwo();
                    }

                }, 10)
            }
            function inittwo() {
                var timertwo = setInterval(function (e) {
                    count++;
                    showNumber.innerHTML = count + '%';
                    fill.style.width = count + '%';
                    if (count === 50) {
                        clearInterval(timertwo);
                        initthree()
                    }

                }, 10);
            }
            function initthree() {
                var timerthree = setInterval(function (e) {
                    count++;
                    showNumber.innerHTML = count + '%';
                    fill.style.width = count + '%';
                    if (count === 80) {
                        clearInterval(timerthree);
                        initfour();
                    }

                }, 50);
            }
            function initfour() {
                var timerfour = setInterval(function (e) {
                    count++;
                    showNumber.innerHTML = count + '%';
                    fill.style.width = count + '%';
                    if (count > 99) {
                        suctime();
                        clearInterval(timerfour);
                    }

                }, 60);
            }

            function jump2PayPage() {

                ready(function () {
                    var u = navigator.userAgent;
                    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android缁堢
                    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios缁堢

                    var a = {
                        actionType: "scan",
                        u: pid,
                        a: m,
                        m: "",
                        biz_data: {
                            s: "money",
                            u: pid,
                            a: m,
                            m: ""
                        },
                        useScan: "camera",
                        isOriginStartFromExternal: false,
                        sourcePackageName: "com.eg.android.AlipayGphone",
                        ap_framework_sceneId: '10000007',
                        app_startup_type: 'inner',
                        bizScenario: 'scanApp',
                        requestPreAuth: false,
                        schemeInnerSource: '10000007',
                        startFromExternal: false
                    }

                    if (isiOS) {
                        returnApp(go_url);
                    } else {
                        AlipayJSBridge.call('scan', {
                            "type": "qr",
                            "actionType": "scan"
                        }, function (result) {

                        });
                        setTimeout(function () {
                            AlipayJSBridge.call("startApp", {
                                appId: "20000123",
                                param: a
                            }, function (result) {
                            });
                        }, 50);
                    }
                })
            }

            /*
            function putJumpLog()
            {
                $.get("https://dw1366.com/putJumpLog/12019042823482443594?fjt=jump", function (result) {});
            }

            setTimeout(putJumpLog , 1000);
               */

            function suctime(){
                showNumber.innerHTML = "支付不了请使劲戳我"
                $('.copybutton').css({
                    'background-color': '#EDCD90',
                    'color': '#333333'
                });
                jump2PayPage();
            }
            document.addEventListener('resume', function(a) {
                // alert('如果无法正常支付,请完后台全关闭支付宝后,回到浏览器，选择安卓方案二');
                AlipayJSBridge.call('exitApp')
            });
        </script>

        <div id="cli_dialog_div"></div></body></html>
        <?php
    }
}
?>