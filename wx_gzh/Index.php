<?php
/**
 * 1.接入服务器验证
 * 2.消息管理
 * @author 菜穗子
 */
include_once 'Base.php';//公共类
class Index extends Base{
    //接入微信公众平台，验证服务器
    public function check()
    {
        // 开发者提交信息后，微信服务器将发送GET请求到填写的服务器地址URL上，GET请求携带参数如下表所示：
        $signature = $_GET['signature'];//微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
        $timestamp = $_GET['timestamp'];//时间戳
        $nonce = $_GET['nonce'];//随机数
        $echostr = $_GET['echostr'];//随机字符串

        /**
         * 开发者通过检验signature对请求进行校验。若确认此次GET请求来自微信服务器，请原样返回echostr参数内容，则接入生效，成为开发者成功，否则接入失败。加密/校验流程如下：
         * 1.将token、timestamp、nonce三个参数进行字典序排序
         * 2.将三个参数字符串拼接成一个字符串进行sha1加密
         * 3.开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
         *  */
        $tmpArr = [ $this->Token, $timestamp, $nonce ];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if($tmpStr == $signature){
            echo $echostr;
        }else{
            return false;
        }
    }

    // 接受普通请求
    public function getMessage()
    {
        $postStr = file_get_contents('php://input');
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);//消息类型

            //用户发送的消息类型判断(通过微信的event字段判断)
            switch ($RX_TYPE)
            {
                case "event":   //推送用户触发的事件
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":    //文本消息
                    $result = $this->receiveText($postObj);//被动回复
                    break;
                case "image":   //图片消息
                    $result = $this->receiveImage($postObj);
                    break;
                case "voice":   //语音消息
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":   //视频消息
                    $result = $this->receiveVideo($postObj);
                    break;
                case "location"://位置消息
                    $result = $this->receiveLocation($postObj);
                    break;
                case "link":    //链接消息
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknow msg type: ".$RX_TYPE;
                    break;
            }
            echo $result;
        }else {
            echo '';
            exit;
        }
    }

    // 获取用户触发的具体事件
    private function receiveEvent($object)
    {
        $event = $object->Event;
        switch($event)
        {
            case 'CLICK': //点击事件
                $result = $this->receiveClick($object);
                break;
            default:
                $result = "unknow event key: ".$RX_TYPE;
                break;
        }
        return $result;
    }

    // 触发点击事件
    private function receiveClick($object)
    {
        $key = isset($object->EventKey) ? $object->EventKey : '';//获取点击事件携带的key
        switch($key)
        {
            case 'V1001_GOOD': // 操作：菜单 => 赞一下 
                $content = '感谢支持。喜欢事自然可以坚持，不喜欢的再怎么也长久不了';
                $result = $this->transmitText($object, $content);
                break;
            case 'V1001_TODAY_MUSIC': //今日歌曲
                $content = '今日分享的歌曲为王菲的 《清风徐来》';
                $result = $this->transmitText($object, $content);
                break;
            default:
                $result = "unknow event key: ".$RX_TYPE;
                break;
        }
        return $result;
    }

    // 接收文本信息
    private function receiveText($object)
    {
        $content = "你发送的是文本，内容为：".$object->Content;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收图片消息
     */
    private function receiveImage($object)
    {
        $content = "你发送的是图片，地址为：".$object->PicUrl;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收语音消息
     */
    private function receiveVoice($object)
    {
        $content = "你发送的是语音，媒体ID为：".$object->MediaId;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收视频消息
     */
    private function receiveVideo($object)
    {
        $content = "你发送的是视频，媒体ID为：".$object->MediaId;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收位置消息
     */
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收链接消息
     */
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

     /*
     * 回复文本消息
     * -----------------
     * 注：更多恢复类型参考 消息管理 => 被动回复用户消息
     */
    private function transmitText($object, $content)
    {
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    
}

$index = new Index();
// $index->check();//验证服务器时调用
$index->getMessage();//验证通过后，微信会将用户在公众号的所有操作都推送到该文件上，所以这里需要调用getMessage来判断用户操作
