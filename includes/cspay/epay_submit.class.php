<?php
/* *
 * 类名：EpaySubmit
 * 功能：支付接口请求提交类
 * 详细：构造支付接口表单HTML文本，获取远程HTTP数据
 */

class AlipaySubmit {

	var $alipay_config;

	function __construct($alipay_config){
		$this->alipay_config = $alipay_config;
		//$this->alipay_gateway_new = $this->alipay_config['apiurl'].'submit.php?';
		$this->alipay_gateway_new = $this->alipay_config['apiurl'];
		$this->url_outmoneyapiurl = $this->alipay_config['outmoneyapiurl'];
		$this->url_outmoneystatusapiurl = $this->alipay_config['outmoneystatusapiurl'];
		$this->url_balanceRequesturl = $this->alipay_config['balanceRequesturl'];
	}
    function AlipaySubmit($alipay_config) {
    	$this->__construct($alipay_config);
    }
	
	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	function buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);
		
		$mysign = md5SignCS($prestr, $this->alipay_config['key']);

		return $mysign;
	}
	
	function buildRequestMysign222($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);
		
		$mysign = md5SignCSonly($prestr, $this->alipay_config['key']);

		return $mysign;
	}
	
	

	/**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
	function buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilterCS($para_temp);

		//对待签名参数数组排序
		$para_sort = argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
		//$mysign222 = $this->buildRequestMysign222($para_sort);
		
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		//$para_sort['signstr'] = $mysign222;
		//$para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));
		
		return $para_sort;
	}
	
	/**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
	function buildRequestParabacksign($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilterCS($para_temp);

		//对待签名参数数组排序
		$para_sort = argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);

		return $mysign;
	}

	function buildRequestParabackpara_sort($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilterCS($para_temp);

		//对待签名参数数组排序prestr
		$para_sort = argSort($para_filter);

		$arg  = "";
		while (list ($key, $val) = each ($para_sort)) {
			$arg.=$key."：".$val." ";
		}
	
		return $arg;
	}	
	

	/**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
	function buildRequestParaToString($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		$request_data = createLinkstringUrlencode($para);
		
		return $request_data;
	}
	
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
	 
	
/**
 * curl 函数
 * @param string $url 请求的地址
 * @param string $type POST/GET/post/get
 * @param array $data 要传输的数据
 * @param string $err_msg 可选的错误信息（引用传递）
 * @param int $timeout 超时时间
 * @param array 证书信息
 * @author 勾国印
 */
function GoCurl($url, $type, $data = false, &$err_msg = null, $timeout = 20, $cert_info = array())
{
    $type = strtoupper($type);
    if ($type == 'GET' && is_array($data)) {
        $data = http_build_query($data);
    }

    $option = array();

    if ( $type == 'POST' ) {
        $option[CURLOPT_POST] = 1;
    }
    if ($data) {
        if ($type == 'POST') {
            $option[CURLOPT_POSTFIELDS] = $data;
        } elseif ($type == 'GET') {
            $url = strpos($url, '?') !== false ? $url.'&'.$data :  $url.'?'.$data;
        }
    }

    $option[CURLOPT_URL]            = $url;
    $option[CURLOPT_FOLLOWLOCATION] = TRUE;
    $option[CURLOPT_MAXREDIRS]      = 4;
    $option[CURLOPT_RETURNTRANSFER] = TRUE;
    $option[CURLOPT_TIMEOUT]        = $timeout;

    //设置证书信息
    if(!empty($cert_info) && !empty($cert_info['cert_file'])) {
        $option[CURLOPT_SSLCERT]       = $cert_info['cert_file'];
        $option[CURLOPT_SSLCERTPASSWD] = $cert_info['cert_pass'];
        $option[CURLOPT_SSLCERTTYPE]   = $cert_info['cert_type'];
    }

    //设置CA
    if(!empty($cert_info['ca_file'])) {
        // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
        $option[CURLOPT_SSL_VERIFYPEER] = 1;
        $option[CURLOPT_CAINFO] = $cert_info['ca_file'];
    } else {
        // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
        $option[CURLOPT_SSL_VERIFYPEER] = 0;
    }

    $ch = curl_init();
    curl_setopt_array($ch, $option);
    $response = curl_exec($ch);
    $curl_no  = curl_errno($ch);
    $curl_err = curl_error($ch);
    curl_close($ch);

    // error_log
    if($curl_no > 0) {
        if($err_msg !== null) {
            $err_msg = '('.$curl_no.')'.$curl_err;
        }
    }
    return $response;
}

function curl_post($url, array $params = array(), $timeout)
{
    $ch = curl_init();//初始化
    curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    return ($data);
}

	function buildRequestOutMoneyStatusCurl($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		$url   = $this->url_outmoneystatusapiurl;
		$data =	$para;
		
		
		$json = $this->curl_post($url, $data,20);

		$array = json_decode($json, true);

		
		
		
		return $array;
		
		//return $data;
	}
	
	function buildRequestbalanceRequestCurl($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		$url   = $this->url_balanceRequesturl;
		$data =	$para;
		
		
		$json = $this->curl_post($url, $data,20);

		$array = json_decode($json, true);

		
		
		
		return $array;
		
		//return $data;
	}
	

	function buildRequestOutMoneyCurl($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		$url   = $this->url_outmoneyapiurl;
		$data =	$para;
		
		//return $data;
		
		$json = $this->curl_post($url, $data,20);

		$array = json_decode($json, true);

		
		
		
		return $array;
		
		//return $data;
	}

	function buildRequestCurl($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		$url   = $this->alipay_gateway_new;
		$data =	$para;
		$json = $this->curl_post($url, $data,20);

		$array = json_decode($json, true);

		
		
		
		return $array;
		//return $data;
	}
	
	 
	function buildRequestForm($para_temp, $method, $button_name) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."' method='post'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

		//submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
		
		$sHtml = $sHtml."<script>//document.forms['alipaysubmit'].submit();</script>";
		
		return $sHtml;
	}
}
?>