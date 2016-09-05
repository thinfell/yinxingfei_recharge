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
$navtitle = lang('plugin/yinxingfei_recharge', 'lang19');
$orderid = $_GET['orderid'];
$data = DB::fetch_first("SELECT * FROM ".DB::table('a_yinxingfei_recharge_order')." WHERE id = '".$orderid."'");
$optional = unserialize($data['optional']);
if($data['uid'] != $_G['uid']){
	exit('uid error');
}elseif($data['state'] != 2){
	exit('state error');
}elseif($optional['fee'] != $_GET['fee']){
	exit('fee error');
}

$isMb = checkmobile();

include template('common/header');
?>
<?php if($isMb == 2){?>
<header class="header">
    <div class="nav">
        <a href="javascript:;" onclick="history.go(-1)" class="z"><img src="<?php echo STATICURL;?>image/mobile/images/icon_back.png" /></a>
		<span><?php echo lang('plugin/yinxingfei_recharge', 'lang19');?></span>
    </div>
</header>
<?php };?>

<div class="thinfellpay-box-main" style="padding-top: 60px;">
    <div class="thinfellpay-box-main-inner">
        <div class="thinfellpay-box-note-main" style="font-size:14px;">
            <div class="thinfellpay-box-note">
                <div class="thinfellpay-box-result">
					<img src="http://obml0xkom.bkt.clouddn.com/success.png" class="thinfellpay-box-result-success" />
					<div style="padding: 20px 0px 10px 0px;"><?php echo lang('plugin/yinxingfei_recharge', 'lang19');?></div>
					<div style="font-size:30px;height:36px;line-height:36px;"><?php echo number_format(($_GET['fee']/100), 2, '.', '');?><?php echo lang('plugin/yinxingfei_recharge', 'lang09');?></div>
					<div style="font-size: 14px;color: #888;padding-top:6px;"><?php echo lang('plugin/yinxingfei_recharge', 'lang30');?><?php echo $_GET['orderid'];?></div>
				</div>
            </div>
        </div>
        <div>
            <a href="home.php?mod=spacecp&ac=credit&showcredit=1" class="thinfellpay-box-pay-btn"><?php echo lang('plugin/yinxingfei_recharge', 'lang32');?></a>
		</div>
        <div class="thinfellpay-box-pay-notice-main">
            <a href="plugin.php?id=yinxingfei_recharge:index" class="thinfellpay-box-pay-notice"><?php echo lang('plugin/yinxingfei_recharge', 'lang33');?></a>
		</div>
        <div>
            <a href="./" class="thinfellpay-box-pay-cancel"><?php echo lang('plugin/yinxingfei_recharge', 'lang34');?></a>
		</div>
    </div>
</div>
<script type="text/javascript" src="http://obml0xkom.bkt.clouddn.com/jquery.min.js"></script>
<script type="text/javascript" src="http://server.suinipai.com/v1/returnscripts.php?appId=<?php echo $_G['cache']['plugin']['yinxingfei_recharge']['partner'];?>"></script>

<?php

include template('common/footer');

?>