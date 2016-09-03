<?php

/**
 *      This file is part of SuiNiPai.
 *      (author) thinfell <thinfell@qq.com>
 *		[SuiNiPai] Copyright (c) 2016 Qurui Inc. Code released under the MIT License.
 *      www.suinipai.com
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
$stater = array(
	1 => '<font color=red>未支付</font>',
	2 => '<font color=green>支付成功</font>',
	3 => '<font color=gray>取消</font>',
);

if($_GET['supplement'] == 'yes'){
	$orderid = $_GET['ordid'];
	$data = DB::fetch_first("SELECT * FROM ".DB::table('a_yinxingfei_recharge_order')." WHERE id = '".$orderid."'");
	$optional = unserialize($data['optional']);
    loadcache('setting');
    $extcredits = $_G['setting']['extcredits'];
	
	if($data['state'] == 1){
		if($optional['type'] == 1){
			$snpNum = $optional['snpNum'];
		}else{
			$snpNum = $optional['snpFee'] * $optional['ratio'];
		}
		updatemembercount($data['uid'], array($optional['snpExtcredits'] => $snpNum), 1, 'AFD', $data['uid']);
		updatecreditbyaction('yinxingfei_recharge', $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0);
		DB::query("UPDATE ".DB::table('a_yinxingfei_recharge_order')." SET state = 2, finish_time = '".$_G['timestamp']."' WHERE id= '".$orderid."'", 'UNBUFFERED');
		notification_add($data['uid'], 'credit', 'addfunds', array(
			'orderid' => $orderid,
			'price' => $optional['fee'] / 100,
			'value' => $extcredits[$optional['snpExtcredits']]['title'].' '.$snpNum.' '.$extcredits[$optional['snpExtcredits']]['unit']
		), 1);
	}
	cpmsg('补单成功', 'action=plugins&operation=config&identifier=yinxingfei_recharge&pmod=order', 'succeed');
}

showformheader('plugins&operation=config&identifier=yinxingfei_recharge&pmod=order');
showtableheader('积分充值记录','','','99');
$limit = 50;
$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('a_yinxingfei_recharge_order')." ");
$page = max(1, intval($_GET['page']));
$start_limit = ($page - 1) * $limit;
$url = "admin.php?action=plugins&operation=config&identifier=yinxingfei_recharge&pmod=order";
$multipage = multi($num, $limit, $page, $url);
$query = DB::query("SELECT * FROM ".DB::table('a_yinxingfei_recharge_order')." ORDER BY start_time DESC LIMIT ".$start_limit." ,".$limit."");
showsubtitle(array('','订单号','用户名','订单详情','支付金额','提交时间','完结时间','状态','操作'));
while ($result = DB::fetch($query)){
	$optional = unserialize($result['optional']);
	showtablerow('','', array(
		'',
		$result['id'],
		'<a href="home.php?mod=space&uid='.$result['uid'].'&do=profile" target="_blank">'.uid2name($result['uid']).'</a>',
		$optional['subject'],
		($optional['fee']/100).'元',
		date("Y-m-d H:i:s",$result['start_time']),
		$result['finish_time'] ? date("Y-m-d H:i:s",$result['finish_time']) : '-',
		$stater[$result['state']],
		$result['state'] == 1 ? '<a href="admin.php?action=plugins&operation=config&identifier=yinxingfei_recharge&pmod=order&supplement=yes&ordid='.$result['id'].'">补单</a>':'-'
	));
}
showtablerow('', array('colspan="99"'), array($multipage));
showtablefooter();
showformfooter();

function uid2name ($id){
	return DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid = '{$id}' ");
}
?>