<?php
namespace epay;

class balance{
    //初始化pay_balance表
    public static function create($data = array()){
        global $DB;
        $sql = "INSERT IGNORE INTO `pay_balance` (`id`,`uuid`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES (:userid,:uuid, 0, :now, CURRENT_TIMESTAMP, 0, 0);";
        $re = $DB->prepare($sql);
        $re->execute($data);
        return $re;
    }

    public static function find($data = array(),$update = false){
        global $DB;
        if($update){
            $sql = "SELECT * FROM `pay_balance` WHERE `uuid` = :uuid FOR UPDATE ; ";
        }else{
            $sql = "SELECT * FROM `pay_balance` WHERE `uuid` = :uuid ; ";
        }
        $re = $DB->prepare($sql);
        $re->execute($data);
        return $re->fetch();
    }

    public function balance_add($data = array()){
        global $DB;
        if(empty($data)) return false;

    }

    public function balance_reduce($data = array()){
        global $DB;
        if(empty($data)) return false;
    }
}
