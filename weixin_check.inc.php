<?php
/**
 * Created by PhpStorm.
 * User: thinfell
 * Date: 2016/9/2
 * Time: 23:38
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