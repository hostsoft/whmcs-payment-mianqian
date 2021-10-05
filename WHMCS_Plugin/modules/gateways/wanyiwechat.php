<?php
@session_start();

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function wanyiwechat_MetaData()
{
    return array(
        'DisplayName' => '免签支付接口',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function wanyiwechat_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => '微信支付(扫码)',
        ),
        // a text field type allows for single line text input
        'AppURL' => array(
            'FriendlyName' => '接口地址',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '应用ID',
        ),
        // a text field type allows for single line text input
        'AppID' => array(
            'FriendlyName' => '应用ID',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '应用ID',
        ),
        // a password field type allows for masked text input
        'AppKEY' => array(
            'FriendlyName' => '应用密匙',
            'Type' => 'password',
            'Size' => '100',
            'Default' => '',
            'Description' => 'SecurityCode',
        ),
        'AppNotify' => array(
            'FriendlyName' => '通知回调',
            'Type' => 'readonly',
            'Size' => '1000',
            'Value' => '',
            'Description' => '付款通知網址請設定：<WHMCS地址+路径>/modules/gateways/wanyi/notify.php',
            'Default' => '',
        ),
    );
}

function wanyiwechat_link($params)
{
    # 网关变量
    $appurl = $params['AppURL'];
    $appid = $params['AppID'];
    $appkey = $params['AppKEY'];
    $appnotify = $params['AppNotify'];
//    $apptest = $params['testmode'];

    # 订单变量
    $invoiceid = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount']; # Format: ##.##
    $currency = $params['currency']; # Currency Code

    # 系统变量
    $companyname = $params['companyname'];
    $systemurl = $params['systemurl'];
    $currency = $params['currency'];
    $notify_url= $systemurl."/modules/gateways/wanyi/notify.php";
    $return_url= $systemurl."/modules/gateways/wanyi/return.php";

    $parameter = [
        "Appid" => $appid,//应用id，在后台应用列表的id部分可以看到
        "Key" => $appkey,//该应用的通讯密钥，如果密钥泄露可以重置密钥
        "CreateOrder" => $appurl."/CreateOrder",//创建订单的接口
        "OrderState" => $appurl."/OrderState",//查看订单状态的接口
        "CloseOrder" => $appurl."/CloseOrder",//关闭订单的接口
        "Confirm" => $appurl."/Confirm",//确认
        "GetOrder" => $appurl."/GetOrder",//确认
        'TimeOut' => '5'
    ];

    require __DIR__.'/wanyi/Vpay.php';
    $Pay = new \Vpay($parameter);

    # Merge Array
    $html = 0;
    $input = [
        "payId" =>  $invoiceid,
        "price" =>  $amount,
        "param" => urlencode("deposit"),
        "type" => 1,
    ];

    $result = $Pay->Create($input,$html);
//    $ret = array();
//    if($result === false) {
//        $ret['code'] = -1;
//        $ret['msg'] = $Pay->getErr();
//        $ret['redirect'] = null;
//    } else {
//        $ret['code'] = 0;
//        $ret['msg'] = "success";
//        $paylink = "{$appurl}/api/pay/index?orderId={$result->data->orderId}";
//        $ret['redirect'] = $paylink;
//    }
    $link = "{$appurl}/api/pay/index?orderId={$result->data->orderId}";
    $icon = "{$systemurl}/modules/gateways/wanyi/wechatbtn.png";
    $code = "<a href=\"{$link}\"><img src=\"{$icon}\" class=\"rounded\" target='_blank' alt=\"点击使用微信扫码支付\" style='max-width: 200px;'></a>";
    return $code;
}
