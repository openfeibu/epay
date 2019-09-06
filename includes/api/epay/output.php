<?php
namespace epay;
class output{
    public static function output($data = array(),$json = false,$exit = false){
        if($json){
            //echo json_encode($data,JSON_UNESCAPED_UNICODE);
            echo json_encode($data,320);
        }else{
            if(isset($data['msg'])){
                echo $data['msg'];
            }else{
                //echo json_encode($data,JSON_UNESCAPED_UNICODE);
                echo json_encode($data,320);
            }
        }
        if($exit){
            exit();
        }
    }
}
