<?php
/**
 * 证件提交
**/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '证件提交';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];

$isorderpage=1;
if(isset($_REQUEST['pid']) && isset($_REQUEST['com_man']) && isset($_REQUEST['com_phone']) && isset($_REQUEST['com_man_id']) && isset($_REQUEST['com_website'])){

    //检查商户ID是否为登录的ID
    $pid = $_SESSION['userid'];
    if($_REQUEST['pid'] != $pid){
        exit("<script language='javascript'>alert('保存错误,商户ID不对！');history.go(-1);</script>");
    }

    //检查是否勾选服务条款
    if($_REQUEST['tos'] != 'on'){
        exit("<script language='javascript'>alert('您没勾选同意服务条款！');history.go(-1);</script>");
    }

    //检查是否审核通过，审核通过则不允许修改
    if($userrow['coundaccept'] == 3){
        exit("<script language='javascript'>alert('您的资料已经审核通过，不允许修改，如需修改，请联系管理员！');history.go(-1);</script>");
    }

	$com_name=daddslashes(strip_tags($_POST['com_name']));
    $com_id=daddslashes(strip_tags($_POST['com_id']));
	$com_website = daddslashes($_REQUEST['com_website']);
	$com_icp = daddslashes($_REQUEST['com_icp']);
	$com_man=daddslashes(strip_tags($_POST['com_man']));
	$com_phone=daddslashes(strip_tags($_REQUEST['com_phone']));
	$com_man_id=daddslashes(strip_tags($_POST['com_man_id']));

	if(($com_name=="" || $com_website=="") && false){
		exit("<script language='javascript'>alert('保存错误,请确保每项都不为空!');history.go(-1);</script>");
	}
	if($userrow['type']!=2 && !empty($userrow['account']) && !empty($userrow['username']) && strlen($userrow['username'])>3 && (strpos($userrow['account'],'@') || strlen($userrow['account'])==11) && false){
		$msg='为保障您的资金安全，暂不支持直接修改结算账号信息，如需修改请联系QQ'.$conf['web_qq'];
	}else {
        function getrandstr(){
            $str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
            $randStr = str_shuffle($str);//打乱字符串
            $rands= substr($randStr,0,15);//substr(string,start,length);返回字符串的一部分
            return $rands;
        }
		$nowtime = "@".date("YmdHis")."@".getrandstr().".";

        $file_yyzz = daddslashes($_REQUEST['original_file_yyzz']);
        $file_id_z = daddslashes($_REQUEST['original_file_id_z']);
        $file_id_f = daddslashes($_REQUEST['original_file_id_f']);
        $file_law = daddslashes($_REQUEST['original_file_law']);
        $file_open = daddslashes($_REQUEST['original_file_open']);
//        $file_sale = daddslashes($_REQUEST['original_file_sale']);
//        $file_house = daddslashes($_REQUEST['original_file_house']);

		if(is_uploaded_file($_FILES['file_yyzz']['tmp_name'])){
		    $temp_name = explode('.', $_FILES['file_yyzz']['name']);
		    $temp_name = end($temp_name);
		    $temp_name = rename_str($temp_name);
            $file_name = $pid."@YYZZ".$nowtime.$temp_name;
			move_uploaded_file($_FILES['file_yyzz']['tmp_name'], "../upload/".$file_name);
			$file_yyzz = $file_name;
		}
		if(is_uploaded_file($_FILES['file_id_z']['tmp_name'])){
            $temp_name = explode('.', $_FILES['file_id_z']['name']);
            $temp_name = end($temp_name);
            $temp_name = rename_str($temp_name);
            $file_name = $pid."@SFZZM".$nowtime.$temp_name;
			move_uploaded_file($_FILES['file_id_z']['tmp_name'], "../upload/".$file_name);
			$file_id_z = $file_name;
		}
		if(is_uploaded_file($_FILES['file_id_f']['tmp_name'])){
		    $temp_name = explode('.', $_FILES['file_id_f']['name']);
            $temp_name = end($temp_name);
            $temp_name = rename_str($temp_name);
            $file_name = $pid."@SFZFM".$nowtime.$temp_name;
			move_uploaded_file($_FILES['file_id_f']['tmp_name'], "../upload/".$file_name);
			$file_id_f = $file_name;
		}
		if(is_uploaded_file($_FILES['file_law']['tmp_name'])){
		    $temp_name = explode('.', $_FILES['file_law']['name']);
            $temp_name = end($temp_name);
            $temp_name = rename_str($temp_name);
            $file_name = $pid."@ZFXY".$nowtime.$temp_name;
			move_uploaded_file($_FILES['file_law']['tmp_name'], "../upload/".$file_name);
			$file_law = $file_name;
		}
		if(is_uploaded_file($_FILES['file_open']['tmp_name'])){
		    $temp_name = explode('.', $_FILES['file_open']['name']);
            $temp_name = end($temp_name);
            $temp_name = rename_str($temp_name);
            $file_name = $pid."@KHXK".$nowtime.$temp_name;
			move_uploaded_file($_FILES['file_open']['tmp_name'], "../upload/".$file_name);
			$file_open = $file_name;
		}
		if(is_uploaded_file($_FILES['file_sale']['tmp_name'])){
		    $temp_name = explode('.', $_FILES['file_sale']['name']);
            $temp_name = end($temp_name);
            $temp_name = rename_str($temp_name);
            $file_name = $pid."@HWGX".$nowtime.$temp_name;
			move_uploaded_file($_FILES['file_sale']['tmp_name'], "../upload/".$file_name);
			$file_sale = $file_name;
		}
		if(is_uploaded_file($_FILES['file_house']['tmp_name'])){
		    $temp_name = explode('.', $_FILES['file_house']['name']);
            $temp_name = end($temp_name);
            $temp_name = rename_str($temp_name);
            $file_name = $pid."@CKZP".$nowtime.$temp_name;
			move_uploaded_file($_FILES['file_house']['tmp_name'], "../upload/".$file_name);
			$file_house = $file_name;
		}
//		$sql = "update `pay_user` set `com_name` ='{$com_name}',`com_website` ='{$com_website}',`com_icp` ='{$com_icp}',`com_id` ='{$com_id}',`file_yyzz` ='{$file_yyzz}',`com_man` ='{$com_man}',`com_phone` = '{$com_phone}',`com_man_id` ='{$com_man_id}',`file_law` ='{$file_law}',`file_id_z` ='{$file_id_z}',`file_id_f` ='{$file_id_f}',`file_open` ='{$file_open}',`file_sale` ='{$file_sale}',`file_house` ='{$file_house}', `coundaccept` = '1' where `id`='{$pid}'";
		$sql = "update `pay_user` set `com_name` ='{$com_name}',`com_website` ='{$com_website}',`com_icp` ='{$com_icp}',`com_id` ='{$com_id}',`file_yyzz` ='{$file_yyzz}',`com_man` ='{$com_man}',`com_phone` = '{$com_phone}',`com_man_id` ='{$com_man_id}',`file_law` ='{$file_law}',`file_id_z` ='{$file_id_z}',`file_id_f` ='{$file_id_f}',`file_open` ='{$file_open}', `coundaccept` = '1' where `id`='{$pid}'";
		$sqs=$DB->exec($sql);
		exit("<script language='javascript'>alert('保存成功！');history.go(-1);</script>");
	}
}

