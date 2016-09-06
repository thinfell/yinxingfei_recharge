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

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$navtitle = lang('plugin/yinxingfei_recharge', 'lang01');

loadcache('yinxingfei_recharge');
$extcredits = $_G['cache']['yinxingfei_recharge'];
$extcredits_list = $_G['setting']['extcredits'];
$set = $_G['cache']['plugin']['yinxingfei_recharge'];

if(!$set['signtype'] &&  !$set['alipay_open'] && !$set['weixin_open']){
    showmessage(lang('plugin/yinxingfei_recharge', 'lang02'));
}

if(submitcheck('snpSubmit', 1)) {
	$snpExtcredits = intval($_POST['snpExtcredits']);
    $backUrl = 'plugin.php?id=yinxingfei_recharge:index';
	if($snpExtcredits < 1 ){
		showmessage(lang('plugin/yinxingfei_recharge', 'lang03'), $backUrl);
	}
	if($set['type'] == 1){
		$snpNum = intval($_POST['snpNum']);
        $snpLeast = $extcredits[$snpExtcredits]['least'];
        $snpMost = $extcredits[$snpExtcredits]['most'];
        $snpRatio = $extcredits[$snpExtcredits]['ratio'];

        $creditTitle = $extcredits_list[$snpExtcredits]['title'];

        if($snpNum < $snpLeast){
			showmessage(lang('plugin/yinxingfei_recharge', 'lang04').$snpLeast.$creditTitle, $backUrl);
		}
		if($snpNum > $snpMost){
			showmessage(lang('plugin/yinxingfei_recharge', 'lang05').$snpMost.$creditTitle, $backUrl);
		}
		$totalFee = $snpNum / $snpRatio;
		if($totalFee < 0.01){
			$totalFee = 0.01;
		}
		$totalFee = number_format($totalFee, 2, '.', '');
		$fee = $totalFee*100;
		$snpFee = $totalFee;
		$subject = lang('plugin/yinxingfei_recharge', 'lang06').$_G['username'].lang('plugin/yinxingfei_recharge', 'lang01').'-'.$snpNum.$creditTitle;
	}else{
		$snpFee = intval($_POST['snpFee']);
		if($snpFee < $snpLeast){
			showmessage(lang('plugin/yinxingfei_recharge', 'lang04').$snpLeast.lang('plugin/yinxingfei_recharge', 'lang09'), $backUrl);
		}
		if($snpFee > $snpMost){
			showmessage(lang('plugin/yinxingfei_recharge', 'lang05').$snpMost.lang('plugin/yinxingfei_recharge', 'lang09'), $backUrl);
		}
		$fee = $snpFee*100;
		$snpNum = $snpFee*$snpRatio;
		$subject = lang('plugin/yinxingfei_recharge', 'lang06').$_G['username'].lang('plugin/yinxingfei_recharge', 'lang01').'-'.$snpFee*$snpRatio.$creditTitle;
	}
	$optional = array(
		'type' => $set['type'],
		'snpExtcredits' => $snpExtcredits,
		'snpFee' => $snpFee,
		'snpNum' => $snpNum,
		'ratio' => $snpRatio,
		'least' => $snpLeast,
		'most' => $snpMost,
		'fee' => $fee,
		'subject' => $subject,
    );

	//创建订单
	$id = date("Ymd",time()).microtime_float();
	
	//写入数据库
	$dbpost = array(
		'id' => $id,
		'uid' => $_G['uid'],
		'start_time' => $_G['timestamp'],
		'finish_time' => '0',
		'state' => 1,
		'optional' => serialize($optional),
	);
	if(DB::insert('a_yinxingfei_recharge_order', $dbpost)){
    //if(1){
		$array = array(
			'code' => 200,
			'data' => array(
				'actionUrl' => $_G['siteurl'].'plugin.php?id=yinxingfei_recharge:operation',
				'subject' => $subject,
				'out_trade_no' => $id,
				'fee' => ''.$fee.'',
				'optional' => json_encode($optional),
            ),
        );
		echo json_encode($array);
		exit();
	}else{
		$array = array(
			'code' => 0,
			'data' => lang('plugin/yinxingfei_recharge', 'lang08'),
        );
		echo json_encode($array);
		exit();
	}
}else{
    $li_html = '';
    $extcreditsCount = 0;
    $onlyone = '';
    $snplang09 = lang('plugin/yinxingfei_recharge', 'lang09');
    for($i = 1; $i <= 8; $i++) {
        if($extcredits[$i]['open']){
            $li_html .= "<li extcredits-value=\"{$i}\" ratio-value=\"{$extcredits[$i]['ratio']}\" least-value=\"{$extcredits[$i]['least']}\" most-value=\"{$extcredits[$i]['most']}\" title-value=\"{$extcredits_list[$i]['title']}\">{$extcredits[$i]['ratio']}{$extcredits_list[$i]['title']} / {$snplang09}</li>";
            $extcreditsCount++;
            $onlyone = $i;
        }
    }
    include DISCUZ_ROOT."source/plugin/yinxingfei_recharge/template/index.php";
}

function microtime_float(){
    list($msec, $sec) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
}
?>