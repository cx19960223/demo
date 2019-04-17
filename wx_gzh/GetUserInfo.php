<?php
/**
 * 网页授权
 * @author 菜穗子
 */
include_once 'Base.php';
class GetUserInfo extends Base{
    // 用户性别
    public $sex = [
        '0' => '未知',
        '1' => '男',
        '2' => '女',
    ];

    // 获取用户基本信息
    public function userInfo()
    {
        // 1 第一步：用户同意授权，获取code [关注之后默认同意授权]
        // 2 第二步：通过code换取网页授权access_token
        // 3 第三步：刷新access_token（如果需要）
        // 4 第四步：拉取用户信息(需scope为 snsapi_userinfo)
        // 5 附：检验授权凭证（access_token）是否有效

        $code = $_GET['code'];
        // 获取access_token的链接
        $this->url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appID.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code';
        $result = $this->request('get');
        if( !empty($result['errcode']) ){
            echo '获取token错误，错误原因：'.$result['errmsg'];
            return false;
        }

        // 验证access_token是否有效
        $access_token = $result['access_token'];
        $openid = $result['openid'];
        $this->url = "https://api.weixin.qq.com/sns/auth?access_token=$access_token&openid=$openid";
        $result = $this->request('get');
        if( $result['errcode'] != '0' ){
            echo 'token验证错误，错误原因：'.$result['errmsg'];
            return false;
        }
        
        // 拉取用户信息链接
        $this->url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
        $result = $this->request('get');
        if( !empty($result['errcode']) ){
            echo '拉取用户信息错误，错误原因：'.$result['errmsg'];
            return false;
        }
        $data = "
            <h2>当前用户的基本信息为：</h2>
            <h2>openid：".$result['openid']."</h2>
            <h2>昵称：".$result['nickname']."</h2>
            <h2>性别：".$this->sex[ $result['sex'] ]."</h2>
            <h2>地区：".$result['city']."</h2>
            <h2>省份：".$result['province']."</h2>
            <h2>国家：".$result['country']."</h2>
            <h2>头像：</h2>
            <img src = \"".$result['headimgurl']."\">
        ";
        echo $data;
    }

    /**
     * 获取关注的用户列表（没超过1w人）
     * @param access_token 调用接口凭证
     * @param next_openid 第一个拉取的OPENID，不填默认从头开始拉取
     *  */
    public function userList()
    {
        $token = $this->getAccessToken();
        $this->url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$token";
        $result = $this->request('get');
        if(!empty($result['errcode'])){
            echo '获取列表报错，错误原因：'.$result['errmsg'];
            return false;
        }
        // 循环出关注人的openid
        foreach($result['data'] as $key => $val){
            foreach($val as $k => $v){
                $user_list .= "<h2>用户".$k."的openid：$v</h2>";
            }
        }
        $data = "
            <h2>关注公众号的人数：".$result['total']."位</h2>
            $user_list
        ";
        echo $data;
    }

}

$info = new GetUserInfo();
$info->userInfo();
// $info->userList();