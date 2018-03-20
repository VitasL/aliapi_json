<?php

namespace app\api\controller;

use app\api\controller\UnauthorizedException;
use app\api\controller\Send;
use think\Exception;
use think\Request;
use think\Db;
use think\Cache;
use Dysmsapi\Request\V20170525\SendSmsRequest;
//use Aliyun\Core\Config;
//include APP_PATH .'commom/aliApiSdk/aliyun-php-sdk-core/Config.php';
require '/aliyun-php-sdk-core/Config.php';
require '/Dysmsapi/Request/V20170525/SendSmsRequest.php';

/**
* 短信接口平台
*/
class Sms 
{
	
	function __construct()
	{
		# code...
	}


	public function send_out($mobile){
//	    var_dump($mobile);die;
	    //发送阿里云短信
//        if (empty($mobile) ) {
//          return false;
//        }
//        $mobile = $_POST['mobile'];
//        $type =get_post_value('type');



//        if ($type == 'reg') {
//            $is_register = db('user_info',[],false)->where("user_mobile=$mobile")->find();
//            if ($is_register) {
//                $this->render('30002', '该手机号已存在', array(
//                    "error" => ""
//                ));
//            }
//        }
//        if ($type != 'reg' && $type != 'pwd') {
//            $this->render('10002', '类型错误', array(
//                "error" => ""
//            ));
//        }

        $accessKeyId = "LTAI2uvUvBQ3PQbd";//参考本文档步骤2
        $accessKeySecret = "mjPa9RatDkDrQTkKIhH2wkUPNCoFyh";//参考本文档步骤2
        //短信API产品名（短信产品名固定，无需修改）
        $product = "Dysmsapi";
        //短信API产品域名（接口地址固定，无需修改）
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";

        $profile = \DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        \DefaultProfile::addEndpoint($region,$endPointName, $product, $domain);
        $acsClient= new \DefaultAcsClient($profile);
        $request = new SendSmsRequest();
        //必填-短信接收号码。支持以逗号分隔的形式进行批量调用，批量上限为1000个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
        $request->setPhoneNumbers($mobile);
//        var_dump($request);die;
        //必填-短信签名
        $request->setSignName("阿里云短信测试专用");
        //必填-短信模板Code
//        if($type=='reg'){
            $request->setTemplateCode("SMS_112785012");

//        }
//        else{
//            $request->setTemplateCode("SMS_100485016");
//        }

        //选填-假如模板中存在变量需要替换则为必填(JSON格式)
        $code = randString(6);
        $arr['code']=$code;

        $paramString = json_encode($arr);

        $request->setTemplateParam($paramString);
        //选填-发送短信流水号
//        $request->setOutId("1234");
        //发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);

        $ace=object2array($acsResponse);
//        var_dump($ace);die;
        if ( $ace['Code']!= 'OK') {
            $error=$ace['Message'];
                return $error;
//                "error" =>

        }

//        if ($type == 'reg') {
//            $is_temp = false;
//            echo$mobile; die;
            $is_reg_key =Db::name('captcha')->where("mobile",$mobile)->find();
            if(!$is_reg_key){
                $data=null;
                $data['code'] = $code;
                $data['uuid'] =uuid();
//                var_dump($data);die;
                $data['mobile'] = $mobile;
//                $data['type']="reg";
                $data['create_time'] = date("Y-m-d H:i:s",time());
                $sql = Db::name('captcha')->where($mobile)->insert($data);
//          var_dump($sql);die;
            }else{
                $data=null;
                $data['code'] = $code;
                $data['uuid'] =uuid();
                $data['create_time'] = date("Y-m-d H:i:s",time());
                $sql=Db::name('captcha')->where('mobile',$mobile)->update($data);
            }
//        }
//        elseif ($type == 'pwd') {
//            $data=null;
//            $data['vali_code'] =$code;
//            $data['mobile'] = $mobile;
//            $data['type']="pwd";
//            $data['create_time'] = time();
//            $sql = db('vali',[],false)->where("mobile=$mobile")->update($data);
//        }
//        var_dump($sql);die;
        if ($sql) {
           return false;
        } else {
            $ace['Message']="数据库操作失败";
         return $ace;
        }
    }

}