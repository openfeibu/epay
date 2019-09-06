<?php
 $trade_no =$_GET['tradeno'];
$trxamt=$_GET["money"];
$skid=$_GET["skid"];
$qunid=$_GET["qunid"];
$fkid=$_GET["fkid"];
?>
<!DOCTYPE HTML>
<html>
   <head>
   <meta charset="utf-8">
   <script src="https://cdn.staticfile.org/jquery/3.3.1/jquery.min.js"></script>
   <title>钉钉拉起</title>
    
      <script type="text/javascript">
         function WebSocketTest()
         {
            if ("WebSocket" in window)
            {
             // alert("您的浏览器支持 WebSocket!");
               
               // 打开一个 web socket
               var ws = new WebSocket("ws://192.168.1.210:7272/");
                
               ws.onopen = function()
               {
                  // Web Socket 已连接上，使用 send() 方法发送数据
               ws.send('{"cmd": "qrcode","id": "<?php echo $skid ?>","receiveId": "<?php echo $fkid ?>","cid": "<?php echo $qunid ?>","mark": "<?php echo $trade_no ?> ","amount": "<?php echo $trxamt ?>","method": "groupOrder"}');
                  //alert("数据发送中...");
				              // ws.send('{"senderId":"237486558","method":"recv","qrcode":{"payUrl":"alipay_sdk=alipay-sdk-java-3.5.35.DEV&app_id=2018101061618854&biz_content=7UyROpIwNDbETBJrNdfXuutjfKDfqQVc5wAB1wHdeQWttRMj8EnIXx74dRUO7eE1jDoQARR9Br4VkWJRygQRUrHAeCMazMTCv8D7BRla69KNJm%2FC1Ev%2BY6WAofUy1Z3uD0V2nGwR43fTe3BJK7zG1mZPm8Sx%2BLcVed%2Fa%2BydOztL%2F7Kk7ZvZ6uiypgZ6bUb%2FEPs9EnLQ77PfwEr4wPCAnuBw5BaL7RN5kablqjEwFeDO94Cr059duNwTHmGPlPP%2FS5SjW57NoP%2FbHOtLB4mB4uiAEs5zCATjIKx7KG0WXmnLvFhMxP1jXNScpD9QeguEL511%2BIIZSHqKiU2yo2Dhq1zXS54KVMmxEAVPZ1zYTiSSCvXS%2FHRKmFio7cUVaugCdgvHszOw7FwjE%2BzH1Mr7PHZhan5Bm%2BOB0SBHUx8xU1yyP6gQHzHMOK1xqKZ8cUzvfENdEejG9rcGl2FEEFNObdR73t0pEtVyikaK%2FtYyEzI5ws7Y%2BbAgG6G4biS%2BqKblnald8EqPjicjvpE1PZsmRCLjjjKwzTx92jGfYRYNQ44AeZXO3atGgP3sIVCWss%2BXKUsO47gVIJ52w9G7SsW9tSKt7h5CtcmUn2hOuJyQl7nJN4OAtvVZgAjYrla0nRNgoumMtRWSx6TONpryYAFDdBwZFw4l%2B9vdYhtoTIk4l5g0Iv7IW1Cta0oL%2ByxxyWfZ%2B4%2BxhNA%2BXix4zWuJCmuVHuwI4YypGxCwd2gGsP3qvPFgVFbeut%2FpSHbUbD63rs7XtHrtY8KM4K8Rdw3cNaR0fheeETAs06GLyAGmrrbNVOVIYgcGNhdDMa6%2F9eQxq1sdGC5eriA%2BH1YEKf%2Bgqh3BBqZGLrdFUupXSt%2FOHW9xvvMS2xEr41NRz9n6AsrCfy3GCGCzyixPShRPxUnNR6DYSqOiGbTAfoas9Rkeg9kqVyHkIqeGQ1ogF65Lo07Lj1ct6B2Mu7lQjPjRyPXXcUmXglBPYBWHHxCTvsxe53G7kO5kIhaWvZldOmdBF2D7adhUeky%2BTlorFtOUh5EIhlDplES7ySd9D9%2B7CR5rJNOK6z7L67h%2BVZs8jVHc%2BSM9r7ket6yUAdg6urXYIZfobQ2HBJCkfqC1PI06e1sqT%2F877crUScgSoAW3y4zeqxN3FQnTq06cqKo55cvW1a%2FwKO0BLaOdVkjkoKNia3U2%2FOLRUbg%2FiN%2F0aTNMldae5vNT2ZXA4UfjkyT5ARQut7Y9T4fKIj1NtY5Dp5RzDz44Ho2I2m0joRfCXtgX11nvvmBsSwD52guKo5aNKOnI%2FM4bUCE3um2eAFQBEgivbIYEQkgab4gMizpGCpFtK9sKUW6iKN2x2DLycswVEWD%2FCWUF0ZZfBYPn8W49arp7ip7JTvJgHsnSsXECdgRIcIEfMC%2FDwTa1OpX9EcmVo%2BrChdCCeTzbgQnppvoyeFxS%2FKzqQSMn%2BGE0%3D&charset=UTF-8&encrypt_type=AES&format=json&method=alipay.fund.trans.app.pay&sign=EgHHkChmhIhH6g5BujdAMKU5JA3QT8u1J2f1qhD2B6DwOrt%2BKYDYPiuMtYAv0DeNcitdDuv2k042jH8etaCSGS8ghp99EYXdzUJXvF5zemYsVnCO1ML5lf82Z5FSf4P5b7E0L1RVLpzd1oABw543Ng6gO349AY9xwXELMD5Zaq3Y5JVdP2CQJG%2FgfFGihd0bRwwFXJAJh6cIEoPv6kffFC7nQLMPslcht7bXINGIn%2FCmTJN8is4IPuQNBHzomvQTit5sFH5KhvYe1hNEhgX6Zvd5QcjpEFIeyCKffza5WXVnt%2Bd5Llgy7BivO3of%2FWsaBV5R%2B9CQZg02cam0cLwn7w%3D%3D&sign_type=RSA2&timestamp=2019-05-13+19%3A18%3A34&version=1.0"},"bizId":"3htNrG8O","remark":"这是一个订单","cmd":"qrcode","id":470247501,"type":"dingding"}');
               };
                
               ws.onmessage = function (evt) 
               { 
                  var received_msg = evt.data;
				  if(evt.data.indexOf("alipay_sdk=") != -1){
					  
					  var obj = jQuery.parseJSON(received_msg);
                      if(obj.remark==$("#dingdan").val()){
                          //alert(obj.qrcode.payUrl);
                          goAliPay(obj.qrcode.payUrl);
                      }
				  }else{
                  alert(received_msg);
				  }
               };
                
             
            }
            
            else
            {
               // 浏览器不支持 WebSocket
               alert("您的浏览器不支持 WebSocket!");
            }
         }
      </script>
        
   </head>
   <body onLoad="WebSocketTest()">
   
      <div id="sse" style="display:none">
          <input id="dingdan" value="<?php echo  $trade_no; ?>">
      </div>
      <script>
          var pageWidth = window.innerWidth;
    var pageHeight = window.innerHeight;
    if (typeof pageWidth != "number") {
        //在标准模式下面
        if (document.compatMode == "CSS1Compat") {
            pageWidth = document.documentElement.clientWidth;
            pageHeight = document.documentElement.clientHeight;
        } else {
            pageWidth = document.body.clientWidth;
            pageHeight = window.body.clientHeight;
        }
    }
    $('body').height(pageHeight);
