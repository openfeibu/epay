<?php
namespace epay;
class user
{
    //创建用户
    public static function create($data = array()){
        global $DB2;
        if(!isset($data['uuid']) || !isset($data['agentuuid']) || !isset($data['adminuuid'])){
            $result = array();
            $result['code'] = 0;
            $result['msg'] = "UUID或者agentuuid或者adminuuid未设置。";
            return $result;
        }

        if(strlen($data['uuid']) != 36 || strlen($data['adminuuid']) != 36 || ($data['agentuuid'] != 0 && $data['agentuuid'] != 1 && strlen($data['agentuuid']) != 36 )){
            $result = array();
            $result['code'] = 0;
            $result['msg'] = "UUID或者agentuuid或者adminuuid不正确。";
            return $result;
        }

        $re = $DB2->insert("pay_user",$data);
        if($re){
            $result = array();
            $result['code'] = 1;
            $result['msg'] = "添加商户成功。";
            $result['userid'] = $re;
        }else{
            $result = array();
            $result['code'] = 0;
            $result['msg'] = $re->errorInfo();
        }
        return $result;
    }

    //删除用户
    public static function delete($uuid){
        //删除用户
        if(strlen($uuid) != 36){
            return false;
        }
        return;
    }

    //更新用户
    public static function update($data = array(),$conds = array()){
        global $DB;
        $data = array_filter($data,function($value){
            return null !== $value;
        });
        $conds = array_filter($conds,function($value){
            return null !== $value;
        });
        $query = "UPDATE `pay_user` SET ";
        $values = array();
        foreach($data as $name => $value){
            $query .= " `{$name}` = :{$name},";
            $values[$name] = $value;
        }

        $where = " WHERE ";
        $i = 1;
        foreach($conds as $name => $value){
            if($i == 1){
                $where .= " `{$name}` = :{$name}";
            }else{
                $where .= " AND `{$name}` = :{$name}";
            }
            $values[$name] = $value;
            $i++;
        }
        $query = substr($query, 0, -1); // remove last , and add a ;
        $query .= $where;
        $re = $DB->prepare($query);
        $re->execute($values);
        return $re;
    }

    public static function find_user($uuid = 0){
        global $DB;
        //兼容函数，前期使用
        return self::find($uuid);
    }

    //账户相关信息
    public static function find($uuid = 0){
        global $DB;
        //用户ID不能为0或1
        if($uuid == 0 || $uuid == 1) return false;
        if(strlen($uuid) == 36){
            $sql = "SELECT * FROM `pay_user` WHERE `uuid` = :uuid LIMIT 1;";
        }else{
            $sql = "SELECT * FROM `pay_user` WHERE `id` = :uuid LIMIT 1;";
        }
        $re = $DB->prepare($sql);
        $re->execute(['uuid' => $uuid]);
        $result = $re->fetch();
        if($result){
            return $result;
        }else{
            return false;
        }
    }


    //查找助手相关信息
    static function find_user_others($userid = 0){
        global $DB;
        if($userid == 0) return false;
        $sql = "SELECT * FROM `pay_user_others` WHERE `id` = '{$userid}' LIMIT 1;";
        $result = $DB->query($sql)->fetch();
        if($result){
            return $result;
        }else{
            return false;
        }
    }
}

