<?php
$clientip = real_ip();

if(isset($_COOKIE["admin_token"]) && false){
    $token = authcode(daddslashes($_COOKIE['admin_token']),'DECODE',SYS_KEY);
    list($user,$sid) = explode("\t",$token);
    $userrow = $DB->query("SELECT * FROM pay_admin WHERE admin_user='{$user}' limit 1")->fetch();

    $prevsql = "(1=2 ";
    $preuidsql = "(1=2 ";
    //$session=md5($conf['admin_user'].$conf['admin_pwd'].$password_hash);
    $session = md5($userrow['admin_user'].$userrow['admin_pwd'].$password_hash);
    if($session == $sid){
        $islogin = 1;

        $userrow = $DB->query("SELECT * FROM pay_admin WHERE admin_user='{$user}' limit 1")->fetch();
        $agentid = $userrow['id'];
        $rs = $DB->query("SELECT * FROM pay_user WHERE uid={$agentid}");

        $preuidsql = $preuidsql." or uid=".$agentid;

        //计算出前一个交易日及其之前的提现总额
        while($row = $rs->fetch()){
            $prevsql = $prevsql." or pid=".$row['id'];

        }
        $prevsql = $prevsql.")";
        $preuidsql = $preuidsql.")";


    }
}
if(isset($_COOKIE["user_token"]) && false){
    $token = authcode(daddslashes($_COOKIE['user_token']),'DECODE',SYS_KEY);
    list($pid,$sid,$expiretime) = explode("\t",$token);
    $userrow = $DB->query("SELECT * FROM pay_user WHERE id='{$pid}' limit 1")->fetch();
    $session = md5($userrow['id'].$userrow['key'].$password_hash);
    if($session == $sid && $expiretime > time()){
        $islogin2 = 1;
    }
}
if(isset($_SESSION['admin_userid'])){
    $admin_userid = $_SESSION['admin_userid'];
    $admin_id = $admin_userid;
    $sql = "SELECT * FROM `pay_admin` WHERE `id` = '{$admin_userid}' limit 1";
    $admin_userrow = $DB->query($sql)->fetch();
}
if(isset($_SESSION['userid'])){
    $pid = $_SESSION['userid'];
    $sql = "SELECT * FROM `pay_user` WHERE `id` = '{$pid}' limit 1";
    $userrow = $DB->query($sql)->fetch();
}

//if($_SESSION['user_token_fs'] != ""){
//    $token = authcode(daddslashes($_SESSION['user_token_fs']),'DECODE',SYS_KEY);
//    list($pid,$sid,$expiretime) = explode("\t",$token);
//    $userrow = $DB->query("SELECT * FROM pay_user WHERE id='{$pid}' limit 1")->fetch();
//    $session = md5($userrow['id'].$userrow['key'].$password_hash);
//    if($session == $sid && $expiretime > time()){
//        $islogin2 = 1;
//    }
//}

//if($_SESSION['user_token_fs2'] != ""){
//    $token = authcode(daddslashes($_SESSION['user_token_fs2']),'DECODE',SYS_KEY);
//    list($user,$sid) = explode("\t",$token);
//    $userrow = $DB->query("SELECT * FROM pay_admin WHERE admin_user='{$user}' limit 1")->fetch();
//
//    $prevsql = "(1=2 ";
//    $preuidsql = "(1=2 ";
//    //$session=md5($conf['admin_user'].$conf['admin_pwd'].$password_hash);
//    $session = md5($userrow['admin_user'].$userrow['admin_pwd'].$password_hash);
//    if($session == $sid){
//        $islogin = 1;
//
//        $userrow = $DB->query("SELECT * FROM pay_admin WHERE admin_user='{$user}' limit 1")->fetch();
//        $agentid = $userrow['id'];
//        $rs = $DB->query("SELECT * FROM pay_user WHERE uid={$agentid}");
//
//        $preuidsql = $preuidsql." or uid=".$agentid;
//
//        //计算出前一个交易日及其之前的提现总额
//        while($row = $rs->fetch()){
//            $prevsql = $prevsql." or pid=".$row['id'];
//
//        }
//        $prevsql = $prevsql.")";
//        $preuidsql = $preuidsql.")";
//
//
//    }
//}
