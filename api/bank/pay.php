
<?php
$trxamt=addslashes($_GET["money"]);
$order=addslashes($_GET["tradeno"]);
$pid=addslashes($_GET["pid"]);

?>
<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0"><title>在线支付</title><link href="./files/font-awesome.min.css" rel="stylesheet"><link href="./files/app.bb351c5485be2a10dd4928fb59f1a431.css" rel="stylesheet"><style>body{
            z-index:-150;
            background-color:#f2f4fc;
        }
        #Loading {
            z-index:-100;
            top:50%;
            left:50%;
            position: absolute;
            -webkit-transform: translateY(-50%)  translateX(-50%);
            transform: translateY(-50%)  translateX(-50%);
            z-index:100;
        }
        @-webkit-keyframes ball-beat {
            50% {
                opacity: 0.2;
                -webkit-transform: scale(0.75);
                transform: scale(0.75); }

            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                transform: scale(1); } }

        @keyframes ball-beat {
            50% {
                opacity: 0.2;
                -webkit-transform: scale(0.75);
                transform: scale(0.75); }

            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                transform: scale(1); } }

        .ball-beat > div {
            background-color: rgba(71,125,167,1);
            width: 15px;
            height: 15px;
            border-radius: 100% !important;
            margin: 2px;
            -webkit-animation-fill-mode: both;
            animation-fill-mode: both;
            display: inline-block;
            -webkit-animation: ball-beat 0.7s 0s infinite linear;
            animation: ball-beat 0.7s 0s infinite linear; }
        .ball-beat > div:nth-child(2n-1) {
            -webkit-animation-delay: 0.35s !important;
            animation-delay: 0.35s !important; }</style><style type="text/css"></style>
  <style type="text/css">
        .Login[data-v-7331fcf3]{
            position: relative;
        }
        .head_bg[data-v-7331fcf3]{
            width:100%;
            height: 22%;
            background: rgba(23,172,255,1);
            z-index: -50;
            position: fixed;
            top: 0;
        }
        .head_bgs[data-v-7331fcf3]{
            width: 100%;
            position: fixed;
            top: 15%;
            bottom: 0px;
            left: 0px;
            right: 0px;
        }
        .head_val[data-v-7331fcf3]{
            width: 40vw;
            height: 3rem;
            line-height: 3rem;
            border:none;
            background: none;
            color: #fff;
            border-top:none;
            border-bottom: 0.4vw solid #fff;
            font-size: 1.6rem;
            position: absolute;
            top: 5%;
            margin-left: 30vw;
        }
        .head_val[data-v-7331fcf3]::-webkit-input-placeholder{
            color:#fff;
            font-size: 1rem;
        }
        .head_val[data-v-7331fcf3]::-moz-placeholder{   /* Mozilla Firefox 19+ */
            color:#fff;
            font-size: 1rem;
        }
        .head_val[data-v-7331fcf3]:-moz-placeholder{    /* Mozilla Firefox 4 to 18 */
            color:#fff;
            font-size: 1rem;
        }
        .head_val[data-v-7331fcf3]:-ms-input-placeholder{  /* Internet Explorer 10-11 */
            color:#fff;
            font-size: 1rem;
        }
        .content[data-v-7331fcf3]{
            position: fixed;
            top: 24%;
            width: 100%;
            height: 65%;
            overflow: scroll;
            padding-top: 0.3rem;
        }
        .content[data-v-7331fcf3]::-webkit-scrollbar{display: none
        }
        .content_li[data-v-7331fcf3]{
            width: 96%;
            margin: 0 auto;
            height: 4rem;
            border-radius: 1.33333vw;
            box-shadow: 0.4vw 0.4vw 8vw rgba(185,230,255,1);
            margin-bottom: 1rem;
            position: relative;
        }
        .content_li[data-v-7331fcf3]:after{
            content:"";
            position: absolute;
            display: block;
            width: 2.8rem;
            height: 2.8rem;
            right: 0;
            top: 0;
            background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFQAAABcCAYAAADwOJKsAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTMyIDc5LjE1OTI4NCwgMjAxNi8wNC8xOS0xMzoxMzo0MCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6Mzc2QjUxQzA1MDNDMTFFOUE3MDhDMjAwQjc3MTA3NjciIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6Mzc2QjUxQzE1MDNDMTFFOUE3MDhDMjAwQjc3MTA3NjciPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDozNzZCNTFCRTUwM0MxMUU5QTcwOEMyMDBCNzcxMDc2NyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDozNzZCNTFCRjUwM0MxMUU5QTcwOEMyMDBCNzcxMDc2NyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pm3CAMUAAAmmSURBVHja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) no-repeat;
            background-size: 100% 100%;
        }
        .content_li_img[data-v-7331fcf3]{
            width: 3rem;
            height: 3rem;
            float: left;
            margin-left: 1rem;
            margin-top: 0.5rem;
        }
        .content_li_title[data-v-7331fcf3]{
            float: left;
            margin-left: 1rem;
            margin-top: 0.8rem;
        }
        .content_li_bank[data-v-7331fcf3]{
            font-size: 1rem;
            color: #333;
            display: block;
        }
        .content_li_enbank[data-v-7331fcf3]{
            color: #939393;
            font-size: 0.8rem;
        }
        .bottom[data-v-7331fcf3]{
            position: fixed;
            bottom: 0;
            z-index: 500;
            left: 0;
            right: 0;
            height: 13.33333vw;
            /*line-height: 8%;*/
        }
        .bottom-cost[data-v-7331fcf3]{
            color: rgba(24,171,255,1);
            font-size: 1.8rem;
            margin-left: 0.6rem;
            display: inline-block;
            margin-top: 2%;
        }
        .Confirm_button[data-v-7331fcf3]{
            float: right;
            margin-right: 0.6rem;
            width: 6rem;
            height: 80%;
            line-height: 80%;
            margin-top: 0.3rem;
            border:none;
            color: #fff;
            background: rgba(24,171,255,1);
            box-shadow: 0.4vw 0.4vw 8vw rgba(185,230,255,1);
            font-size: 3.2vw;
            border-radius: 1.33333vw;
            /*background: url("../../images/Confirm_button.png") no-repeat;*/
            /*background-size:100% 100%;*/
        }
        .content_lis[data-v-7331fcf3]{
            width: 96%;
            margin: 0 auto;
            height: 4rem;
            border-radius: 1.33333vw;
            box-shadow: 0.4vw 0.4vw 8vw #e1e1e1;
            margin-bottom: 1rem;
        }
        .content_li_titles[data-v-7331fcf3]{
            float: left;
            margin-left: 1rem;
            margin-top: 1.2rem;
            font-size: 1rem;
        }
        .pc_head[data-v-7331fcf3]{
            width: 100%;
            height: 1.06667vw;
            background: rgba(58,58,58,1);
            position: fixed;
            top: 0;
        }
        .pc_heads[data-v-7331fcf3]{
            width: 100%;
            height: 1.33333vw;
            position: fixed;
            top: 1.06667vw;
            background:white;
        }
        .pc_head_content[data-v-7331fcf3]{
            position: fixed;
            top: 2.4vw;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
        }
        .pc_content[data-v-7331fcf3]{
            width: 60%;
            margin:0 auto;
            margin-top: 1.33333vw;
            /*height: 8rem;*/
        }
        .pc_bg[data-v-7331fcf3]{
            width: 100%;
            /*height: 80px;*/
            margin-bottom: 0.53333vw;
        }
        .pc_bank[data-v-7331fcf3]{
            border: 1px solid #ccc;
            width: 100%;
            height: 28.66667vw;
            background: #fff;
        }
        .pc_ul[data-v-7331fcf3]{
            width: 100%;
            height: 17.33333vw;
            overflow: scroll;
            padding-top:1.33333vw;
        }
        .pc_ul[data-v-7331fcf3]::-webkit-scrollbar{display: none
        }
        .pc_ul li[data-v-7331fcf3]{
            width: 180px;
            height: 2.93333vw;
            line-height: 2.93333vw;
            float: left;
            margin-left: 3%;
            margin-bottom: 1.33333vw;
            border:0px solid rgba(151,151,151,0.6);
            border-radius: 0.26667vw;
        }
		.pc_ul li:hover{cursor: pointer}
        .pc_ul .pc_content_li[data-v-7331fcf3]{
            border:0px solid rgba(20,180,255,0.5);
            position: relative;
        }
        .pc_content_li[data-v-7331fcf3]:after{
            content:"";
            position: absolute;
            display: block;
            width: 1.86667vw;
            height: 1.86667vw;
            right: 0;
            top: 0;
            background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACUAAAAoCAYAAAB5ADPdAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTMyIDc5LjE1OTI4NCwgMjAxNi8wNC8xOS0xMzoxMzo0MCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MzQ5MEI1NUQ1MDM5MTFFOUJFODlCQTVENDhFOEI2QjgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MzQ5MEI1NUU1MDM5MTFFOUJFODlCQTVENDhFOEI2QjgiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDozNDkwQjU1QjUwMzkxMUU5QkU4OUJBNUQ0OEU4QjZCOCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDozNDkwQjU1QzUwMzkxMUU5QkU4OUJBNUQ0OEU4QjZCOCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PrwaA/AAAAOjSURBVHjazJhbSFVBFIZ/TxZlRGVmZKVhUuQlDTEsqKggLxVlGKRGPSRkDwllUJBFgZEPSeWLFr0kXaDEIqxM6Gp4wSzCSrqQdlPKRDRTyML+5czO4+l4O+5wL/g5e4bZ+3x7rTVrZo+bV2FXJoB9GDnroj5Ql6kMqtWNUG68OEMlY+TtGbXcpklTqKsWgAqlDtp04ze1mbprAbBNNrvGT2oDVeHq06InAvt9hw01y+bQ8Z1aR71w5WmTRwMxM4cN5WZz0tkoL019HOxTriwEfAnU3Am8a1V9CV6uU9n66P9ErdSAA9obghQsBopagIYOoIZ3pgUCER5KZkGJvdUea+nvAeKh1TOA/DqVU2LZNcB4d+BIEHA8zFwosSdUHNXuCCLhEYmHBCiT5S+MUEkBQC1H1zI7I7yB3NfmQ4ndkxTRZaPbUjjDUuYChxYAxZ+Biw09g9uZV6cj1XVqhRoz1PySij7YsVuoPLnH6MieB0yg19b6qfbLZuAY520qgYM8gR3lQBMhK9vN95Rh56k99h1lTUAI/zy8GJh6g+tDKbDdH/jxSwFlhCogCbfkm+hmxMB/5D7EcJ+kJBgHpHHpG+BXp/JqYxmw118leGylGlz0UP0GcgaeCFfXu6vM9ZRh6VSO0ZAEz9dgbfSQ9zjg8TJVCsRDjibhlLD3V/ndXaxvuygpAIkGmFj8bAX4vl2FTeC+dvybX3GE3zm/56UcbZRH4mFX90CFlGRIgHQ8YjXz1HMmV89Gacf6qhk5ia+/giWijoU2mXfkvuLMmQMEjwVuNbk++5yZ1Os7VKR9p4SnvkN5QzwklV7Kgh9HbyOQF0N8v57h7lQzN6u6t8eGCwUdxhIqxH4tXOStgLpLBUOWFayuq+gVH0ItmQaUfuGSRM/lNJjrKcN8qAdGKO1NElpyTWbn7aV0LZM/nq+QR99uLXdew0yBalzTXacESIrAdGdgEsrGDrX8GB7rK9FtMMkIJgt4lLMFPHyKSnaxtOcKSGCcAZkKpcGq9c6iV1Aa2Ioq6alnfcGYHj57Yyhj+HONGuM4VorqQGuhWYnuzJKoc1IL/8fWxVW7oCs/rAQFvUamWw1K7Ch1ympQ3TsWHU5LQcls2kZdtxKUcTSQoPf8loGCLqrydfTUSlDQy1CU/q60DJRxNLBKf4lbBgr69C7a2dHASEJBn+7IKU+blaCgz8PW6/Mxy0BBnyD+PRqwCpRYAdTZa5c7rGVn5Qv8jwADAAUMDUSzUEUbAAAAAElFTkSuQmCC) no-repeat;
            background-size: 100% 100%;
        }
        .pc_img[data-v-7331fcf3]{
            float: left;
            width: 2.13333vw;
            height: 2.13333vw;
            margin-top: 0.26667vw;
            margin-left: 0.15rem;
            margin-right: 0.8vw;
        }
        .pc_title[data-v-7331fcf3]{
            color: rgba(0,0,0,1);
            font-size: 0.8vw;
            font-weight: 550;
        }
        .pc_cost_title[data-v-7331fcf3]{
            margin-left: 3%;
            color: rgba(51,51,51,0.7);
            font-size: 1.06667vw;
            font-weight: 550;
        }
        .pc_bottom[data-v-7331fcf3]{
            width: 100%;
            padding-left: 3%;
            line-height: 4vw;
            height: 4vw;
            overflow: hidden;
        }
        .pc_cost_unit[data-v-7331fcf3]{
            font-size: 1.33333vw;
            color: rgba(20,180,255,1);
        }
        .pc_cost_inp[data-v-7331fcf3]{
            display: inline-block;
            width: 8vw;
            border:none;
            padding-left:0.15rem;
            height: 2.66667vw;
            font-size: 1.6vw;
            color: rgba(20,180,255,1);
            font-weight: 550;
            border-bottom: 1px solid rgba(20,180,255,1);
        }
        .pc_btn:hover{cursor: pointer}
        .pc_btn[data-v-7331fcf3]{
            float: left;
            margin-left: 43%;
            width: 7.66667vw;
            height: 2.53333vw;
            line-height: 2.13333vw;
            border:none;
            background: #f55601;
            border-radius: 0.26667vw;
            font-size: 0.8vw;
            color: #fff;
            margin-top: 0.8vw;
        }</style><style type="text/css">
    </style>
    <script>

        function tijiao() {
            var type=document.getElementById("yinxing").value;

            location.href="curl.php?pid=<?php echo $pid; ?>&money=<?php echo $trxamt; ?>&mark=<?php echo $order; ?>&bankCode="+type;
        }
    </script>
