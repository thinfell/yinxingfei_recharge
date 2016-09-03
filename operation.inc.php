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

require_once(DISCUZ_ROOT."source/plugin/yinxingfei_recharge/inc/Recharge.class.php");

global $_G;
$parameter = array(
    "out_trade_no"  => $_POST['out_trade_no'],
    "fee"           => $_POST['fee'],
    "subject"       => $_POST['subject'],
    "paytype"       => $_POST['paytype'],
    "optional"      => $_POST['optional'],
);

$Recharge = new Recharge($parameter);
$result = $Recharge->run();
echo json_encode($result);
exit();

?>