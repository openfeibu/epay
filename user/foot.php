  <!-- /content -->

<?php
if ($isorderpage!=1){
?>
  <!-- footer -->
  <footer id="footer" class="app-footer" role="footer">
    <div class="wrapper b-t bg-light">
      <span class="pull-right">Powered by <a href="/" target="_blank"><?php echo $conf['web_name']?></a></span>
    	&copy; 2016-2018 Copyright.
    </div>
  </footer>
  <!-- / footer -->
<?php
}
?>

</div>

<!--<script src="http://template.down.swap.wang/ui/angulr_2.0.1/bower_components/jquery/dist/jquery.min.js"></script>-->
<!--<script src="http://template.down.swap.wang/ui/angulr_2.0.1/bower_components/bootstrap/dist/js/bootstrap.js"></script>-->
<!--<script src="http://template.down.swap.wang/ui/angulr_2.0.1/html/js/ui-load.js"></script>-->
<!--<script src="http://template.down.swap.wang/ui/angulr_2.0.1/html/js/ui-jp.config.js"></script>-->
<!--<script src="http://template.down.swap.wang/ui/angulr_2.0.1/html/js/ui-jp.js"></script>-->
<!--<script src="http://template.down.swap.wang/ui/angulr_2.0.1/html/js/ui-nav.js"></script>-->
<!--<script src="http://template.down.swap.wang/ui/angulr_2.0.1/html/js/ui-toggle.js"></script>-->
<!--<script src="http://ie.swapteam.cn/ie.js"></script>-->
<script src="../libs/jquery/jquery/dist/jquery.js"></script>
<script src="../libs/jquery/bootstrap/dist/js/bootstrap.js"></script>
<script src="js/ui-load.js"></script>
<script src="js/ui-jp.config.js"></script>
<script src="js/ui-jp.js"></script>
<script src="js/ui-nav.js"></script>
<script src="js/ui-toggle.js"></script>
<script src="js/ui-client.js"></script>
  <?php
  //非结算的代理商余额提示功能
  if($_SESSION['agentuuid'] == '1' && $userrow['type']== '2') {                                                          //代理商权限
        echo '
        <script>
      window.onload = aa();
      function aa(){
          $.ajax({
              type: "GET",
              url: "../api/ajax_api/ajax_user_balance.php",
              data: "",
              success: function(data){
                  if(Number(data) <= \'100\'){
                      alert(\'余额小于￥100 请尽快充值！当前余额是：\'+data+\'元。\');
                  }
              }
          });
          setTimeout("aa()",60000);
      }
  </script>';
  }
  ?>


</body>
</html>