</head><body><div data-v-7e53fbe8="" id="app">
    <div data-v-7331fcf3="" data-v-7e53fbe8=""><div data-v-7331fcf3=""><!----></div>
        <div data-v-7331fcf3=""><div data-v-7331fcf3="" class="Logins">
                <p data-v-7331fcf3="" class="pc_head"></p>
                <div data-v-7331fcf3="" class="pc_head_content"><div data-v-7331fcf3="" class="pc_content">
                        <div style="padding:0px 15px;font-size: 15px">支付步骤：选择银行 》 安装银行控件 》 输入卡号短信验证码 》支付成功</div><br>
                        <div style="background-color: #ededed;width:100%;height:35px;padding:10px;border: 1px solid #ccc; " >&nbsp;&nbsp;订单编号：<?php echo $order; ?></div>
                        <div data-v-7331fcf3="" class="pc_bank" style="height:80px;"><div style="padding:30px">订单金额：<span style="color:#f55601">￥ <?php echo $trxamt; ?></span></span></div></div><br><br>
                        <div style="background-color: #ededed;width:100%;height:35px;padding:10px;border: 1px solid #ccc;" >&nbsp;&nbsp;&nbsp;线上支付</div>
                        <div data-v-7331fcf3="" class="pc_bank">
                            <div style="padding:25px">个人网银</div>
                            <ul data-v-7331fcf3="" class="pc_ul">
                               <li data-v-7331fcf3="" onclick="yinhang1()" onmouseover="go(1)" onmouseout="out(1)" id="tu1"  class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode1" value="icbc"> <img  src="img/icbc.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang2()"  onmouseover="go(2)" onmouseout="out(2)"  id="tu2" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode2" value="cmb"><img src="img/cmb.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang3()"  onmouseover="go(3)" onmouseout="out(3)"  id="tu3" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode3" value="pingan"><img src="img/pingan.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang4()"  onmouseover="go(4)" onmouseout="out(4)"  id="tu4" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode4" value="citic"><img  src="img/citic.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang5()"  onmouseover="go(5)" onmouseout="out(5)"  id="tu5" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode5" value="ccb"><img  src="img/ccb.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang6()"  onmouseover="go(6)" onmouseout="out(6)"  id="tu6" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode6" value="psbc"><img  src="img/psbc.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang7()"  onmouseover="go(7)" onmouseout="out(7)"  id="tu7" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode7" value="comm"><img  src="img/comm.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang8()"  onmouseover="go(8)" onmouseout="out(8)"  id="tu8" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode8" value="boc"><img  src="img/boc.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang9()"  onmouseover="go(9)" onmouseout="out(9)"  id="tu9" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode9" value="ceb"><img  src="img/ceb.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang10()"  onmouseover="go(10)" onmouseout="out(10)"  id="tu10" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode10" value="spdb"><img  src="img/spdb.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang11()"  onmouseover="go(11)" onmouseout="out(11)"  id="tu11" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode11" value="cib"><img  src="img/cib.gif"></li>
                                <li data-v-7331fcf3="" onclick="yinhang12()"  onmouseover="go(12)" onmouseout="out(12)"  id="tu12" class="pc_content_lis"><input type="radio" style="float:left;margin-top: 15px;margin-left: 5px" name="bankcode" id="bankcode12" value="cgb"><img  src="img/cgb.gif"></li>
                            </ul>
                            <button data-v-7331fcf3="" onclick="tijiao()" class="pc_btn">去支付</button></div></div></div></div></div>
    </div></div></div>
