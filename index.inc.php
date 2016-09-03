<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$navtitle = '积分充值';

loadcache('yinxingfei_recharge');
$extcredits = $_G['cache']['yinxingfei_recharge'];
$extcredits_list = $_G['setting']['extcredits'];
$set = $_G['cache']['plugin']['yinxingfei_recharge'];

if(!$set['signtype'] &&  !$set['alipay_open'] && !$set['weixin_open']){
    showmessage('管理员未开启任何支付模式，请联系管理员!');
}

if(submitcheck('snpSubmit', 1)) {
	$snpExtcredits = intval($_POST['snpExtcredits']);
    $backUrl = 'plugin.php?id=yinxingfei_recharge:index';
	if($snpExtcredits < 1 ){
		showmessage('选择充值积分', $backUrl);
	}
	if($set['type'] == 1){
		$snpNum = intval($_POST['snpNum']);
        $snpLeast = $extcredits[$snpExtcredits]['least'];
        $snpMost = $extcredits[$snpExtcredits]['most'];
        $snpRatio = $extcredits[$snpExtcredits]['ratio'];

        $creditTitle = $extcredits_list[$snpExtcredits]['title'];

        if($snpNum < $snpLeast){
			showmessage('每次最少充值'.$snpLeast.$creditTitle, $backUrl);
		}
		if($snpNum > $snpMost){
			showmessage('每次最多充值'.$snpMost.$creditTitle, $backUrl);
		}
		$totalFee = $snpNum / $snpRatio;
		if($totalFee < 0.01){
			$totalFee = 0.01;
		}
		$totalFee = number_format($totalFee, 2, '.', '');
		$fee = $totalFee*100;
		$snpFee = $totalFee;
		$subject = '用户'.$_G['username'].'积分充值-'.$snpNum.$creditTitle;
	}else{
		$snpFee = intval($_POST['snpFee']);
		if($snpFee < $snpLeast){
			showmessage('每次最少充值'.$snpLeast.'元', $backUrl);
		}
		if($snpFee > $snpMost){
			showmessage('每次最多充值'.$snpMost.'元', $backUrl);
		}
		$fee = $snpFee*100;
		$snpNum = $snpFee*$snpRatio;
		$subject = '用户'.$_G['username'].'积分充值-'.$snpFee*$snpRatio.$creditTitle;
	}
	$optional = [
		'type' => $set['type'],
		'snpExtcredits' => $snpExtcredits,
		'snpFee' => $snpFee,
		'snpNum' => $snpNum,
		'ratio' => $snpRatio,
		'least' => $snpLeast,
		'most' => $snpMost,
		'fee' => $fee,
		'subject' => $subject,
	];

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
		$array = [
			'code' => 200,
			'data' => [
				'actionUrl' => $_G['siteurl'].'plugin.php?id=yinxingfei_recharge:operation',
				'subject' => $subject,
				'out_trade_no' => $id,
				'fee' => ''.$fee.'',
				'optional' => json_encode($optional),
			],
		];
		echo json_encode($array);
		exit();
	}else{
		$array = [
			'code' => 0,
			'data' => '写入数据库失败',
		];
		echo json_encode($array);
		exit();
	}
}else{
    $li_html = '';
    $extcreditsCount = 0;
    $onlyone = '';
    for($i = 1; $i <= 8; $i++) {
        if($extcredits[$i]['open']){
            $li_html .= "<li extcredits-value=\"{$i}\" ratio-value=\"{$extcredits[$i]['ratio']}\" least-value=\"{$extcredits[$i]['least']}\" most-value=\"{$extcredits[$i]['most']}\" title-value=\"{$extcredits_list[$i]['title']}\">{$extcredits[$i]['ratio']}{$extcredits_list[$i]['title']} / 元</li>";
            $extcreditsCount++;
            $onlyone = $i;
        }
    }
    include template('yinxingfei_recharge:index');
}

function microtime_float(){
    list($msec, $sec) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
}
?>