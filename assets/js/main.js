	var jq = jQuery.noConflict();
	var snpType = jq('#snp-type').val();
	var extcreditsValue = 0,ratioValue,leastValue,mostValue,titleValue,inputValue;

	jq('.snp-input').placeholder();
	jq("#go-pay").click(function(){
		inputValue = jq(".snp-input").val();
		inputValue = parseInt(inputValue);
		if (extcreditsValue == 0) {
			alert('请选择充值积分');
			return false;
		}else if (isNaN(inputValue)) {
			inputValue = leastValue;
			if(snpType == 1){
				alert('请输入充值积分数量');
			}else{
				alert('请输入充值金额');
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
			alert('请选择充值积分');
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
				alert('每次最少充值'+leastValue+titleValue);
			}else{
				alert('每次最少充值'+leastValue+'元');
			}
			inputValue = leastValue;
			jq(".snp-input").val(inputValue);
			snpCalculate();
			return false;
		}else if(inputValue > mostValue){
			if(snpType == 1){
				alert('每次最多充值'+mostValue+titleValue);
			}else{
				alert('每次最多充值'+mostValue+'元');
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
			jq('#snp-tip').html('充值：'+inputValue+titleValue+'，需要：'+totalValue+'元');
		}else{
			jq('#snp-tip').html('充值：'+inputValue+'元，可获得：'+(inputValue*ratioValue)+titleValue);
		}

		if(extcreditsValue == 0 || isNaN(inputValue)){
			jq('#snp-tip').css('display','none');
		}
	}
	function SuiNiPaiPay(PostData) {
		SNP.click(PostData);
	}