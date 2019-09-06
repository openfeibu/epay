<?php
//邮箱白名单
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '代理短信验证设置';
require_once __DIR__.DIRECTORY_SEPARATOR."../config/config_base.php";
require_once __DIR__.DIRECTORY_SEPARATOR."./head.php";
if($agent_sms_switch != true) {
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
                            添加代理短信手机号码（用于修改通道）
                        </div>
                        <div class="panel-body">
                            <form class="form-horizontal devform">
                                <?php
                                $sql = "SELECT `id`,`account`,`username`,`com_phone`,`addtime` FROM `pay_user`";
                                $row = $DB->query($sql)->fetchAll();
                                if(!isset($row)){
                                    echo '<div class="form-group">
                                <div style="color: red;margin-left: 15px;">
                                    添加代理信息
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>';
                                }
                                echo '<table style="width: 100%;font-size: 15px;">
                                    <thead>
                                        <th width="10%">代理id</th>
                                        <th width="20%">代理结算帐号</th>
                                        <th width="15%">代理名称</th>
                                        <th width="15%">代理手机号码</th>
                                        <th width="20%">短信发送时间</th>
                                        <th width="20%">注册时间</th>
                                    </thead>
                                    <tbody>
                                    ';
                                foreach ($row as $value){
                                    $json = unserialize($value["com_phone"]);
                                    echo "<tr style=\"border-bottom: 1px solid #dee5e7;\">
                                            <td style=\"padding:10px 0px;\">{$value["id"]}</td>
                                            <td style=\"padding:10px 0px;\">{$value["account"]}</td>
                                            <td style=\"padding:10px 0px;\">{$value["username"]}</td>
                                            <td style=\"padding:10px 0px;\" id='{$value["id"]}' ondblclick='edit_content(this)'>{$json["phone"]}</td>
                                            <td style=\"padding:10px 0px;\">{$json["time"]}</td>
                                            <td style=\"padding:10px 0px;\">{$value["addtime"]}</td>
                                        </tr>";
                                }
                                echo '
                                    </tbody>
                                </table>';
                                ?>
                            </form>
                        </div>
                    </div>

                    <script>
                        function edit_content(element){
                            var oldhtml = element.innerHTML;//获得元素之前的内容
                            var newobj = document.createElement('input');//创建一个input元素
                            newobj.type = 'text';//为newobj元素添加类型
                            newobj.value=oldhtml;
                            element.innerHTML = '';　　 //设置元素内容为空
                            element.appendChild(newobj);//添加子元素
                            newobj.focus();//获得焦点
                            //设置newobj失去焦点的事件
                            newobj.onblur = function(){
                                //下面应该判断是否做了修改并使用ajax代码请求服务端将id与修改后的数据提交
                                //alert(element.id);
                                if(!re_phone(this.value)){
                                    return;
                                }
                                $.ajax({
                                    url:"../api/ajax_api/ajax_agent_set_phone.php",
                                    type:"get",
                                    dataType:"json",
                                    data:{
                                        agent_id:element.id,
                                        phone:this.value
                                    },
                                    success:function(data){
                                        if(data.status){
                                            alert(data.msg);
                                        }
                                        else{
                                            alert(data.msg);
                                        }
                                    }
                                })

                                //当触发时判断newobj的值是否为空，为空则不修改，并返回oldhtml
                                element.innerHTML = this.value ? this.value : oldhtml;
                            }
                        }

                        function re_phone(buyer_phone) {
                            //手机号正则
                            var phoneReg = /(^1[3|4|5|7|8]\d{9}$)|(^09\d{8}$)/;
                            //电话
                            var phone = $.trim(buyer_phone);
                            if (!phoneReg.test(phone)) {
                                return false;
                            }
                            else{
                                return true;
                            }
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
                            代理短信验证设置密码验证
                        </div>
                        <div class="panel-body">
                            <form class="form-horizontal devform" method="get" action="./agent_sms_white_list.php">
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