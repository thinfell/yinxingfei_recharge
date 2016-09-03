<?php


if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('settingsubmit')) {
	require_once libfile('function/cache');
	loadcache('yinxingfei_recharge');
	loadcache('plugin');
    loadcache('setting');
    $set = $_G['cache']['plugin']['yinxingfei_recharge'];
	$extcredits = $_G['setting']['extcredits'];
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=yinxingfei_recharge&pmod=extcredits', 'enctype');
	showtableheader('充值比例', 'fixpadding');
	$title = $creditsetting = array();
	for($i = 1; $i <= 8; $i++) {
		if($i == 1) {
			$title[] = '<font style="font:12px normal normal">积分名称</font>';
			$creditsetting[0] = '<td class="td23">是否开启充值</td>';
			$creditsetting[1] = '<td class="td23">1元=？积分</td>';
			if($set['type'] == 1){
				$creditsetting[2] = '<td class="td23">单次最少充值<font color=red>(数量)</font></td>';
				$creditsetting[3] = '<td class="td23">单次最多充值<font color=red>(数量)</font></td>';
			}else{
				$creditsetting[2] = '<td class="td23">单次最少充值<font color=red>(元)</font></td>';
				$creditsetting[3] = '<td class="td23">单次最多充值<font color=red>(元)</font></td>';
			}
		}
		
		if($extcredits[$i]['title']){
			$title[] = $extcredits[$i]['title']."</br>extcredits$i";
			$creditsetting[0] .= "<td class=\"td32\"><input class=\"checkbox\" type=\"checkbox\" name=\"extcredits[$i][open]\" value=\"1\" ".($_G['cache']['yinxingfei_recharge'][$i]['open'] ? 'checked' : '')."></td>";
			$creditsetting[1] .= "<td class=\"td32\"><input type=\"text\" class=\"txt\" name=\"extcredits[$i][ratio]\" value=\"{$_G['cache']['yinxingfei_recharge'][$i]['ratio']}\"></td>";
			$creditsetting[2] .= "<td class=\"td32\"><input type=\"text\" class=\"txt\" name=\"extcredits[$i][least]\" value=\"{$_G['cache']['yinxingfei_recharge'][$i]['least']}\"></td>";
			$creditsetting[3] .= "<td class=\"td32\"><input type=\"text\" class=\"txt\" name=\"extcredits[$i][most]\" value=\"{$_G['cache']['yinxingfei_recharge'][$i]['most']}\"></td>";
		}else{
			$title[] = "该积分未启用</br>extcredits$i";
			$creditsetting[0] .= "<td class=\"td32\">-</td>";
			$creditsetting[1] .= "<td class=\"td32\">-</td>";
			$creditsetting[2] .= "<td class=\"td32\">-</td>";
			$creditsetting[3] .= "<td class=\"td32\">-</td>";
		}
	}
	showsubtitle($title, 'header sml');
	echo '<tr>'.implode('</tr><tr>', $creditsetting).'</tr>';
	showsubmit('settingsubmit');
	showtablefooter();
	showformfooter();
}else{
	save_syscache('yinxingfei_recharge', $_POST['extcredits']);
	cpmsg('设置保存成功', 'action=plugins&operation=config&do='.$pluginid.'&identifier=yinxingfei_recharge&pmod=extcredits', 'succeed');
}
?>