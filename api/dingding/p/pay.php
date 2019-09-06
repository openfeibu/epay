<?php
header("Content-Type: text/html;charset=utf8");
require_once __DIR__ . DIRECTORY_SEPARATOR . "../../../includes/api/init.php";
//require_once __DIR__ . DIRECTORY_SEPARATOR . "../../../includes/api/debug.php";
if (isset($_REQUEST['tradeno']) && $_REQUEST['tradeno'] != '') {
    $trade_no = daddslashes($_REQUEST['tradeno']);
} else {
    echo "未找到订单。";
    exit();
}




    $qrcode = false;


 
        $type = "2";
     

$pid="99@".daddslashes($_GET["pid"]);
$return_url=daddslashes($_GET["return"]);
	 $sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `type` = 'ddqr' and `status` = '1' order by rand() limit 1";
     $userrow = $DB->query($sql)->fetch();
//if(!isset($userrow["private_key"])||$userrow["private_key"]==''){
// echo "无可用通道";
// return;
//}
	
$skid = urlencode($userrow["private_key"]);
$fkid = urlencode($userrow["public_key"]);
$qunid = urlencode($userrow["body"]);
$tdid=$userrow["id"];
$trxamt=daddslashes($_GET["money"]);
$cxsjfw=date("Y-m-d H:i:s",time()-180);
$money2=0;
 for ($x=1; $x<=999; $x++) {
	     $sqlc = "SELECT * FROM `pay_order` WHERE `money` = '{$trxamt}' and `type` = 'pdd'  and `money2` = '{$money2}' and `status` = '0' and `addtime` >'{$cxsjfw}'";
 $chajine = $DB->query($sqlc)->fetch();
	
	  if($chajine["money"]>0){
		  $money2=$money2+0.01;
		  
	  }else{
	   
       break;
   }
  
}
$trxamt=$trxamt-$money2;
	$sqlg = "UPDATE pay_order SET mobile_url='{$tdid}',money2='{$money2}' WHERE trade_no='{$trade_no}'";
	$DB->query($sqlg)->fetch();
	if($userrow["gatewayUrl"]==''){
		$userrow["gatewayUrl"]=0;
	}
	$cishu=$userrow["gatewayUrl"]+1;
	$sqlg = "UPDATE pay_channel SET gatewayUrl='{$cishu}' WHERE id='{$tdid}'";
	$DB->query($sqlg)->fetch();
	$url="./ddlq.php?tradeno=".$trade_no."&money=".$trxamt."&skid=".$skid."&fkid=".$fkid."&qunid=".$qunid;
	
$paypddma = urlencode($url);
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta http-equiv="Content-Language" content="zh-cn">
    <meta name="apple-mobile-web-app-capable" content="no">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="format-detection" content="telephone=no,email=no">
    <meta name="apple-mobile-web-app-status-bar-style" content="white">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Cache" content="no-cache">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>
        <?php
        if ($type == '1') {
            echo '微信支付';
        } elseif ($type == '2') {
            echo '支付宝支付';
        } elseif ($type == '3') {
            echo "QQ钱包支付";
        }
        else if($type == '4'){
            echo "云闪付";
        }
        ?>
    </title>
    <link href="./css/pay.css" rel="stylesheet" media="screen">
    <!--引入二维码及剪贴外部库-->
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.bootcss.com/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
    <script src="https://cdn.bootcss.com/clipboard.js/2.0.4/clipboard.min.js"></script>
</head>

