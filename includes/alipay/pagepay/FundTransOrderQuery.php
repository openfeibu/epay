<?php
/**
 * ALIPAY API: alipay.fund.trans.toaccount.transfer request
 *
 * @author QQ 13894877 email jxgame@163.com
 * @ https://docs.open.alipay.com/api_28/alipay.fund.trans.toaccount.transfer
 
 https://docs.open.alipay.com/api_28/alipay.fund.trans.order.query
 
 
 * **引用此类前，先引用SDK类中的 config,AopSdk.php这两个文件。
 使用方法：
  */
 require_once dirname(dirname(__FILE__)).'/config.php';
require_once dirname(__FILE__).'/service/AlipayTradeService.php';

$toUserAccount="465603617@qq.com";
$toUserName="傅胜";
$money="1";
//$out_no=time();
$order_id="20180413110070001502890081322091";
$out_no="456465895645504";

		$aop = new AopClient();
		$aop->gatewayUrl = $config['gatewayUrl'];
		$aop->appId = $config['app_id'];
		$aop->rsaPrivateKey = $config['merchant_private_key'];
		$aop->alipayrsaPublicKey=$config['alipay_public_key'];
		$aop->apiVersion = '1.0';
		$aop->signType = 'RSA2';
		$aop->postCharset='UTF-8';
		$aop->format='json';
		$request = new AlipayFundTransOrderQueryRequest ();
		$param=array(
			'out_biz_no'=>$out_no
		);
		
		//$param=array(
		//	'order_id'=>$order_id
		//);
		$request->setBizContent(json_encode($param,320));
		$result = $aop->execute ($request); 			
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
		echo "成功".$result->$responseNode->status;
		echo "成功".$result->$responseNode->order_id;
		} else {
		echo "失败".$result->$responseNode->sub_msg;
		}
 

class AlipayFundTransToaccountTransferRequest
{
	/** 
	 * 单笔转账到支付宝账户接口
	 **/
	private $bizContent;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;
	private $returnUrl;
    private $needEncrypt=false;

	
	public function setBizContent($bizContent)
	{
		$this->bizContent = $bizContent;
		$this->apiParas["biz_content"] = $bizContent;
	}

	public function getBizContent()
	{
		return $this->bizContent;
	}

	public function getApiMethodName()
	{
		return "alipay.fund.trans.toaccount.transfer";
	}

	public function setNotifyUrl($notifyUrl)
	{
		$this->notifyUrl=$notifyUrl;
	}

	public function getNotifyUrl()
	{
		return $this->notifyUrl;
	}

	public function setReturnUrl($returnUrl)
	{
		$this->returnUrl=$returnUrl;
	}

	public function getReturnUrl()
	{
		return $this->returnUrl;
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getTerminalType()
	{
		return $this->terminalType;
	}

	public function setTerminalType($terminalType)
	{
		$this->terminalType = $terminalType;
	}

	public function getTerminalInfo()
	{
		return $this->terminalInfo;
	}

	public function setTerminalInfo($terminalInfo)
	{
		$this->terminalInfo = $terminalInfo;
	}

	public function getProdCode()
	{
		return $this->prodCode;
	}

	public function setProdCode($prodCode)
	{
		$this->prodCode = $prodCode;
	}

	public function setApiVersion($apiVersion)
	{
		$this->apiVersion=$apiVersion;
	}

	public function getApiVersion()
	{
		return $this->apiVersion;
	}

  public function setNeedEncrypt($needEncrypt)
  {

     $this->needEncrypt=$needEncrypt;

  }

  public function getNeedEncrypt()
  {
    return $this->needEncrypt;
  }

}
