<?php

/**
 *      This file is part of SuiNiPai.
 *      (author) thinfell <thinfell@qq.com>
 *		[SuiNiPai] Copyright (c) 2016 Qurui Inc. Code released under the MIT License.
 *      www.suinipai.com
 */

require_once(DISCUZ_ROOT."source/plugin/yinxingfei_recharge/alipay/alipay_notify.class.php");
require_once(DISCUZ_ROOT."source/plugin/yinxingfei_recharge/weixin/Weixin.class.php");

class Notify
{
    public function run()
    {
        //首先判断是数据源
        $judgeResult = $this->judge();
		
        if($judgeResult){
            switch ($judgeResult) {
                case 1 :
                    $result = $this->selfSign_alipay_notify();
                    break;
                case 2 :
                    $result = $this->selfSign_weixin_notify();
                    break;
                case 3 :
                    $this->selfSign_alipay_return();
                    break;
                case 4 :
                    $result = $this->exemptSign_notify();
                    break;
                default :
                    $result = '';
            }
        }else{
            $result = '';
        }
        return $result;
    }

    private function judge()
    {
        //通过来路参数是否含有特定参数 来判断
        if(isset($_POST['optional'])){
            //免签约 异步
            return 4;
        }elseif (isset($_POST['notify_id'])){
            //支付宝 异步
            return 1;
        }elseif (isset($_GET['notify_id'])){
            //支付宝 同步
            return 3;
        }else{
            //微信 异步
            return 2;
        }
    }

    private function selfSign_alipay_notify()
    {
        global $_G;
        //计算得出通知验证结果
        $alipay_config['partner']		= $_G['cache']['plugin']['yinxingfei_recharge']['ec_partner'];
        $alipay_config['key']			= $_G['cache']['plugin']['yinxingfei_recharge']['ec_securitycode'];
        $alipay_config['sign_type']    = strtoupper('MD5');
        $alipay_config['cacert']    = getcwd().'\\..\\alipay\\cacert.pem';
        $alipay_config['transport']    = 'http';

        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) {
            $out_trade_no = $_POST['out_trade_no'];
            if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $data = DB::fetch_first("SELECT * FROM ".DB::table('a_yinxingfei_recharge_order')." WHERE id = '".$out_trade_no."'");
                $optional = unserialize($data['optional']);

                if($data['state'] != 1){
                    return "";
                }
                //验证金额
                if($_POST['total_fee'] * 100 != $optional['fee']){
                    return "";
                }
                if($optional['type'] == 1){
                    $snpNum = $optional['snpNum'];
                }else{
                    $snpNum = $optional['snpFee'] * $optional['ratio'];
                }

                updatemembercount($data['uid'], array($optional['snpExtcredits'] => $snpNum), 1, 'AFD', $data['uid']);
                updatecreditbyaction('yinxingfei_recharge', $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0);
                DB::query("UPDATE ".DB::table('a_yinxingfei_recharge_order')." SET state = 2, finish_time = '".$_G['timestamp']."' WHERE id= '".$out_trade_no."'", 'UNBUFFERED');
                notification_add($data['uid'], 'credit', 'addfunds', array(
                    'orderid' => $out_trade_no,
                    'price' => $optional['fee'] / 100,
                    'value' => $_G['setting']['extcredits'][$optional['snpExtcredits']]['title'].' '.$snpNum.' '.$_G['setting']['extcredits'][$optional['snpExtcredits']]['unit']
                ), 1);

            }
             return "success";
        } else {
            return "";
        }
    }

    private function selfSign_alipay_return()
    {
        global $_G;
        $out_trade_no = $_GET['out_trade_no'];
        $fee =  $_GET['total_fee'] * 100;
        header('Location: '.$_G['siteurl'].'plugin.php?id=yinxingfei_recharge:return_url&fee='.$fee.'&orderid='.$out_trade_no);
    }

    private function selfSign_weixin_notify()
    {
        global $_G;
		
        $Weixin = new Weixin();
        $xml = file_get_contents("php://input");		
        $notify = $Weixin->FromXml($xml);
        $key = $_G['cache']['plugin']['yinxingfei_recharge']['ec_wxpay_key'];

		$myfile = fopen("log.txt", "w") or die("Unable to open file!");
		$txt = json_encode($notify);
		fwrite($myfile, $txt);
		fclose($myfile);
		
        if($notify['sign'] == $Weixin->MakeSign($notify,$key)){
            if($notify['result_code'] == 'SUCCESS') {
                $out_trade_no = $notify['out_trade_no'];
                $data = DB::fetch_first("SELECT * FROM ".DB::table('a_yinxingfei_recharge_order')." WHERE id = '".$out_trade_no."'");
                $optional = unserialize($data['optional']);

                if($data['state'] != 1){
                    return "";
                }
                //验证金额
                if($notify['total_fee'] != $optional['fee']){
                    return "";
                }
                if($optional['type'] == 1){
                    $snpNum = $optional['snpNum'];
                }else{
                    $snpNum = $optional['snpFee'] * $optional['ratio'];
                }

                updatemembercount($data['uid'], array($optional['snpExtcredits'] => $snpNum), 1, 'AFD', $data['uid']);
                updatecreditbyaction('yinxingfei_recharge', $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0);
                DB::query("UPDATE ".DB::table('a_yinxingfei_recharge_order')." SET state = 2, finish_time = '".$_G['timestamp']."' WHERE id= '".$out_trade_no."'", 'UNBUFFERED');
                notification_add($data['uid'], 'credit', 'addfunds', array(
                    'orderid' => $out_trade_no,
                    'price' => $optional['fee'] / 100,
                    'value' => $_G['setting']['extcredits'][$optional['snpExtcredits']]['title'].' '.$snpNum.' '.$_G['setting']['extcredits'][$optional['snpExtcredits']]['unit']
                ), 1);

                return  $Weixin->ToXml([
                    'code' => 'SUCCESS',
                    'msg' => 'OK',
                ]);

            }else{
                return "";
            }
        }else{
            return "";
        }

    }

    private function exemptSign_notify()
    {
        global $_G;

        if(empty($_POST)) {
            return '';
        }
		
        $timeStamp = $_POST['timeStamp'];
        $fee = intval($_POST['fee']);
        //$optional = $_POST['optional'];//原样返回参数
        $orderid = $_POST['orderid'];
        $sign = $_POST['sign'];

        //第一步:验证签名
        $checksign = $this->buildRequestSign($timeStamp);
        if($checksign != $sign) {// 签名不正确
            return '';
        }

        //-------------获取订单参数
        $data = DB::fetch_first("SELECT * FROM ".DB::table('a_yinxingfei_recharge_order')." WHERE id = '".$orderid."'");
        $optional = unserialize($data['optional']);

        //第二步:是否已经操作过，避免重复操作
        if($data['state'] != 1){
            return '';
        }

        //第三步:验证订单金额与购买的产品实际金额是否一致
        if($optional['fee'] != $fee){
            return '';
        }

        //第四步:处理业务逻辑和返回
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
            'price' => $fee/100,
            'value' => $_G['setting']['extcredits'][$optional['snpExtcredits']]['title'].' '.$snpNum.' '.$_G['setting']['extcredits'][$optional['snpExtcredits']]['unit']
        ), 1);

        return 'success';//不可更改
    }

    private function buildRequestSign($timestamp)
    {
        global $_G;
        $sign = md5($_G['cache']['plugin']['yinxingfei_recharge']['partner'].$_G['cache']['plugin']['yinxingfei_recharge']['tkey'].$timestamp);
        return $sign;
    }

}