
/**
 *      This file is part of SuiNiPai.
 *      (author) thinfell <thinfell@qq.com>
 *		[SuiNiPai] Copyright (c) 2016 Qurui Inc. Code released under the MIT License.
 *      www.suinipai.com
 */

	var jq = jQuery.noConflict();
	var snpType = jq('#snp-type').val();
	var extcreditsValue = 0,ratioValue,leastValue,mostValue,titleValue,inputValue;

	jq('.snp-input').placeholder();
	jq("#go-pay").click(function(){
		inputValue = jq(".snp-input").val();
		inputValue = parseInt(inputValue);
		if (extcreditsValue == 0) {
			alert(snpLang.lang03);
			return false;
		}else if (isNaN(inputValue)) {
			inputValue = leastValue;
			if(snpType == 1){
				alert(snpLang.lang14);
			}else{
				alert(snpLang.lang15);
			}
			return false;
		}else{
			return true;
		}
	});
	jq('.snp-select').click(function(e){
		jq(this).find('ul').show();
		jq(this).addClass('on');
		e.stopPropagation();
	});
	jq('.snp-select li').click(function(e){
		textValue = jq(this).text();
		inputValue = jq(".snp-input").val();
		
		//赋值基本参数
		extcreditsValue = jq(this).attr('extcredits-value');
		ratioValue = jq(this).attr('ratio-value');
		leastValue = jq(this).attr('least-value');
		mostValue = jq(this).attr('most-value');
		titleValue = jq(this).attr('title-value');
		
		jq('.snp-select-a').html('<font color=#555>'+textValue+'</font>');
		jq('#snp-extcredits').val(extcreditsValue);
		jq('.snp-select ul').hide();
		jq('.snp-select').removeClass('on');
		
		if(inputValue < leastValue){
			jq(".snp-input").val(leastValue);
		}else if(inputValue > mostValue){
			jq(".snp-input").val(mostValue);
		}
		snpCalculate();
		e.stopPropagation();
	});
	jq(".snp-input").keyup(function(){
		inputValue = jq(this).val();
		inputValue = parseInt(inputValue);
		if (!isNaN(inputValue)) {
			jq(this).val(inputValue);
		}else{
			jq(this).val(0);
		}
		snpCalculate();
	});
	jq(".snp-input").blur(function(){
		snpCheckUp();
	});
	jq(".snp-input").keydown(function(){
		if (extcreditsValue == 0) {
			alert(snpLang.lang03);
			return false;
		}
	});
	jq(document).click(function(){
		jq('.snp-select ul').hide();
		jq('.snp-select').removeClass('on');
	});

	function credit_submit(){
		jq.post(
			"plugin.php?id=yinxingfei_recharge:index",
			jq("#pay_form").serialize(),
			function(result){
				if(result.code == 200){
					result.data.optional = eval('(' + result.data.optional + ')');
					SuiNiPaiPay(result.data);
				}else{
					console.log(result.code);
					alert(result.data);
				}
			},
			"json"
		);
		return false;
	}
	function snpCheckUp(){
		inputValue = jq(".snp-input").val();
		inputValue = parseInt(inputValue);
		if (isNaN(inputValue)) {
			inputValue = leastValue;
			jq(".snp-input").val(inputValue);
			snpCalculate();
			return false;
		}else if(inputValue < leastValue){
			if(snpType == 1){
				alert(snpLang.lang04+leastValue+titleValue);
			}else{
				alert(snpLang.lang04+leastValue+snpLang.lang09);
			}
			inputValue = leastValue;
			jq(".snp-input").val(inputValue);
			snpCalculate();
			return false;
		}else if(inputValue > mostValue){
			if(snpType == 1){
				alert(snpLang.lang05+mostValue+titleValue);
			}else{
				alert(snpLang.lang05+mostValue+snpLang.lang09);
			}
			inputValue = mostValue;
			jq(".snp-input").val(inputValue);
			snpCalculate();
			return false;
		}else{
			jq(".snp-input").val(inputValue);
			snpCalculate();
			return true;
		}
	}
	function snpCalculate(){
		jq('#snp-tip').css('display','block');
		inputValue = jq(".snp-input").val();
		if(snpType == 1){
			var totalValue = inputValue/ratioValue;
			if(totalValue < 0.01){
				totalValue = 0.01
			}
			totalValue = totalValue.toFixed(2);
			jq('#snp-tip').html(snpLang.lang44+inputValue+titleValue+snpLang.lang45+totalValue+snpLang.lang09);
		}else{
			jq('#snp-tip').html(snpLang.lang44+inputValue+snpLang.lang09+snpLang.lang46+(inputValue*ratioValue)+titleValue);
		}

		if(extcreditsValue == 0 || isNaN(inputValue)){
			jq('#snp-tip').css('display','none');
		}
	}
	function SuiNiPaiPay(PostData) {
		SNP.click(PostData);
	}