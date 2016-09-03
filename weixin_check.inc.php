<?php

/**
 *      This file is part of SuiNiPai.
 *      (author) thinfell <thinfell@qq.com>
 *		[SuiNiPai] Copyright (c) 2016 Qurui Inc. Code released under the MIT License.
 *      www.suinipai.com
 */

$timestamp = $_POST['timestamp'];
$orderid = $_POST['orderid'];
$sign = $_POST['sign'];

if(md5($orderid . $timestamp) != $sign) {// 签名不正确
    $return = 0;
}

$data = DB::fetch_first("SELECT * FROM ".DB::table('a_yinxingfei_recharge_order')." WHERE id = '".$orderid."'");
if($data['state'] == 2){
    $return = 1;
}else{
    $return = 0;
}

echo $return;
exit();