<body >
<div class="body">
    <h1 class="mod-title">
        <?php
        if ($type == '1') {
            echo '<span class="ico_log ico-3"></span>';
        } elseif ($type == '2') {
            //echo 'ico-1';
            echo '<span class="ico_log ico-1"></span>';
        } elseif ($type == '3') {
            //echo 'ico-2';
            echo '<span class="ico_log ico-2"></span>';
        }
        else if ($type == '4') {
            echo '<span class="">云闪付</span>';
        }
        ?>

    </h1>
    <input type="hidden" id="type" name="type" value="<?php echo $type; ?>"/>
    <input type="hidden" id="payurl" name="payurl" value="<?php echo @$pay_url; ?>"/>
    <input type="hidden" id="trade_no" name="trade_no" value="<?php echo @$trade_no; ?>"/>
    <input type="hidden" id="amount" name="amount" value="<?php echo @$trxamt ?>"/>
    <input type="hidden" id="redirect_url" name="redirect_url" value="<?php echo @$return_url; ?>"/>
    <div class="mod-ct">
        <div class="order">
        </div>
        <div class="amount" id="money" style="font-size: 20px;">
            <?php
          
                echo '<label style="color: red;font-size: 30px;">￥'.$trxamt."</label>";
            
            ?>
        </div>
		<!--支付宝app支付-->
        <div class="paybtn" style="display: none;" id="btnalipay">
            <div class="payalipaybtn" style="display: none;">
                打开支付宝 [扫一扫]
            </div>

            <!--<a href="javascript:void(0);" class="btn btn-primary" target="_self" onclick="javascript:ToUrl('<?php echo $pay_url ?>')">启动支付宝App支付</a>-->
            <?php
            //require_once __DIR__.DIRECTORY_SEPARATOR."../../config/config_base.php";
            $data = unserialize(file_get_contents("../../config/cache/".md5("scan_code")));//取出定义的好的scan_code值
            $scan_code = $data["scan_code"];

            if($scan_code == 'true'){
//                echo '<h1 style="font-size:1.5em;color:red;">1.截图保存二维码到手机</h1>
//                       <h1 style="font-size:1.5em;color:red;">2.打开支付宝，扫一扫本地图片</h1>';
                echo '<h1 style="font-size:1.5em;color:red;">1.截图保存二维码到手机</h1>
                       <h1 style="font-size:1.5em;color:red;">2.打开支付宝App扫码支付</h1>';
            }
            else{
                echo '<a href="'.$pay_url1.'" id="alipaybtn" class="btn btn-primary" target="_blank">启动支付宝App支付</a>';
            }
            ?>
            <!--<div class="btn btn-primary" style=""><p style="color:black">去使用另外一个手机的支付宝来付款</p></div>-->


            <!--<a href="<?php echo @$pay_url; ?>" id="alipaybtn" class="btn btn-primary" target="_blank">启动支付宝App支付</a>-->
            <!--<a href="../api/qrcode.php?data=<?php echo @$pay_url_encode; ?>" target="_blank" download="" id="downloadbtn" class="btn btn-primary">1.先保存(或截图)二维码到手机</a>
            <div style="padding: 10px;"></div>
            <div class="btn btn-primary" style=""><p style="color:red">2.打开支付宝，扫一扫本地图片</p></div>
            <div class="btn btn-primary" style=""><p style="color:black">去使用另外一个手机的支付宝来付款</p></div>-->
            <div id="openalipay" style="display: none"><?php echo $pay_url ?></div>
        </div>
        <div style="color:red;font-size:20px; display: none;">请输入备注：<?php echo $code ?></div>
        <div style="color:red;font-size:20px; display: block;">
            <?php
            //扫码点单选用1
            if (substr($channel['body'],0,22)=="https://qr.alipay.com/") {
               echo '<b>点击直接买单，输入优惠后的金额并付款！</b><br/>';
            }
            else if(strlen($channel['public_key'])==16 || strlen($channel['public_key'])==19){
                echo '';
            }else if(substr($channel['body'],0,22)=="https://qr.alipay.com/" || substr($channel['public_key'],0,22)=="https://qr.alipay.com/"){
				echo "请在转账页面上填写金额：<span style='font-size:1.6em;'>".number_format($money,2)."</span> &nbsp;&nbsp;&nbsp;备注：<span style='font-size:1.6em;'>".$trade_no."</span>
				<button id='cpbtn' class='cpbtn btn btn-primary' style='width:auto;margin-top:-10px;' data-clipboard-text='".$trade_no."';>一键复制备注</button><br/>
				注意：一定要复制备注，然后在转账页面粘贴到备注一栏。否则无法自动上分
				";
            }else if(substr($channel['body'],0,1)=="_" || substr($channel['public_key'],0,1)=="_"){
                echo "步骤提示：1.跳转到支付宝后点击发送留言<br/>2.点击付款链接付款";
            }else if($channel["type"] == "yunshanpay"){
                echo "请不要重复扫码！<br/>";
            }else if(substr($channel['body'],0,1)=="#"){
                echo "请不要重复扫码！<br/>";
            }else{
                echo '请不要重复扫码！<br/>';
            }
            ?>
            <!--<a href="javascript:void(0);" class="btn btn-primary" target="_self" onclick="javascript:ToUrl('<?php echo $pay_url1 ?>')" style="font-size: 1.4em;">启动支付宝App支付</a>-->
        </div>

        <!--QQapp支付-->
        <div class="paybtn" style="display: none;" id="btnqq22">
            <!--<a href="mqqapi://forward/url?url_prefix=<?php echo $pay_url_qr; ?>&souce=oicqzone.com&version=1&src_type=web" id="qqpaybtn2" class="btn btn-primary" target="_blank">启动QQ支付</a>-->
            <a href="<?php echo @$pay_url; ?>" id="qqpaybtn" class="btn btn-primary" target="_blank">启动QQ支付</a>
        </div>
        <div class="qrcode-img-wrapper" data-role="qrPayImgWrapper">
            <div data-role="qrPayImg" class="qrcode-img-area">
                <div class="ui-loading qrcode-loading" data-role="qrPayImgLoading" style="display: none;"></div>
                <div style="position: relative;display: inline-block;">
                   
                   
                    <div id="show_qrcode33" style="padding:20px;"> <img src="../../../api/qrcode.php?data=<?php echo $paypddma; ?>" width="300"
                         height="210" style="display: block; width: 310px; height: 270px;"></div>
                    <img onclick="$(&#39;#use&#39;).hide()" id="use" src="
                 <?php
                    if ($type == '1') {
                        echo 'Images/logo_weixin.png';
                    } elseif ($type == '2') {
                        echo 'Images/logo_alipay.png';
                    } elseif ($type == '3') {
                        echo "Images/logo_qq.png";
                    }
                    else if ($type == "4"){
                        echo "Images/logo_yunshan.jpg";
                    }
                    ?>"
                         style="position: absolute;top: 50%;left: 50%;width:32px;height:32px;margin-left: -16px;margin-top: -30px">
                    <div id="qrcode" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
        <script>
            function ToUrl(x) {
                // window.location.href=x;
                location.href = x;
            }
        </script>

        <!--微信内支付-->
        <div class="payweixinbtn" style="display: none;">
            <a href="http://pay.weimifu.net/api/qrcode.php?data=?data=<?php echo $pay_url_encode; ?>" target="_blank" download=""
               id="downloadbtn" class="btn btn-primary">1.先保存二维码到手机</a>
        </div>
        <div class="payweixinbtn" style="display: none;"><p style="color:red">如果不能保存二维码，请截屏识别图中二维码付款</p></div>
        <div class="payweixinbtn" style="display: none;padding-top: 10px">
            <!-- <a href="weixin://" class="btn btn-primary">2.打开微信，扫一扫本地图片</a>-->
            <a href="javascript:void(0);" class="btn btn-primary" target="_blank"
               onclick="javascript:ToUrl('weixin://')">2.打开微信，扫一扫本地图片</a>
        </div>

        <div class="iospayweixinbtn" style="display: none;">1.长按上面的图片然后"存储图像"</div>
        <div class="iospayweixinbtn" style="display: none;padding-top: 10px"><a href="weixin://scanqrcode" class="btn btn-primary">2.打开微信，扫一扫本地图片</a>
        </div>
        <div class="time-item" style="color:red;font-size:1.5em;">
            <?php
            if(substr($channel['body'],0,22)=="https://qr.alipay.com/" || substr($channel['public_key'],0,22)=="https://qr.alipay.com/"){
                if(substr($channel['body'],0,22)=="https://qr.alipay.com/"){
                    echo "<b>温馨提示：请您填写优惠后的金额并且在三分钟内完成支付，否则将会导致无法上分！</b>";
                }
                else{
                    echo "温馨提示：请您正确填写金额和备注，否则将会导致无法上分！";
                }
            }
            ?>
        </div>
        <div class="time-item" style="color:red;font-size:1.3em;">
			<?php
				if(strlen($channel['public_key'])==16 || strlen($channel['public_key'])==19){
					echo '
					<h1>尊敬的会员,充值时请仔细看完以下注意事项:</h1><br/>
					<h1>1.付款时提示预计两小时到账,可忽略不管, 支付完成正常2~5分钟到账</h1>
					<h1>2.支付过程中,如果遇到支付宝风险提示,请点击继续支付</h1>
					<h1>3.切记，每张二维码只能付款一次，重复使用会导致无法上分！</h1>
					';
				}
			?>
        </div>
        <div class="time-item" style="padding-top: 10px">
            <div class="time-item" id="msg"><h1>付款即时到账，未到账可联系我们。
                    <br>
                    <!--<span style="color: red">请注意：该二维码保存也只能使用一次，请勿重复支付，否则不能自动到账！！！</span>--></h1></div>
            <div class="time-item"><h1><button id="cpbtn" class="cpbtn" data-clipboard-text="<?php echo $trade_no; ?>";>一键复制备注</button></h1></div>
            <!--
            <strong id="hour_show"><s id="h"></s></strong>
            <strong id="minute_show"><s></s></strong>
            <strong id="second_show"><s></s></strong>
            -->
        </div>
        <div class="tip">
            <div class="ico-scan"></div>
            <div class="tip-text">
                <p id="showtext">打开
                    <?php
                    if ($type == '1') {
                        echo '微信';
                    } elseif ($type == '2') {
                        echo '支付宝';
                    } elseif ($type == '3') {
                        echo "QQ钱包";
                    }
                    elseif ($type == '4') {
                        echo "云闪付";
                    }
                    ?> [扫一扫]</p>
            </div>
        </div>
        <div class="tip-text">
        </div>
    </div>
    <div class="foot">
        <div class="inner" style="display:none;">
            <p>手机用户可保存上方二维码到手机中</p>
            <p>在微信扫一扫中选择“相册”即可</p>
            <p></p>
        </div>
    </div>
