
  <!-- footer -->
  <footer id="footer" class="app-footer" role="footer">
    <div class="wrapper b-t bg-light">
      <span class="pull-right">Powered by <a href ui-scroll="app" class="m-l-sm text-muted"><?php echo $conf['web_name']?></a></span>
      &copy; 2016-2018 Copyright.
    </div>
  </footer>
  <!-- / footer -->


</div>

<script src="../libs/jquery/jquery/dist/jquery.js"></script>
<script src="../libs/jquery/bootstrap/dist/js/bootstrap.js"></script>
<script src="js/ui-load.js"></script>
<script src="js/ui-jp.config.js"></script>
<script src="js/ui-jp.js"></script>
<script src="js/ui-nav.js"></script>
<script src="js/ui-toggle.js"></script>
<script src="js/ui-client.js"></script>
<script>
    <?php
    if($balance_alert == true){
        print <<< EOF
    $(function(){
        setInterval('a()',1000*60);
    })
    window.onload = a();
    function a(){
        $.ajax({
            type: "GET",
            url: "../api/balance.php",
            data: "",
            success: function(data){
                if(Number(data) <= '100'){
                    alert('余额小于￥100 请尽快充值！当前余额是：'+data+'元。');
                }
            }
        });
    }
EOF;
    }
    ?>

    $(document).ready(function(){
        $.ajax({                                                                //提现申请记录提示
            type:"GET",
            url:"../api/ajax_api/ajax_apply.php",
            data:"",
            success:function(data){
                data = eval('('+data+')');
                if(data["error"]==1){
                    var left = (document.body.clientWidth-300)/2;
                    var apply_show = '<div id="apply_show" style="display:none;position:  absolute;top: 176px;left:'+left+'px;width: 300px;padding:  10px;background-color: #000;color:  #ffffff;text-align:  center;border-radius:  5px;opacity: 0.8;box-shadow: 3px 10px 15px #000;    font-size: 20px;font-weight: bold;z-index:1000000;">您有<b style="border-radius: 50%;background-color: red;padding: 1px 8px;margin: 0 5;font-weight: 100;font-size: 20px;width: 40px">'+data["ajax_apply"]+'</b>条未处理的提现申请，请处理！</div>';
                    $("#data_all").append(apply_show);
                    $("#apply_show").fadeIn(1000);
                    setTimeout(function(){$("#apply_show").fadeOut(1000)},2000);

                    $("#ajax_apply_num").html(data["ajax_apply"]);
                    $("#ajax_apply_num").css('display',"initial");
                }else{
                    if(data["ajax_apply"]!=0){
                        $("#ajax_apply_num").html(data["ajax_apply"]);
                        $("#ajax_apply_num").css('display',"initial");
                    }
                }
            }
        });
        $("#order_update_pass").keydown(function(event){
            if(event.keyCode==13){
                get_manual();
            }
        })
        $("#sms_key").keydown(function(event){
            if(event.keyCode==13){
                get_manual();
            }
        })
    });
</script>
</body>
</html>
