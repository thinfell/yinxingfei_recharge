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
	1 => '<font color=red>'.lang('plugin/yinxingfei_recharge', 'lang18').'</font>',
	2 => '<font color=green>'.lang('plugin/yinxingfei_recharge', 'lang19').'</font>',
	3 => '<font color=gray>'.lang('plugin/yinxingfei_recharge', 'lang31').'</font>',
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
	cpmsg(lang('plugin/yinxingfei_recharge', 'lang20'), 'action=plugins&operation=config&identifier=yinxingfei_recharge&pmod=order', 'succeed');
}

showformheader('plugins&operation=config&identifier=yinxingfei_recharge&pmod=order');
showtableheader(lang('plugin/yinxingfei_recharge', 'lang21'),'','','99');
$limit = 50;
$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('a_yinxingfei_recharge_order')." ");
$page = max(1, intval($_GET['page']));
$start_limit = ($page - 1) * $limit;
$url = "admin.php?action=plugins&operation=config&identifier=yinxingfei_recharge&pmod=order";
$multipage = multi($num, $limit, $page, $url);
$query = DB::query("SELECT * FROM ".DB::table('a_yinxingfei_recharge_order')." ORDER BY start_time DESC LIMIT ".$start_limit." ,".$limit."");
showsubtitle(array('',lang('plugin/yinxingfei_recharge', 'lang22'),lang('plugin/yinxingfei_recharge', 'lang06'),lang('plugin/yinxingfei_recharge', 'lang23'),lang('plugin/yinxingfei_recharge', 'lang24'),lang('plugin/yinxingfei_recharge', 'lang25'),lang('plugin/yinxingfei_recharge', 'lang26'),lang('plugin/yinxingfei_recharge', 'lang27'),lang('plugin/yinxingfei_recharge', 'lang28')));
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
		$result['state'] == 1 ? '<a href="admin.php?action=plugins&operation=config&identifier=yinxingfei_recharge&pmod=order&supplement=yes&ordid='.$result['id'].'">'.lang('plugin/yinxingfei_recharge', 'lang29').'</a>':'-'
	));
}
showtablerow('', array('colspan="99"'), array($multipage));
showtablefooter();
showformfooter();

function uid2name ($id){
	return DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid = '{$id}' ");
}
?>