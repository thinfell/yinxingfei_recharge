<?php

/**
 *      This file is part of SuiNiPai.
 *      (author) thinfell <thinfell@qq.com>
 *		[SuiNiPai] Copyright (c) 2016 Qurui Inc. Code released under the MIT License.
 *      www.suinipai.com
 */

class Weixin
{
    public function run($para)
    {
        global $_G;

        $mch_id = $_G['cache']['plugin']['yinxingfei_recharge']['ec_wxpay_mch_id'];
        $appid = $_G['cache']['plugin']['yinxingfei_recharge']['ec_wxpay_appid'];
        $key = $_G['cache']['plugin']['yinxingfei_recharge']['ec_wxpay_key'];
        $nonce_str = $this->getNonceStr();
        $out_trade_no = $para['out_trade_no'];
        $total_fee = $para['fee'];
        $body = $para['subject'];
        $notify_url = $_G['siteurl'].'yinxingfei_recharge/notify_url';
		
        $input_prepare = array(
            'appid' => $appid,
            'body' => $body,
            'mch_id' => $mch_id,
            'nonce_str' => $nonce_str,
            'notify_url' => $notify_url,
            'out_trade_no' => $out_trade_no,
            'spbill_create_ip' => $_G['clientip'],
            'total_fee' => $total_fee,
            'trade_type' => 'NATIVE',
        );

        $sign = $this->MakeSign($input_prepare,$key);

        $input = array(
            'appid' => $appid,
            'body' => $body,
            'mch_id' => $mch_id,
            'nonce_str' => $nonce_str,
            'notify_url' => $notify_url,
            'out_trade_no' => $out_trade_no,
            'spbill_create_ip' => $_G['clientip'],
            'total_fee' => $total_fee,
            'trade_type' => 'NATIVE',
            'sign' => $sign
        );

        $input_xml = $this->ToXml($input);
        $result_xml = $this->postXmlCurl($input_xml,'https://api.mch.weixin.qq.com/pay/unifiedorder',6);
        $result = $this->FromXml($result_xml);

        if($result['sign'] == $this->MakeSign($result,$key)){
            if($result['return_code'] == 'SUCCESS' && is_array($result)){
                $result["code_url"] = "http://server.suinipai.com/v1/qrcode?url=".$result["code_url"];
                $return = array(
                    'code' => 200,
                    'message' => '<img src="'.$result["code_url"].'" width="250"/>',
                );
            }else{
                $return = array(
                    'code' => 0,
                    'message' => $result["return_msg"]
                );
            }
        }else{
            $return = [
                'code' => 0,
                'message' => $result["return_msg"],
            ];
        }
        return $return;
    }

    private function postXmlCurl($xml, $url, $second = 30){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);


        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);


        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            echo "curl is error, error code is {$error}";
        }
    }

    public function ToXml($values)
    {
        $xml = "<xml>";
        foreach ($values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    private function getNonceStr($length = 32){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    private function ToUrlParams($input){
        $buff = "";
        foreach ($input as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    public function MakeSign($input,$key){
        ksort($input);
        $string = $this->ToUrlParams($input);
        $string = $string . "&key=" . $key;
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }

    public function FromXml($xml){
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

}
?>