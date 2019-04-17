<?php
/**
 * 自定义菜单模块
 * ----------------
 * 1、自定义菜单最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。
 * 2、一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。
 * --------------------------------------------------------------------------------
 * 个性化菜单步骤相同，不多写了
 * @author 菜穗子
 */
include_once 'Base.php';
class CaiDan extends Base{

    // 自定义菜单创建接口
    public function create()
    {  
        $token = $this->getAccessToken();
        $this->url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;
        /**
         * 当前为click和view的请求示例
         * 更多请求示例参考：微信公众平台文档 => 自定义菜单 => 自定义菜单创建接口
         *  */
        $data = '
        {
            "button":[
            {    
                 "type":"click",
                 "name":"今日歌曲",
                 "key":"V1001_TODAY_MUSIC"
             },
             {
                  "name":"菜单",
                  "sub_button":[
                    {    
                        "type":"view",
                        "name":"我的博客",
                        "url":"http://chenxin.pro"
                    },
                    {    
                        "type":"view",
                        "name":"获取用户基本信息",
                        "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appID.'&redirect_uri=http://www.chenxin.pro/GetUserInfo.php&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect"
                    },
                    {
                        "type":"miniprogram",
                        "name":"我的小程序",
                        "url":"http://mp.weixin.qq.com",
                        "appid":"wx286b93c14bbf93aa",
                        "pagepath":"pages/lunar/index"
                    },
                    {
                        "type":"click",
                        "name":"赞一下",
                        "key":"V1001_GOOD"
                    }]
              }]
        }';
        $result = $this->request('post',$data);
        if($result['errcode'] == '0'){
            echo '自定义创建菜单成功';
        }else{
            echo '自定义创建菜单失败，失败原因：'.$result['errmsg'];
        }
    }

    /**
     * 自定义菜单查询接口
     * 注：menu为默认菜单，conditionalmenu为个性化菜单列表。字段说明请见个性化菜单接口页的说明。
     * @return json
     *  */
    public function query()
    {
        $token = $this->getAccessToken();
        $this->url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$token;
        $result = $this->request('get');
        if(!empty($result)){
            $this->dump($result);
        }else{
            echo '未返回数据';
        }
    }

    // 自定义菜单删除接口
    public function delete()
    {
        $token = $this->getAccessToken();
        $this->url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$token;
        $result = $this->request('get');
        if($result['errcode'] == '0'){
            echo '删除菜单成功';
        }else{
            echo '删除菜单失败，失败原因：'.$result['errmsg'];
        }
    }

}

$caidan = new CaiDan();
$caidan->create();