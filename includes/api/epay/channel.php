<?php
namespace epay;

//通道管理类

class channel{
    //增加通道
    public static function create($channel = array()){

    }

    //查询通道
    public static function find($id){
        global $DB;
        $data["id"] = $id;
        $sql = "SELECT * FROM `pay_channel` WHERE `id` = :id; ";
        try{
            $re = $DB->prepare($sql);
            $re->execute($data);
            $row = $re->fetch();
            if($row){
                return $row;
            }else{
                return false;
            }
        }catch(\Exception $e){
            $result['code'] = 0;
            $result['msg'] = "查询失败！{$e->errorInfo[2]}";
            return false;
        }
    }
}