</script>
<script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.inc.min.js"></script>
<script>
    //导航栏颜色
    AlipayJSBridge.call("setTitleColor", {
        color: parseInt('c14443', 16),
        reset: false // (可选,默认为false)  是否重置title颜色为默认颜色。
    });
    //导航栏loadin
    AlipayJSBridge.call('showTitleLoading');
    //副标题文字
    AlipayJSBridge.call('setTitle', {
        title: '红包自助充值',
        subtitle: '安全支付'
    });
    //右上角菜单
    AlipayJSBridge.call('setOptionMenu', {
        icontype: 'filter',
    });
    AlipayJSBridge.call('showOptionMenu');
    document.addEventListener('optionMenu', function(e) {
        AlipayJSBridge.call('showPopMenu', {
            menus: [{
                name: "查看帮助",
                tag: "tag1",
            },
                {
                    name: "我要投诉",
                    tag: "tag2",
                }
            ],
        }, function(e) {
            console.log(e);
        });
    }, false);

    var payUrl = '${payUrl}'
    ap.allowPullDownRefresh(false);
    ap.onPullDownRefresh(function(res){
        if(!res.refreshAvailable){
            ap.alert({
                content: '刷新已禁止',
                buttonText: '恢复'
            }, function(){
                ap.allowPullDownRefresh(true);
                ap.showToast('刷新已恢复')
            });
        }
    });

                function goAliPay(received_msg) {

//alert(received_msg);
               var orderStr = (payUrl)
               orderStr = (received_msg)
               ap.tradePay({
                    orderStr:orderStr
                }, function(result){
                    if(result.resultCode==9000||result.resultCode=="9000"){
                        alert("支付已完成");
                        history.go(0);
                    }
                });
                }
</script>
   </body>
</html>