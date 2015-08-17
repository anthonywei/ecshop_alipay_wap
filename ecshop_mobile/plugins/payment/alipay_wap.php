<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：alipay_wap.php
 * ----------------------------------------------------------------------------
 * 功能描述：手机支付宝支付插件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

header("Content-type:text/html;charset=utf-8");
require_once("lib/alipay_submit.class.php");

//写日志--------------------add by anthonywei-----------//
function log_run($module, $content)
{
	$fp = fopen("/tmp/".$module.".".date("Y-m-d").".log", "a");

	$date = "[".date('Y-m-d H:i:s',time())."]";     //
	$type = " [RUN] ";                              //
	$content = $date . $type . $content;

	fwrite($fp, $content."\n");
	fclose($fp);
}


$payment_lang = ROOT_PATH . 'plugins/payment/language/' . C('lang') . '/' . basename(__FILE__);

if (file_exists($payment_lang)) {
    include_once ($payment_lang);
    L($_LANG);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;
    /* 代码 */
    $modules[$i]['code'] = basename(__FILE__, '.php');
    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'alipay_wap_desc';
    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';
    /* 是否支持在线支付 */
    $modules[$i]['is_online'] = '1';
    /* 作者 */
    $modules[$i]['author'] = 'ECTOUCH TEAM';
    /* 网址 */
    $modules[$i]['website'] = 'http://www.ectouch.cn';
    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';
    /* 配置信息 */
    $modules[$i]['config'] = array(
        array(
            'name' => 'alipay_account',
            'type' => 'text',
            'value' => ''
        ),
        array(
            'name' => 'alipay_key',
            'type' => 'text',
            'value' => ''
        ),
        array(
            'name' => 'alipay_partner',
            'type' => 'text',
            'value' => ''
        ),
        array(
            'name' => 'relate_pay',
            'type' => 'select',
            'value' => ''
        )
    );
    
    return;
}

/**
 * 支付插件类
 */
class alipay_wap
{
    public $alipay_config		= array('partner' => '你自己的商家合作ID',
						'seller_id' => '你自己的商家合作ID',
						'private_key_path' => 'key/rsa_private_key.pem',
						'ali_public_key_path' => 'key/alipay_public_key.pem',
						'sign_type' => 'RSA',
						'input_charset' => 'utf-8',
						'transport' => 'http');

//	$alipay_config['cacert']    = getcwd().'\\cacert.pem';

    /**
     * 生成支付代码
     *
     * @param array $order
     *            订单信息
     * @param array $payment
     *            支付方式信息
     */
    function get_code($order, $payment)
    {
        if (! defined('EC_CHARSET')) {
            $charset = 'utf-8';
        } else {
            $charset = EC_CHARSET;
        }
 	    //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径，注意这个路径不能加参数的，不然校验会出错
        $notify_url = "http://网站域名/mobile/respondnotify.php";
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $return_url = "http://网站域名/mobile/respondreturn.php";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号，这里可以自己修改，echsop2.73是用id + 'O' + 数据库记录存的
        $out_trade_no = $order['order_sn'] . 'O' . $order['log_id'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = $order['order_sn'];
        //必填

        //付款金额
        $total_fee = $order['order_amount'];
        //必填

        //商品展示地址
        $show_url = "http://产品的URL地址，没有就设置为网站首页";
        //必填，需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html


	/************************************************************/

	//构造要请求的参数数组，无需改动
	$parameter = array(
			"service" => "alipay.wap.create.direct.pay.by.user",
			"partner" => trim($this->alipay_config['partner']),
			"seller_id" => trim($this->alipay_config['seller_id']),
			"payment_type"	=> $payment_type,
			"notify_url"	=> $notify_url,
			"return_url"	=> $return_url,
			"out_trade_no"	=> $out_trade_no,
			"subject"	=> $subject,
			"total_fee"	=> $total_fee,
			"show_url"	=> $show_url,
			"body"	=> $body,
			"it_b_pay"	=> $it_b_pay,
			"extern_token"	=> $extern_token,
			"_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
	);

	log_run("alipay_wap", serialize($parameter));

	//建立请求
	log_run("alipay_wap", serialize($this->alipay_config));
	$alipaySubmit = new AlipaySubmit($this->alipay_config);
	$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "去支付");
	return $html_text;
    }

    /**
     * 手机支付宝同步响应操作
     * 
     * @return boolean
     */
    public function callback()
    {
	require_once('lib/alipay_notify.class.php');
	log_run("alipay_wap", "callback get:".$_SERVER["REQUEST_URI"]);
	$alipayNotify = new AlipayNotify($this->alipay_config);
	$verify_result = $alipayNotify->verifyReturn();

	if($verify_result) {//验证成功

		$out_trade_no = explode('O', $_GET['out_trade_no']);
		$log_id = $out_trade_no[1];
		$trade_no = $_GET['trade_no'];

		
		$trade_status = $_GET['trade_status'];


	    if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {

		log_run("alipay_wap", "set order paied " . $log_id);
		model('Payment')->order_paid($log_id, 2);
		return 0;
	    }
	    else {

	        log_run("alipay_wap", "callback trade status not ok and is " .$_GET['trade_status']);
		return 1;
	    }
	}
	else {

	    log_run("alipay_wap", "callback verify failed");
	    return 1;
	}
		
    }

    /**
     * 手机支付宝异步通知
     * 
     * @return string
     */
    public function notify()
    {
	require_once('lib/alipay_notify.class.php');
	log_run("alipay_wap", "notify :".serialize($_POST));
	$lipayNotify = new AlipayNotify($this->alipay_config);
	$verify_result = $alipayNotify->verifyNotify();

	if($verify_result) {//验证成功
	
		$out_trade_no = $_POST['out_trade_no'];

	 	$out_trade_no = explode('O', $data['out_trade_no']);
		$log_id = $out_trade_no[1]; // 订单号log_id

		//支付宝交易号

		$trade_no = $_POST['trade_no'];

		//交易状态
		$trade_status = $_POST['trade_status'];


	    if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
		model('Payment')->order_paid($log_id, 2);
		echo "success";
	    }
	    else
	    {
		log_run("alipay_wap", "notify trade status not ok and is " .$_POST['trade_status']);
	        echo "fail";
	    }

	}
	else {
	    //验证失败
	    log_run("alipay_wap", "notify verify failed");
	    echo "fail";

	}

    }
}
