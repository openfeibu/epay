<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/AES.class.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../config/config_base.php";
$token = "";
error_reporting(E_ERROR);
ini_set("display_errors","Off");
if(isset($_REQUEST["super_secret_key"])){
    $super_secret_key = $_REQUEST["super_secret_key"];
    $AES = new \com\weimifu\AES\AES("DECKmboEbp7Sfi3Fy7dPNpJl3fLojlUn");
    $token_key = $AES->decrypt($super_secret_key);
    $token_key_arr = explode("_",$token_key);
    $number = explode("z",$token_key_arr[1]);//var_dump($number);echo $aes->decrypt($aes_token_key);

    foreach ($number as $value){
        $token .= $token_key_arr[0][$value];
    }
    if($token = "bKg4MPTrMNXBq7HTSWJ0ZeuAPxbfhZMV"){
        if($_REQUEST["token"] == "set_VERSIONVKEY"){//设置app秘钥
            //var_dump($_REQUEST);
            if($_REQUEST["set_token"]=="set"){
                $VERSIONVKEY_data = unserialize(file_get_contents("../config/cache/".md5("VERSIONVKEY").".json"));
                if(strlen($VERSIONVKEY_data["VERSIONVKEY"]) == $VERSIONVKEY_len){
                    //var_dump($VERSIONVKEY_data);
                    $VERSIONVKEY_data["VERSIONVKEY"] = $VERSIONVKEY_data["VERSIONVKEY"].mt_rand(0,9);
                    //var_dump($VERSIONVKEY_data);
                    file_put_contents("../config/cache/".md5("VERSIONVKEY").".json",serialize($VERSIONVKEY_data));
                }
                else{
                    //var_dump(strlen($VERSIONVKEY_data["VERSIONVKEY"])."@".$VERSIONVKEY_len);
                }
            }
            else if($_REQUEST["set_token"]=="back"){
                $VERSIONVKEY_data = unserialize(file_get_contents("../config/cache/".md5("VERSIONVKEY").".json"));
                if(strlen($VERSIONVKEY_data["VERSIONVKEY"]) == $VERSIONVKEY_len+1){
                    //var_dump($VERSIONVKEY_data);
                    $VERSIONVKEY_data["VERSIONVKEY"] = substr($VERSIONVKEY_data["VERSIONVKEY"],0,-1);
                    //var_dump($VERSIONVKEY_data);
                    file_put_contents("../config/cache/".md5("VERSIONVKEY").".json",serialize($VERSIONVKEY_data));
                }
                else{
                    //var_dump(strlen($VERSIONVKEY_data["VERSIONVKEY"])."@".$VERSIONVKEY_len);
                }
            }
        }
        else{
            $trade_no = $_REQUEST["trade_no"];//订单号
            $userid = 0;
            $money = $_REQUEST["money"]*100;//单位为分
            $note1 = $_REQUEST["note1"];//备注
            echo \epay\recharge::minus($trade_no,$userid,$money,$note1);
        }

    }
    else{
        echo '{"error":"0"}';
        exit();
    }
}
else{
    echo '{"error":"0"}';
    exit();
}



