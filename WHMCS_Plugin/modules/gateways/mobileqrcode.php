<?php
function mobileqrcode_config() {
    $configarray = array(
        "FriendlyName"  => array(
            "Type"  => "System",
            "Value" => "手机扫码支付"
        ),
    );
    return $configarray;
}

function mobileqrcode_form($params) {
    $systemurl = $params['systemurl'];
    $invoiceid = $params['invoiceid'];
    $amount    = $params['amount']; # Format: ##.##
    $seller_email = $params['seller_email'];
    $fingerprint      = '0.' . $invoiceid;
    $shouldpay      =  $amount; //+$fingerprint;
    $form_html = '';
    //$img=$seller_barcode; //这个图片要先存放好.
	$wximg=$systemurl."/modules/gateways/qr_wechat.jpg";
	$aliimg=$systemurl."/modules/gateways/qr_alipay.jpg";
    $code  = $form_html . '

<div class="alert alert-danger" role="alert">非常抱歉: 支付网关协议到期,临时请使用扫码支付,如果不方便请提交工单我们提供其他方式支付!</div>

<div class="row">
  <div class="col-xs-6 col-sm-6 col-md-6">
	<div class="thumbnail">
		<div class="caption">
			<h4>支付宝扫码</h4>
			<img src="'.$aliimg.'" class="img-thumbnail img-responsive">
		</div>
	</div>
  </div>
  <div class="col-xs-6 col-sm-6">
	<div class="thumbnail">
		<div class="caption">
			<h4>微信扫码</h4>
			<img src="'.$wximg.'" class="img-thumbnail img-responsive">
		</div>
	</div>
  </div>
</div>

<strong>扫码支付的方法:</strong>
<ol style="text-align:left;">
  <li>使用微信/支付宝APP,扫描上方二维码</li>
  <li>输入金额: <input style="width:128px;" name="payAmount" value="'.$shouldpay.'"/></li>
  <li>备注输入: <kbd>账单ID '.$invoiceid.'</kbd> <span class="label label-danger"> 不要忘记</span></li>
  <li>工作时间: 付款完成后1小时内会处理完毕</li>
  <li>紧急业务: 联系QQ:525742937 / 致电:16605535352 </li>
</ol>
	
	'; // 微信扫码 <img src="" style="width: 250px;"><br> 支付宝扫码<img src="'.$aliimg.'" style="width: 250px;">
    return $code;
}

function mobileqrcode_link($params) {
    return mobileqrcode_form($params);
}

?>
