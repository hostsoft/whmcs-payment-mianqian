<?php

$appid="3";//应用id，在后台应用列表的id部分可以看到
$key="test key"; //该应用的通讯密钥，如果密钥泄露可以重置密钥
$url="https://www.pay.com"; //支付站点的URL

return [
    "Appid"=>$appid,//应用id，在后台应用列表的id部分可以看到
    "Key"=>$key,//该应用的通讯密钥，如果密钥泄露可以重置密钥
    "CreateOrder"=>$url."/CreateOrder",//创建订单的接口
    "OrderState"=>$url."/OrderState",//查看订单状态的接口
    "CloseOrder"=>$url."/CloseOrder",//关闭订单的接口
    "Confirm"=>$url."/Confirm",//确认
    "GetOrder"=>$url."/GetOrder",//确认
    'TimeOut'=>'5'
];