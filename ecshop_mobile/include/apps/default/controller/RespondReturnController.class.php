<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：RespondController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 支付应答控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class RespondReturnController extends CommonController
{

    private $data;

    public function __construct()
    {
        parent::__construct();
        // 获取参数
	}

    // 发送
    public function index()
    {
	include_once(ADDONS_PATH.'payment/alipay_wap.php');
	$payobj = new alipay_wap();

	$ret = (@$payobj->callback());
	if($ret == 0) $msg = L('pay_success');
	if($ret == 1) $msg = L('pay_fail');

                 
	//显示页面
        $this->assign('message', $msg);
        $this->assign('shop_url', __URL__);
        $this->display('respond.dwt');
    }
}