</div>
<div class="copyRight">
    <p></p>
</div>
<br>
<!--<script src="js/jquery.qrcode.min.js"></script>-->
<script type="text/javascript">
       
    var clipboard = new ClipboardJS('.cpbtn');
    clipboard.on('success', function(e) {
        alert("复制成功！");
    });

    var myTimer;

    function yun_url(){
        $("#show_qrcode").html("");
        $("#show_qrcode").append("<img src='Images/loading.gif' style='width: 300px;'>");
        $.ajax({
            url:"../yun/epay_data_get.php",
            type:"get",
            dataType:"json",
            data:{
                trade_no:'<?php echo $trade_no; ?>',
                channel:'<?php echo $mobile_url; ?>',
                money:'<?php echo $money; ?>',
                type:'<?php echo $type; ?>',
                token:'<?php echo md5(md5($trade_no."@".$mobile_url."@".$money."@")."GzUsMXjYaFdOmzBjHu1bGFjeIEYzAhoW"); ?>'
            },
            success:function(data){
                if(data.success=="true"){
                    if(data.msg=="ddpay"){
                        $("#show_qrcode").html("");
                        $('#qrcode').qrcode('<?php echo $pay_url; ?>').hide(); //必须将相应的元素隐藏
                        var img = convertCanvasToImage($('#qrcode').find("canvas").get(0));
                        $("#show_qrcode").append(img);
                    }
                    else{
                        $("#show_qrcode").html("");
                        $('#qrcode').qrcode(data.remarks).hide(); //必须将相应的元素隐藏
                        var img = convertCanvasToImage($('#qrcode').find("canvas").get(0));
                        $("#show_qrcode").append(img);
                    }
                }
                else {
                    setTimeout("yun_url()",1000);
                }
            }
        });
    }

    function timer(intDiff) {
        myTimer = window.setInterval(function () {
            // var day = 0,
            // hour = 0,
            // minute = 0,
            // second = 0;//时间默认值
            // if (intDiff > 0) {
            // day = Math.floor(intDiff / (60 * 60 * 24));
            // hour = Math.floor(intDiff / (60 * 60)) - (day * 24);
            // minute = Math.floor(intDiff / 60) - (day * 24 * 60) - (hour * 60);
            // second = Math.floor(intDiff) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
            // }
            // if (minute <= 9) minute = '0' + minute;
            // if (second <= 9) second = '0' + second;
            // $('#hour_show').html('<s id="h"></s>' + hour + '时');
            // $('#minute_show').html('<s></s>' + minute + '分');
            // $('#second_show').html('<s></s>' + second + '秒');
            // if (hour <= 0 && minute <= 0 && second <= 0) {
            // qrcode_timeout();
            // clearInterval(myTimer);
            // }
            // intDiff--;
            checkdata();
        }, 3000);
    }


    function getRootPath() {
        //获取当前网址，如： http://localhost:8083/uimcardprj/share/meun.jsp
        var curWwwPath = window.document.location.href;
        //获取主机地址之后的目录，如： uimcardprj/share/meun.jsp
        var pathName = window.document.location.pathname;
        var pos = curWwwPath.indexOf(pathName);
        //获取主机地址，如： http://localhost:8083
        var localhostPaht = curWwwPath.substring(0, pos);
        //获取带"/"的项目名，如：/uimcardprj
        var projectName = pathName.substring(0, pathName.substr(1).indexOf('/') + 1);
        return localhostPaht;
        // var curPageUrl = window.document.location.href;
        // var rootPath = curPageUrl.split("//")[0] + curPageUrl.split("//")[1].split("/")[0]
        // 	  + curPageUrl.split("//")[1].split("/")[1];
        // return rootPath;
    }

    function checkdata() {
        var no = $("#trade_no").val();
        $.post(
            "http://pay.weimifu.net/api/getresult.php",
            {
                userid: <?php echo daddslashes($_GET["pid"]);?>,
                trade_no: no,
            },
            function (data) {
                if (data.msg == '支付成功') {
                    // alert(data.returnurl);
                    // $("qrcode").hidden;
                    data.returnurl = "<?php echo $return_url ?>";
                    window.clearInterval(timer);
                    //$("#show_qrcode").attr("src", "http://imgcdn2.xinlis.com/static/index/Images/pay_ok.png");
                    $('#show_qrcode33').html("<img src='http://imgcdn2.xinlis.com/static/index/Images/pay_ok.png' width='240' height='210'/>");
                    $("#use").remove();
                    $("#money").text("支付成功");
                    $("#msg").html("<h1>即将返回商家页</h1>");
                    if (isMobile() == 1) {
                        $(".paybtn").html('<a href="" class="btn btn-primary">返回商家页</a>');
                        setTimeout(function () {
                            window.location = data.returnurl;
                        }, 5000);
                    } else {
                        $("#msg").html("<h1>即将跳转回商家页</h1>");
                        setTimeout(function () {
                            window.location = data.returnurl;
                        }, 5000);
                    }

                }
            }
        );
    }

    function qrcode_timeout() {
        //$('#show_qrcode').attr("src", "http://imgcdn2.xinlis.com/static/index/Images/qrcode_timeout.png");
        $('#show_qrcode').html("<img src='http://imgcdn2.xinlis.com/static/index/Images/qrcode_timeout.png' width='240' height='210'/>");
        $("#use").hide();
        $('#msg').html("<h1>请刷新本页</h1>");

    }

    function isWeixin() {
        var ua = window.navigator.userAgent.toLowerCase();
        if (ua.match(/MicroMessenger/i) == 'micromessenger') {
            return 1;
        } else {
            return 0;
        }
    }

    function isQQ() {
        var ua = navigator.userAgent.toLowerCase();
        if (ua.indexOf('mqqbrowser') > -1 && ua.indexOf('nettype') > -1 && ua.indexOf('mttcustomua') == -1) {
            return 1;
        } else if (ua.indexOf('iphone') > -1 || ua.indexOf('mac') > -1) {
            if (ua.indexOf('qq') > -1 && ua.indexOf('mttcustomua') == -1) {
                return 2;
            } else {
                return 3;
            }
        } else {
            return 3;
        }
    }

    function isMobile() {
        var ua = navigator.userAgent.toLowerCase();
        _long_matches = 'googlebot-mobile|android|avantgo|blackberry|blazer|elaine|hiptop|ip(hone|od)|kindle|midp|mmp|mobile|o2|opera mini|palm( os)?|pda|plucker|pocket|psp|smartphone|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce; (iemobile|ppc)|xiino|maemo|fennec';
        _long_matches = new RegExp(_long_matches);
        _short_matches = '1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-';
        _short_matches = new RegExp(_short_matches);
        if (_long_matches.test(ua)) {
            return 1;
        }
        user_agent = ua.substring(0, 4);
        if (_short_matches.test(user_agent)) {
            return 1;
        }
        return 0;
    }


    $().ready(function () {
        //默认6分钟过期
        //云闪付
        <?php
        if($channel["type"] == "yunshanpay" || $channel["type"] == "ddpay"){
            echo 'yun_url();';
        }
        ?>
        timer("300");

        var istype = $("#type").val();
        var suremoney = "1";
        var uaa = navigator.userAgent;
        var isiOS = !!uaa.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        if (isMobile() == 1) {
            if (isWeixin() == 1 && istype == 1) {
                //微信内置浏览器+微信支付
                $("#showtext").text("长按二维码识别");
            } else {
                //其他手机浏览器+支付宝支付
                if (isWeixin() == 0 && istype == 2) {
                    var payurl = $("#payurl").val();
                    $("#btnalipay").attr('style', 'padding-top:15px;height:70px;');
                    var goPay = '<span id="goPay"> <span>';
                    //给A标签中的文字添加一个能被jQuery捕获的元素
                    $('#alipaybtn').append(goPay);
                    //模拟点击A标签中的文字
                    $('#goPay').click();
                    $('#msg').html("<h1>支付完成后，请返回此页</h1>");
                    //隐藏二维码
                    // $(".qrcode-img-wrapper").remove();
                    $(".tip").remove();
                    $(".foot").remove();
                    //手机页面显示“扫一扫”
                    // $(".payalipaybtn").attr('style','padding-top:15px;');

                    //自动跳转到支付宝
                    // window.onload = function () {
                    //     // document.getElementById("openalipay").click();
                    //     location.href = document.getElementById("openalipay").innerHTML;
                    // }

                } else if (isQQ() == 3 && istype == 3) {
                    var payurl = $("#payurl").val();
                    $("#btnqq").attr('style', '');
                    var goPay = '<span id="goPay"> <span>';
                    //给A标签中的文字添加一个能被jQuery捕获的元素
                    $('#qqpaybtn').append(goPay);
                    //模拟点击A标签中的文字
                    $('#goPay').click();
                    $('#msg').html("<h2 style='color:red'>点击启动QQ支付后会调用手机QQ打开一个二维码页面<br>长按二维码选择扫描二维码，然后支付即可</h2><br><br><h1>支付完成后，请返回此页</h1>");
                    $(".qrcode-img-wrapper").remove();
                    $(".tip").remove();
                    $(".foot").remove();
                } else if ((isQQ() == 1 || isQQ() == 2) && istype == 3) {
                    //QQ内置浏览器+QQ支付
                    $("#showtext").text("长按二维码识别");
                } else {
                    if (isWeixin() == 0 && istype == 1) {
                        //其他手机浏览器+微信支付
                        //IOS的排除掉
                        if (isiOS) {
                            $('.iospayweixinbtn').attr('style', 'padding-top: 15px;');
                        } else {
                            $(".payweixinbtn").attr('style', 'padding-top: 15px;');
                        }
                        $("#showtext").html("请保存二维码到手机<br>微信扫一扫点右上角-从相册选取");
                    }
                }
            }
        }

        if (isiOS) {
            $('#show_qrcode').css({width: 310, height: 310});
        }
    });

    function convertCanvasToImage(canvas){
        //新Image对象,可以理解为DOM;
        var image = new Image();
        //canvas.toDataURL返回的是一串Base64编码的URL,当然,浏览器自己肯定支持，指定格式PNG
        image.src = canvas.toDataURL("image/png");
        return image;
    }
    <?php
    if($channel["type"] != "yunshanpay"){
        echo '$(\'#qrcode\').qrcode("'.$pay_url.'").hide(); //必须将相应的元素隐藏
    var img = convertCanvasToImage($(\'#qrcode\').find("canvas").get(0));
    $("#show_qrcode").append(img);';
    }
    ?>

</script>

<!--<span id="time">10秒钟后自动关闭</span><br>-->
<!--<a href="javascript:clearTimeout(timer)">留在本页</a>-->
<!--<script>-->
<!--    //定义函数myClose关闭当前窗口-->
<!--    function myClose(){-->
<!--        //将id为time的元素的内容转为整数，保存在变量n中-->
<!--        var n=parseInt(time.innerHTML);-->
<!--        n--;//将n-1-->
<!--        //如果n==0,关闭页面-->
<!--        //否则, 将n+秒钟后自动关闭，再保存回time的内容中-->
<!--        if(n>0){-->
<!--            time.innerHTML=n+"秒钟后自动关闭";-->
<!--            timer=setTimeout(myClose,1000);-->
<!--        }else{-->
<!--            close();-->
<!--        }-->
<!--    }-->
<!--    var timer=null;-->
<!--    //当页面加载后，启动周期性定时器，每个1秒执行myClose-->
<!--    window.onload=function(){-->
<!--        timer=setTimeout(myClose,1000);-->
<!--    }-->
<!--</script>-->
</body>
</html>
