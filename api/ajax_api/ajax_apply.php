<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../../config_base.php";
if(!session_id()){
    session_start();
}
if(isset($_SESSION['userid'])){
    $id = $_SESSION['userid'];
}elseif(isset($_SESSION['admin_id'])){
    $id = $_SESSION['admin_id'];
}else{
    echo "error";
    exit();
}
/*去除时间限制
$begintime = date("Y-m-d 00:00:00");
$endtime = date("Y-m-d 23:59:59");

$sql = " `addtime` >= '{$begintime}' AND `addtime` <= '{$endtime}' and status=0";

 */
$sql2 = "SELECT status FROM `pay_apply` WHERE status=0";                        //查询语句
$numrows = $DB->query($sql2)->rowCount();                                       //查询出未处理的条数
$json = array();                                                                //创建数组
if($numrows){                                                                   //判断有没有未处理的提现申请
    if(isset ($_SESSION["ajax_apply"])){                                        //如果存储在session中的未处理的提现申请数值是存在的
        if($_SESSION["ajax_apply"]>=$numrows){                                  //session中的未处理的提现申请数是否大于等于查询出来的数值
            $json["error"] = 0;                                                 //error这个返回值是用于弹不弹出提示框
            $json["ajax_apply"] = $numrows;                                     //ajax_apply是提现申请数
        }
        else{
            $json["error"] = 1;                                                 //大于session中提现申请数，则需弹出提示框
            $json["ajax_apply"] = $numrows;
        }
    }
    else{
        $json["error"] = 1;                                                     //session中不存在提现申请数值，也需弹出提示框
        $json["ajax_apply"] = $numrows;
    }
    $_SESSION["ajax_apply"] = $numrows;                                         //存储session提现申请数值
}
else{
    $json["error"] = 0;                                                         //没有查询未处理的提现申请，则不弹框，不显示，设置session为0
    $json["ajax_apply"] = 0;
    $_SESSION["ajax_apply"] = 0;
}
echo json_encode($json);
?>
