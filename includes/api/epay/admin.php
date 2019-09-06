<?php
namespace epay;
class admin
{
    //创建管理员
    public static function create($data = array()){
        global $DB2;
        if(!isset($data['uuid']) || !isset($data['admin_user']) || !isset($data['admin_pwd'])){
            $result = array();
            $result['code'] = 0;
            $result['msg'] = "UUID或者admin_user或者admin_pwd未设置。";
            return $result;
        }

        if(strlen($data['uuid']) != 36 || strlen($data['admin_pwd']) < 10 || $data['admin_user'] == 'root'){
            $result = array();
            $result['code'] = 0;
            $result['msg'] = "UUID或者密码长度（至少10位）或用户名不正确。";
            return $result;
        }

        $re = $DB2->insert("pay_admin",$data);
        if($re){
            $result = array();
            $result['code'] = 1;
            $result['msg'] = "添加管理员成功。";
            $result['userid'] = $re;
        }else{
            $result = array();
            $result['code'] = 0;
            $result['msg'] = $re->errorInfo();
        }
        return $result;
    }

    //删除管理员
    public static function delete($uuid){
        //删除管理员
        if(strlen($uuid) != 36){
            return false;
        }
        return;
    }

    //更新管理员
    public static function update($data = array(),$conds = array()){
        global $DB;
        $data = array_filter($data,function($value){
            return null !== $value;
        });
        $conds = array_filter($conds,function($value){
            return null !== $value;
        });
        $query = "UPDATE `pay_admin` SET ";
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
    //账户相关信息
    public static function find($uuid = 0){
        global $DB;
        //if($uuid == 0) return false;
        if(strlen($uuid) == 36){
            $sql = "SELECT * FROM `pay_admin` WHERE `uuid` = :uuid LIMIT 1;";
        }else{
            $sql = "SELECT * FROM `pay_admin` WHERE `id` = :uuid LIMIT 1;";
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


}