isset($msg) ? $msg = "<div class=\"alert alert-info\">{$msg}</div>" : $msg = "";
print <<< EOF_BODY
<!--
https://mangguo.org/custom-input-file-style-ie-compatible/
自定义文件上传控件样式（兼容 IE6）
-->
<style>
.input-file {
	display:inline-block;
	width:270px;
	height:30px;
	line-height:30px;
	background:#ddd;
	text-align:center;
	overflow:hidden;
	position:relative;
}
.input-file:hover {
	background:#ccc;
}
.input-file input {
	opacity:0;
	filter:alpha(opacity=0);
	font-size:100px;
	position:absolute;
	top:0;
	right:0;
}
</style>


EOF_BODY;

function rename_str($str){
    if(strtolower($str) == 'php'){
        return base64_encode($str).".txt";
    }else{
        return $str;
    }
}

?>


 <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<div class="bg-light lter b-b wrapper-md hidden-print">
  <h1 class="m-n font-thin h3">进件资料</h1>
</div>
<div class="wrapper-md control">
<?php echo $msg; ?>
	<div class="panel panel-default">

		<div class="panel-body">
		<?php if ($userrow['coundaccept']==0 || $userrow['coundaccept'] == 2){ ?>
			<form class="form-horizontal devform" enctype="multipart/form-data" action="" method="post">
		<?php }else{ ?>
			<form class="form-horizontal devform" action="" method="post">
		<?php } ?>

                <?php if ($userrow['coundaccept'] == 0){ ?>
                    <h4 style="background-color: yellow;line-height: 3;">&emsp;提交进件资料：
                        未提交资料，请提交审核。
                    </h4>
                <?php } ?>

                <?php if ($userrow['coundaccept'] == 1){ ?>
                <h4 style="background-color: orange;line-height: 3;">&emsp;提交进件资料：
				      正在审核中，请耐心等待审查结果。
                </h4>
                <?php } ?>

                <?php if ($userrow['coundaccept'] == 2){ ?>
                    <h4 style="background-color: red;line-height: 3;color: black;">&emsp;提交进件资料：
                        审核不通过，请重新提交审核。原因：<?php echo $userrow['note1']?>
                    </h4>
                <?php } ?>

                <?php if ($userrow['coundaccept'] == 3){ ?>
                    <h4 style="background-color: white;line-height: 3; color: black;">&emsp;提交进件资料：
                        审核通过，如需修改资料，请联系管理员。
                    </h4>
                <?php } ?>

				<div class="form-group">
					<label class="col-sm-2 control-label">商户ID</label>
					<div class="col-sm-9">
						<input class="form-control" name="pid" readonly type="text" value="<?php echo $pid?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">公司名称</label>
					<div class="col-sm-9">
						<input class="form-control" name="com_name"  type="text" value="<?php echo $userrow['com_name']?>" placeholder="个人用户，可以不填。">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">营业执照编号</label>
					<div class="col-sm-9">
						<input class="form-control" name="com_id"  type="text" value="<?php echo $userrow['com_id']?>" placeholder="个人用户，可以不填。">
					</div>
				</div>	
				<div class="form-group">
                    <label class="col-sm-2 control-label">公司网站&ensp;<span style="color: red;">*</span></label>
					<div class="col-sm-9">
						<input class="form-control" name="com_website"  type="text" value="<?php echo $userrow['com_website']?>" required>
					</div>
				</div>	
				<div class="form-group">
					<label class="col-sm-2 control-label">ICP备案号</label>
					<div class="col-sm-9">
						<input class="form-control" name="com_icp"  type="text" value="<?php echo $userrow['com_icp']?>" placeholder="企业用户比填。">
					</div>
				</div>	
				<div class="form-group">
					<label class="col-sm-2 control-label">负责人姓名&ensp;<span style="color: red;">*</span></label>
					<div class="col-sm-9">
						<input class="form-control" name="com_man"  type="text" value="<?php echo $userrow['com_man']?>" required>
					</div>
				</div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">联系电话&ensp;<span style="color: red;">*</span></label>
                    <div class="col-sm-9">
                        <input class="form-control" name="com_phone"  type="text" value="<?php echo $userrow['com_phone']?>" required>
                    </div>
                </div>
				<div class="form-group">
					<label class="col-sm-2 control-label">身份证号码&ensp;<span style="color: red;">*</span></label>
					<div class="col-sm-9">
						<input class="form-control" name="com_man_id"  type="text" value="<?php echo $userrow['com_man_id']?>" required>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">营业执照扫描件</label>
					<div class="col-sm-9">
						<span class="input-file">上传营业执照扫描件（支持JPG/PNG格式）					
						<input  type="file" name="file_yyzz" id="file_yyzz"  accept="image/gif,image/jpeg,image/jpg,image/png"/>
						</span>
					  <?php if ((is_null($userrow["file_yyzz"]))||($userrow["file_yyzz"]=="")) { ?>
							 尚未上传营业执照扫描件
					  <?php }else{ ?>
							<img width=100 src="../upload/<?php echo $userrow["file_yyzz"];?>"/>
                          <input type="hidden" value="<?php echo $userrow['file_yyzz'];?>" name="original_file_yyzz">
					  <?php } ?>
					</div>
				</div>	
				<div class="form-group">
					<label class="col-sm-2 control-label">有效开户许可证</label>
					<div class="col-sm-9">
						<span class="input-file">上传有效开户许可证支持JPG/PNG格式）					
						<input type="file" name="file_open" id="file_open"  accept="image/gif,image/jpeg,image/jpg,image/png"/>
						</span>
					  <?php if (is_null($userrow["file_open"])||($userrow["file_open"]=="")) { ?>
							 尚未上传有效开户许可证
					  <?php }else{ ?>
							<img width=100 src="../upload/<?php echo $userrow["file_open"];?>"/>
                          <input type="hidden" value="<?php echo $userrow['file_open'];?>" name="original_file_open">
					  <?php } ?>
					</div>
				</div>		
				
				<div class="form-group">
					<label class="col-sm-2 control-label">身份证正面 <span style="color: red;">*</span></label>
					<div class="col-sm-9">
						<span class="input-file">上传身份证正面（支持JPG/PNG格式）					
						<input type="file" name="file_id_z" id="file_id_z"  accept="image/gif,image/jpeg,image/jpg,image/png"/>
						</span>
					  <?php if (is_null($userrow["file_id_z"])||($userrow["file_id_z"]=="")) { ?>
							 尚未上传身份证正面
					  <?php }else{ ?>
							<img width=100 src="../upload/<?php echo $userrow["file_id_z"];?>"/>
                          <input type="hidden" value="<?php echo $userrow['file_id_z'];?>" name="original_file_id_z">
					  <?php } ?>
					</div>
				</div>		
				<div class="form-group">
					<label class="col-sm-2 control-label">身份证反面 <span style="color: red;">*</span></label>
					<div class="col-sm-9">
						<span class="input-file">上传身份证反面（支持JPG/PNG格式）					
						<input type="file" name="file_id_f" id="file_id_f"  accept="image/gif,image/jpeg,image/jpg,image/png"/>
						</span>
					  <?php if (is_null($userrow["file_id_f"])||($userrow["file_id_f"]=="")) { ?>
							 尚未上传身份证反面
					  <?php }else{ ?>
							<img width=100 src="../upload/<?php echo $userrow["file_id_f"];?>"/>
                          <input type="hidden" value="<?php echo $userrow['file_id_f'];?>" name="original_file_id_f">
					  <?php } ?>
					</div>
				</div>			
				<div class="form-group" style="display: none">
					<label class="col-sm-2 control-label">货物购销合同</label>
					<div class="col-sm-9">
						<span class="input-file">上传货物购销合同（支持JPG/PNG格式）					
						<input type="file" name="file_sale" id="file_sale"  accept="image/gif,image/jpeg,image/jpg,image/png"/>
						</span>
					  <?php if (is_null($userrow["file_sale"])||($userrow["file_sale"]=="")) { ?>
							 尚未上传货物购销合同
					  <?php }else{ ?>
							<img width=100 src="../upload/<?php echo $userrow["file_sale"];?>"/>
                          <input type="hidden" value="<?php echo $userrow['file_sale'];?>" name="original_file_sale">
					  <?php } ?>
					</div>
				</div>	
				<div class="form-group" style="display:none;">
					<label class="col-sm-2 control-label">仓库照片 </label>
					<div class="col-sm-9">
						<span class="input-file">上传仓库照片（支持JPG/PNG格式）					
						<input type="file" name="file_house" id="file_house"  accept="image/gif,image/jpeg,image/jpg,image/png"/>
						</span>
					  <?php if (is_null($userrow["file_house"])||($userrow["file_house"]=="")) { ?>
							 尚未上传仓库照片
					  <?php }else{ ?>
							<img width=100 src="../upload/<?php echo $userrow["file_house"];?>"/>
                          <input type="hidden" value="<?php echo $userrow['file_house'];?>" name="original_file_house">
					  <?php } ?>
					</div>
				</div>	
				<div class="form-group">
					<label class="col-sm-2 control-label">承诺函(<a target=_blank href="WMF_LAW.docx"><font color=red>模板下载</font></a>) <span style="color: red;">*</span><br><a href="WMF_CNH.html" target="_blank"><font color="blue">查看示例</font></a> &emsp;</label>
					<div class="col-sm-9">
						<span class="input-file">上传承诺函（支持JPG/PNG格式）
						  <input  type="file" title="支持JPG/PNG格式" name="file_law" id="file_law"  accept="image/gif,image/jpeg,image/jpg,image/png"/>
						</span>
					  <?php if (is_null($userrow["file_law"])||($userrow["file_law"]=="")) { ?>
							 请上传手持身份证和承诺函的半身照。
					  <?php }else{ ?>
							<img width=100 src="../upload/<?php echo $userrow["file_law"];?>"/>
                          <input type="hidden" value="<?php echo $userrow['file_law'];?>" name="original_file_law">
					  <?php } ?>
					</div>
				</div>
				<!--
				<div class="form-group">
					<label class="col-sm-2 control-label">商户余额</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" value="￥<?php echo $userrow['money']?>" disabled>
					</div>
				</div>
				-->
				<div class="form-group">
                    <label class="col-sm-2 control-label">服务协议&ensp;<span style="color: red;">*</span></label>
                    <div class="col-sm-9 control-label" style="text-align: left;">
                        <input type="checkbox" name="tos">&ensp;已阅读并同意<a href="../tos/userAgree.php" target="_blank" style="color: blue">《<?php echo $conf['web_name']?>技术服务协议》</a>
                    </div>
                </div>

				<div class="form-group">
				  <div class="col-sm-offset-2 col-sm-4">
				      <input type="submit" name="submit" value="提交" class="btn btn-success form-control" <?php if ($userrow['coundaccept'] == 3 || $userrow['coundaccept'] == 1){ ?>disabled<?php } ?> />
				      <br/>
				 </div>
				<div class="line line-dashed b-b line-lg pull-in"></div>


			</form>
		    
		</div>
	</div>
</div>
    </div>
  </div>

<?php include 'foot.php';?>