<input type="hidden" id="yinxing" value="">
<div class="sogoutip" style="z-index: 2147483645; visibility: hidden; display: none;"></div><div class="sogoubottom" id="sougou_bottom" style="display: none;">

</div>
<script type="text/javascript" src="files/jquery-1.7.2.js"></script>
<script>
	function go(id){
		$("#tu" + id).css("border","1px solid #ccc");
	}
	
	function out(id){
		$("#tu" + id).css("border","0px solid #ccc");
	}
	

	
</script>
<script>

    function yinhang1() {
        document.getElementById("yinxing").value="ICBCicbc105_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        //document.getElementById("tu1").innerHTML='<img width=100% src="img/icbc.jpg">';

        $('#bankcode1').attr('checked',true);
    }
    function yinhang2() {
        document.getElementById("yinxing").value="CMBcmb103_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode2').attr('checked',true);
    }
    function yinhang3() {
        document.getElementById("yinxing").value="SPABANKspabanknucc103_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode3').attr('checked',true);
    }
    function yinhang4() {
        document.getElementById("yinxing").value="CITICciticnucc103_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode4').attr('checked',true);
    }
    function yinhang5() {
        document.getElementById("yinxing").value="CCBccb103_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";

        $('#bankcode5').attr('checked',true);
    }
    function yinhang6() {
        document.getElementById("yinxing").value="PSBCpsbcnucc103_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode6').attr('checked',true);
    }
    function yinhang7() {
        document.getElementById("yinxing").value="COMMcommnucc103_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode7').attr('checked',true);
    }
    function yinhang8() {
        document.getElementById("yinxing").value="BOCboc102_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode8').attr('checked',true);
    }
    function yinhang9() {
        document.getElementById("yinxing").value="CEBcebnucc103_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode9').attr('checked',true);
    }
    function yinhang10() {
        document.getElementById("yinxing").value="SPDBspdbnucc103_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode10').attr('checked',true);
    }
    function yinhang11() {
        document.getElementById("yinxing").value="CIBcib102_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode11').attr('checked',true);
    }
    function yinhang12() {
        document.getElementById("yinxing").value="GDBgdbnucc103_DEPOSIT_DEBIT_EBANK_XBOX_MODEL";
        $('#bankcode12').attr('checked',true);
    }
    //function go() {
        //document.getElementById("tu"+i).style.border-color= "#0000ff";
    //}
   
</script>


</body>
</html>
