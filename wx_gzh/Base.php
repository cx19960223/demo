<?php
/**
 * 微信公用部分
 * @author 菜穗子
 */
class Base{
    protected $appID = '你的appid';//appID

    protected $appsecret = '你的密钥';//密钥

    protected $Token = '你填写的token';//开发者自定义的token

    protected $url = '';//请求链接
    /**
     * 获取access_token
     * ----------------------------------------------------------------------------------------------
     * access_token是公众号的全局唯一接口调用凭据，公众号调用各接口时都需使用access_token。开发者需要进行妥善保存。
     * access_token的存储至少要保留512个字符空间。
     * access_token的有效期目前为2个小时，需定时刷新，重复获取将导致上次获取的access_token失效。
     *  */
    protected function getAccessToken()
    {
        $this->url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appID.'&secret='.$this->appsecret;
        $result = $this->request('get');
        if( !empty($result['access_token']) ){
            return $result['access_token'];
        }else{
            echo '未获取到access_token';
            return false;
        }
    }

    // 封装请求
    protected function request($type, $data = '')
    {
        $ch = curl_init();
        switch ($type) {
            case "get" :
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "post":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "put" :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "delete":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if (!empty($error)) {
            echo '连接服务器失败，' . $error;
            return false;
        }

        $result = json_decode($content, true);

        if (empty($result)) {
            echo '没有获取到返回值';
            return false;
        }

        return $result;
    }

    /**
     * 打印函数,方便调试
     */
    public function dump($var, $exit = true) {
        echo '<pre>';
        print_r ( $var );
        echo '</pre>';
        if ($exit) {
            die ();
        }
    }

}
