<?php
//只有管理员能查看此页
require_once __DIR__.DIRECTORY_SEPARATOR."config_base.php";
session_start();
//if(!isset($_SESSION['admin_userid'])){
//    exit();
//}
set_time_limit(0);
ob_start(); //打开输出缓冲区
ob_end_flush();
ob_implicit_flush(1); //立即输出
require_once __DIR__.DIRECTORY_SEPARATOR."includes/api/autoload.php";
require_once __DIR__.DIRECTORY_SEPARATOR."includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."includes/api/debug.php";
$uuid = \Webpatser\Uuid\Uuid::generate(4)->string;
echo $uuid;
//return;

while(true){
    $uuid = \epay\uuid::generate(4);
    $sql = "UPDATE `pay_admin` SET `uuid` = '{$uuid}' WHERE `uuid` = '' LIMIT 1; ";
    $result = $DB->query($sql);
    var_dump($result->rowCount());
    if($result->rowCount() == 0){
        break;
    }
}

while(true){
    $uuid = \epay\uuid::generate(4);
    $sql = "UPDATE `pay_user` SET `uuid` = '{$uuid}' WHERE `uuid` = '' LIMIT 1; ";
    $result = $DB->query($sql);
    var_dump($result->rowCount());
    if($result->rowCount() == 0){
        break;
    }
}

//更新pay_user表
while(true){
    $sql = "UPDATE `pay_user` SET `agentuuid` = '1' WHERE `uid` = '1' LIMIT 1; ";
    $result = $DB->query($sql);
    var_dump($result->rowCount());
    if($result->rowCount() == 0){
        break;
    }
}

while(true){
    $sql = "UPDATE `pay_user` SET `agentuuid` = '0',`uid` = '0' WHERE `agentuuid` != '0' AND (`uid` = '0' OR `uid` = '' OR `uid` IS NULL) LIMIT 1; ";
    $result = $DB->query($sql);
    var_dump($result->rowCount());
    if($result->rowCount() == 0){
        break;
    }
}

while(true){
    $sql = "SELECT * FROM `pay_user` WHERE `uid` > 1 AND `agentuuid` = ''";
    $result = $DB->query($sql)->fetch();
    if($result){
        $uid = $result['uid'];
        $user = \epay\user::find_user($uid);
        $agentuuid = $user['uuid'];
        $sql = "UPDATE `pay_user` SET `agentuuid` = '{$agentuuid}' WHERE `id` = '{$result['id']}' LIMIT 1; ";
        $result = $DB->query($sql);
        var_dump($result->rowCount());
    }else{
        break;
    }
}

while(true){
    $sql = "SELECT * FROM `pay_user` WHERE `uid` = 1 AND `agentuuid` = ''";
    $result = $DB->query($sql)->fetch();
    if($result){
        $uid = $result['uid'];
        $agentuuid = "1";
        $sql = "UPDATE `pay_user` SET `agentuuid` = '{$agentuuid}' WHERE `id` = '{$result['id']}' LIMIT 1; ";
        $result = $DB->query($sql);
        var_dump($result->rowCount());
    }else{
        break;
    }
}

while(true){
    $sql = "SELECT * FROM `pay_user` WHERE `adminuuid` = '' LIMIT 1";
    $result = $DB->query($sql)->fetch();
    if($result){
        //查找默认管理员uuid
        $admin_user = \epay\admin::find(0);
        $sql = "UPDATE `pay_user` SET `adminuuid` = '{$admin_user['uuid']}' WHERE `id` = '{$result['id']}' LIMIT 1;";
        $result = $DB->query($sql);
        var_dump($result->rowCount());
    }else{
        break;
    }
}

//pay_balance
while(true){
    $sql = "SELECT * FROM `pay_balance` WHERE `uuid` = '' LIMIT 1; ";
    $result = $DB->query($sql)->fetch();
    if($result){
        $id = $result['id'];
        if($id < 10){
            $admin = \epay\admin::find($id);
            $uuid = $admin['uuid'];
        }else{
            $user = \epay\user::find_user($id);
            $uuid = $user['uuid'];
        }
        $sql = "UPDATE `pay_balance` SET `uuid` = '{$uuid}' WHERE `id` = '{$id}' LIMIT 1; ";
        $result = $DB->query($sql);
        var_dump($result->rowCount());
    }else{
        break;
    }
}

//pay_recharge
while(true){
    $sql = "SELECT * FROM `pay_recharge` WHERE `uuid` = '' LIMIT 1; ";
    $result = $DB->query($sql)->fetch();
    if($result){
        $id = $result['id'];
        if($id < 10){
            $admin = \epay\admin::find($id);
            $uuid = $admin['uuid'];
        }else{
            $user = \epay\user::find_user($id);
            $uuid = $user['uuid'];
        }
        $sql = "UPDATE `pay_recharge` SET `uuid` = '{$uuid}' WHERE `id` = '{$id}' LIMIT 1; ";
        $result = $DB->query($sql);
        var_dump($result->rowCount());
    }else{
        break;
    }
}

//pay_order
while(true && false){
    $sql = "SELECT * FROM `pay_order` WHERE `uuid` = '' LIMIT 1; ";
    $result = $DB->query($sql)->fetch();
    if($result){
        $trade_no = $result['trade_no'];
        $id = $result['pid'];
        if($id < 10){
            $admin = \epay\admin::find($id);
            $uuid = $admin['uuid'];
        }else{
            $user = \epay\user::find_user($id);
            $uuid = $user['uuid'];
            $agentuuid = $user['agentuuid'];
        }
        $sql = "UPDATE `pay_order` SET `uuid` = '{$uuid}',`agentuuid` = '{$agentuuid}' WHERE `trade_no` = '{$trade_no}' LIMIT 1; ";
        $result = $DB->query($sql);
        var_dump($result->rowCount());
    }else{
        break;
    }
}

//$now = date("Y-m-d H:i:s");
//$sql2 = "INSERT IGNORE INTO `pay_balance` (`id`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES ('aa00', '0', '{$now}', CURRENT_TIMESTAMP, '0', '0');";
//$a=$DB->query($sql2);
//var_dump($DB->errorCode());

//只有管理员能查看此页
//require_once __DIR__.DIRECTORY_SEPARATOR."config_base.php";
//session_start();
//if(isset($_SESSION['admin_userid'])){
//    echo phpinfo();
//}
