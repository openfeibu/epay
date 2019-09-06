<?php
namespace output;
function output($data = array(),$json = false,$exit = false){
    if($json){
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }else{
        if(isset($data['msg'])){
            echo $data['msg'];
        }else{
            echo json_encode($data,JSON_UNESCAPED_UNICODE);
        }
    }
    if($exit){
        exit();
    }
}
