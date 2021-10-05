<?php
# Required File Includes
include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gatewaymodule = ($_GET['type'] == 1) ? "wanyiwechat":"wanyialipay";
$GATEWAY = getGatewayVariables($gatewaymodule);
//var_dump($GATEWAY);
if (!$GATEWAY["type"]) edie("Module Not Activated"); # Chcks gateway module is active before accepting callback
//$need_confirm=$GATEWAY['need_confirm'];
//$auto_send=$GATEWAY['auto_send'];
$debug=$GATEWAY['debug'];

$appurl = $GATEWAY['AppURL'];
$appid =$GATEWAY['AppID'];
$appkey = $GATEWAY['AppKEY'];
$appnotify = $GATEWAY['AppNotify'];

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

include_once dirname(__FILE__) . '/Vpay.php';
$Pay = new \Vpay($parameter);
//var_dump($Pay);

//var_dump($Pay->PayNotify($_GET));

if($Pay->PayNotify($_GET)) {

    $invoiceid = $_GET['payId'];
    $transid = "WY_".time();
    $param = urldecode($_GET['param']);
    $type = ($_GET['type'] == 1) ? "Wechat Pay":"AliPay";
    $price = $_GET['price'];
    $amount = $_GET['reallyPrice'];
    $fee = 0;

//    if ($debug) logResult("订单 $invoiceid  支付成功.");
    $invoiceid = checkCbInvoiceID($invoiceid,$GATEWAY["name"]);
//    var_dump($invoiceid);
    checkCbTransID($transid);
    addInvoicePayment($invoiceid,$transid,$amount,$fee,$gatewaymodule);
    logTransaction($GATEWAY["name"],$_GET,"Successful-A");

    //此处进行你自己的业务逻辑（数据库的插入操作）
    header('Content-Type: application/json');
    echo json_encode(array("state"=>Vpay::Api_Ok,"msg"=>"okok"));
}else{
    logTransaction($GATEWAY["name"],$_GET,"Unsuccessful1");
    header('Content-Type: application/json');
    echo json_encode(array("state"=>Vpay::Api_Err,"msg"=>$Pay->getErr()));//可以通过这个查看错误信息
    exit;
    //没有通过sign验证或者这笔订单异常
}