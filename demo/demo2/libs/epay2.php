<?php
namespace epay2;

require_once __DIR__.DIRECTORY_SEPARATOR."function.php";
class epay2{
    //网关地址
    public $gateway_url;
    public $order_query_url;
    public $pid;
    public $key;

    public $post = array();

    public function __construct($config){
        $this->pid = $config['pid'];
        $this->key = $config['key'];
        $this->gateway_url = $config['gateway_url'];
        $this->order_query_url = $config['order_query_url'];
        //$this->currency = "cny";
    }

    public function epay($config){
        $this->__construct($config);
    }

    public function submit(){
        $data = $this->post;
        $data['pid'] = $this->pid;
        $data['sign'] = \epay2\getSign($data,$this->key);
        $data['sign'] = strtolower($data['sign']);
        $result = array();
        $result['url'] = $this->gateway_url;
        $result['data'] = $data;
        return $result;
    }

    public function query(){
        $data = array();
        $data['out_trade_no'] = $this->out_trade_no;
        $data['act'] = "query";
        $data['sign'] = \epay2\getSign($data,$this->key);

        $result = array();
        $result['url'] = $this->order_query_url;
        $result['data'] = $data;
        return $result;
    }
}
