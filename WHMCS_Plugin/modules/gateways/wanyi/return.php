<?php
echo "支付成功!";
echo "<a href=\"../../../\" target=\"_self\">点击这里返回客户系统</a>";

//@session_start();
//include_once dirname(__FILE__) . '/class/Pay.php';
//include_once 'config.php';
//$Pay = new \Pay($param);
//
//if($Pay->PayReturn($_GET)){//回调时，验证通过
//    echo "支付成功！！！<br>";
//    echo "后台订单状态必须为“订单已确认”才是真的成功了，否则是失败的<br>";
//    echo "此处的是同步回调，这里不要将数据插入数据库，因为是否支付是没有验证的，数据入库部分请放到异步回调，当你收到钱时，app会推送收钱信息到后台，后台会向该程序发送已收钱的请求<br>";
//    echo "商户订单号：" . $_GET['payId'] . "<br>自定义参数：" . urldecode($_GET['param']) . "<br>支付方式：" . $_GET['type'] . "<br>订单金额：" . $_GET['price'] . "<br>实际支付金额：" . $_GET['reallyPrice'];
//}else{
//    //没有通过验证
//    echo "<script>alert('".$Pay->getErr()."')</script>";//可以通过这个查看错误信息
//}
