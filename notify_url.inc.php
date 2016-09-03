<?php

/**
 *      This file is part of SuiNiPai.
 *      (author) thinfell <thinfell@qq.com>
 *		[SuiNiPai] Copyright (c) 2016 Qurui Inc. Code released under the MIT License.
 *      www.suinipai.com
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once(DISCUZ_ROOT."source/plugin/yinxingfei_recharge/inc/Notify.class.php");

$Notify = new Notify();
$result = $Notify->run();
echo $result;
exit();

?>