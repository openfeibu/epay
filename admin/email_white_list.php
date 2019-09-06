<?php
//邮箱白名单
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '邮箱白名单';
require_once __DIR__.DIRECTORY_SEPARATOR."../config/config_base.php";
require_once __DIR__.DIRECTORY_SEPARATOR."./head.php";
if($email_switch != true) {
    exit("<script language='javascript'>window.location.href='./index.php';</script>");
}
?>

    <div id="content" class="app-content" role="main">
        <div class="app-content-body ">

            <div class="bg-light lter b-b wrapper-md hidden-print">
                <h1 class="m-n font-thin h3"><a href="recharge_result.php"><?php echo $title ?></a></h1>
            </div>

            <?php
            if(isset($_REQUEST["email_white_list_key"]) && $_REQUEST["email_white_list_key"]=="123"){
                ?>

                <div class="wrapper-md control">
                    <div class="panel panel-default">
                        <div class="panel-heading font-bold">
                            添加邮箱白名单（用于修改通道）
                        </div>
                        <div class="panel-body">
                            <form class="form-horizontal devform">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">邮箱</label>
                                    <div class="col-sm-9">
                                        <input class="form-control" type="text" id="email" value="">
                                    </div>
                                </div>
                                <div class="line line-dashed b-b line-lg pull-in"></div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">处理员名称</label>
                                    <div class="col-sm-9">
                                        <input class="form-control" type="text" id="name">
                                    </div>
                                </div>
                                <div class="line line-dashed b-b line-lg pull-in"></div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"></label>
                                    <div class="col-sm-9">
                                        <a href="javascript:email_add();" id="email_code_btn" class="btn btn-primary btn-sm" style="padding: 5px 30px;">
                                            保存
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading font-bold">
                            白名单列表
                        </div>
                        <div class="panel-body">
                            <form class="form-horizontal devform">

                                <?php
                                $file = "../config/cache/".md5("email_white_list");
                                if(!file_exists($file)){
                                    echo '<div class="form-group">
                                <div style="color: red;margin-left: 15px;">
                                    暂无邮箱白名单
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>';
                                }
                                else{
                                    $data = unserialize(file_get_contents($file));
                                    echo '<table style="width: 100%;font-size: 15px;">
                                    <thead>
                                        <th width="10%">id</th>
                                        <th width="20%">处理人员</th>
                                        <th width="25%">邮箱</th>
                                        <th width="25%">添加时间</th>
                                        <th width="20%">操作</th>
                                    </thead>
                                    <tbody>
                                    ';

                                    foreach ($data as $key => $value){
                                        echo "<tr style=\"border-bottom: 1px solid #dee5e7;\">
                                            <td style=\"padding:10px 0px;\">".($key+1)."</td>
                                            <td style=\"padding:10px 0px;\">{$value["name"]}</td>
                                            <td style=\"padding:10px 0px;\">{$value["email"]}</td>
                                            <td style=\"padding:10px 0px;\">{$value["addtime"]}</td>
                                            <td style=\"padding:10px 0px;\"><a href=\"javascript:email_del({$key});\" class=\"btn btn-primary btn-sm\" style=\"padding: 5px 30px;\">删除</a></td>

                                        </tr>";
                                    }
                                    echo '
                                    </tbody>
                                </table>';
                                }
                                ?>
                            </form>
                        </div>
                    </div>
                    <script>
                        window.onkeyup = function (event){
                            if(event.keyCode==13){
                                email_add()
                            }
                        }
                        function email_add(){
                            if($("#email").val() == ""){
                                $("#email").focus();
                                return;
                            }
                            if($("#name").val() == ""){
                                $("#name").focus();
                                return;
                            }
                            $.ajax({
                                url:"../api/ajax_api/ajax_email_while_list.php",
                                type:"GET",
                                dataType:"json",
                                data:{
                                    type:"set",
                                    email:$("#email").val(),
                                    name:$("#name").val(),
                                },
                                success:function(data){
                                    if(data.success=="true"){
                                        alert(data.msg);
                                        location.reload();
                                    }else{
                                        alert(data.msg);
                                    }
                                }
                            })
                        }
                        function email_del(i){
                            $.ajax({
                                url:"../api/ajax_api/ajax_email_while_list.php",
                                type:"POST",
                                dataType:"json",
                                data:{
                                    type:"delete",
                                    id:i
                                },
                                success:function(data){
                                    if(data.success=="true"){
                                        alert(data.msg);
                                        location.reload();
                                    }else{
                                        alert(data.msg);
                                    }
                                }
                            })
                        }
                    </script>
                </div>

                <?php
            }
            else{
                $msg = "";
                $ran = "";
                if(isset($_REQUEST["email_white_list_key"])){
                    $msg = '<div class="form-group">
                                    <div class="col-sm-9" style="margin-left: 15px;color: red;">
                                        密码错误，请重试!
                                    </div>
                                </div>
                                <div class="line line-dashed b-b line-lg pull-in"></div>';
                }
                for($i = 0;$i<100;$i++){
                    $ran .= chr(mt_rand(35, 126));
                }
                echo '<div class="wrapper-md control">
                    <div class="panel panel-default">
                        <div class="panel-heading font-bold">
                            邮箱白名单密码验证
                        </div>
                        <div class="panel-body">
                            <form class="form-horizontal devform" method="get" action="./email_white_list.php">
                            '.$msg.'
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">密码</label>
                                    <div class="col-sm-9">
                                        <input class="form-control" type="hidden" name="token" value="'.$ran.'">
                                        <input class="form-control" type="password" name="email_white_list_key">
                                    </div>
                                </div>
                                <div class="line line-dashed b-b line-lg pull-in"></div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"></label>
                                    <div class="col-sm-9">
                                        <input type="submit" class="btn btn-primary btn-sm" value="确定">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>';
            }
            ?>

        </div>
    </div>


<?php

require_once __DIR__.DIRECTORY_SEPARATOR."./foot.php";