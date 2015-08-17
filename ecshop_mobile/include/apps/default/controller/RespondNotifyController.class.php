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

class RespondController extends CommonController
{

    private $data;

    public function __construct()
    {
        parent::__construct();
	}

    // 发送
    public function index()
    {
	include_once(ADDONS_PATH.'payment/alipay_wap.php');
	$payobj = new alipay_wap();

	$ret = (@$payobj->notify());
    }
}
