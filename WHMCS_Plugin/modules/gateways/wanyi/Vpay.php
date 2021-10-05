<?php

include dirname(__FILE__) . '/class/Sign.php';
include dirname(__FILE__) . '/class/Web.php';

class Vpay{
    //后台api数据
    const Api_Ok=0;//接口状态ok
    const Api_Err=-1;//接口状态错误
    //监控端
    const State_Online=1;//监控在线
    const State_Offline=0;//监控掉线
    const State_Nobind=-1;//监控还没绑定
    //递增递减
    const PayIncrease=1;//递增
    const PayReduce=2;//递减
    //订单状态常量定义
    const State_Succ = 3;//远程服务器回调成功，订单完成确认
    const State_Err = 2;//通知失败,回调服务器没有返回正确的响应信息
    const State_Ok = 1;//支付完成，通知成功
    const State_Wait = 0;//订单等待支付中
    const State_Over = -1;//订单超时
    //支付选择
    const NeedHtml=1;//需要html
    const NeedData=0;//我只要支付相关的数据
    //支付方式
    const PayWechat=1;
    const PayAlipay=2;
    private $conf;
    private $err;

    /**
     * Vpay constructor.
     * @param null $conf 配置文件数组
     */
    public function __construct($conf=null) {
        if($conf) {
            $this->conf=$conf;
        } else {
            $this->conf=include(dirname(__FILE__).'/config.php');
        }
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getErr() {
        return $this->err;
    }

    //订单创建
    public function Create($arg,$html=1) {
        if(!isset($arg["payId"])){
            $this->err="请传入payId";return false;
        }
        if(!isset($arg["price"])){
            $this->err="请传入price";return false;
        }
        if(!isset($arg["param"])){
            $this->err="请传入param,不需要参数请留空~";return false;
        }
        if(!isset($arg["type"])){
            $this->err="请传入type，支付方式：1 微信 2支付宝";return false;
        }
        //进行签名
        $alipay = new \Sign();
        $arg["type"]=intval($arg["type"])===1?1:2;
        $arg["price"]=floatval($arg["price"]);
        $arg["isHtml"] = intval($html)===1?1:0;//采用自带的ui或者自己写ui
        $arg["appid"] = $this->conf["Appid"];//把appid也参与计算
        $arg["sign"]  = $alipay->getSign($arg, $this->conf["Key"]);

        //生成签名后的url
        $_SESSION['timeOut']=strtotime('+'.$this->conf['TimeOut'].' min');

        $web=new Web();
        $result=$web->get($this->conf["CreateOrder"],$arg);

        $json=json_decode($result);
        if($json){
            if($json->code===self::Api_Ok)
                return $json->data;
            else{
                $this->err=$json->msg;
                return false;
            }
        } else {
            $this->err='远程支付站点发生问题，或创建订单的地址有误';
            return false;
        }
    }

    //签名校验，此处校验的是notify或者return的签名
    private function CheckSign($arg) {
        $sign = $arg['sign'];
        $arg = array_diff_key($_GET, array("sign" => $sign));
        $alipay = new \Sign();
        $_sign = $alipay->getSign($arg, $this->conf["Key"]);
        if (md5($_sign) !== md5($_sign)) {
            $this->err="sign校验失败！";
            return false;
        } else {
            return true;
        }
    }

    /**
     * name 同步回调
     * @param $arg
     * @return bool
     */
    public function PayReturn($arg){
        $bool=$this->CheckSign($arg);
        $payId=$this->checkClient($arg['price'],$arg['param']);
        if($bool&&$payId===$arg['payId']){
            $this->closeClient();
            return true;
        }else{
            if($bool) {
                $this->err='支付已完成！请不要重复刷新！';
                return false;
            }
        }
    }

    /**
     * name 异步回调
     * @param $arg
     * @return bool
     */
    public function PayNotify($arg){
        //检查sign
        if(!$this->CheckSign($arg))return false;
        //检查是否支付
        $payId = $arg["payId"];
        $web = new web();
        $res = $web->get($this->conf["OrderState"] ,array('payId'=>$payId));
        $json = json_decode($res);
        if (isset($json->code) && intval($json->code) === self::Api_Ok && isset($json->state) ) {
            //这是交易完成
            if(intval($json->state) === self::State_Ok){
                $alipay=new Sign();
                $key = $this->conf["Key"];
                //确认交易
                $param=['payId'=>$payId];
                $param['sign']=$alipay->getSign($param, $key);
                $url = $this->conf["Confirm"] ;
                //交易要确认
                $web->get($url,$param);
                return true;
            }elseif($json->state === self::State_Succ){
                $this->err="该交易已经完成！";
                return false;
            }elseif($json->state === self::State_Wait){
                $this->err="正在等待交易！";
                return false;
            }elseif($json->state === self::State_Over){
                $this->err="该订单已经超时或被远程关闭！";
                return false;
            }
        } else {
            $this->err="订单不存在！";
            return false;
        }
    }

    /**
     * name 关闭订单，主要用于用户自己开启了之后使用
     * @param $payId
     * @return bool
     */
    public function Close($payId){
        $this->closeClient();
        $web=new Web();
        $alipay=new Sign();
        $key = $this->conf["Key"];
        $param=['payId'=>$payId];
        $param['sign']=$alipay->getSign($param, $key);
        $url = $this->conf["CloseOrder"] ;
        $res=$web->get($url,$param);
        $json=json_decode($res);
        if($json->code===self::Api_Err){
            $this->err=$json->msg;
            return false;
        } else {
            return true;
        }
    }

    public function getPayId($price,$param){
        if($PayId=$this->checkClient($price,$param)){
            return $PayId;
        }else{
            $clientID=md5(md5($price).sha1(urldecode($param)));
            $_SESSION['clientID']=$clientID;
            $PayId = date("YmdHms") . rand(1, 9) . rand(1, 9) . rand(1, 9) . rand(1, 9);
            $_SESSION['payID']=$PayId;
            return $PayId;
        }
    }

    private function checkClient($price,$param){
        $param=urldecode($param);
        $clientID=md5(md5($price).sha1($param));
        if(isset($_SESSION['clientID'])&&$_SESSION['clientID']===$clientID){
            //var_dump(isset($_SESSION['payID']),isset($_SESSION['timeOut']),intval($_SESSION['timeOut']),time());
            if(isset($_SESSION['payID'])&&isset($_SESSION['timeOut'])&&intval($_SESSION['timeOut'])>time()){
                return $_SESSION['payID'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function closeClient(){
        $_SESSION['clientID']=false;
        $_SESSION['timeOut']=false;
        $_SESSION['payID']=false;
    }

}