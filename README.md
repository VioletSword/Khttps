# Khttps
php https请求类-------------------------可实现普通http请求与https携带证书验证请求

方法列表以请求方式分两类：

get请求：

​	1.send_get--------------------------普通get请求

​	2.send_get_not_ssl--------------https 不带证书get请求

​	3.send_get_ssl--------------------https 带证书get请求

post请求：

​	1.send_post------------------------普通post请求

​	2.send_post_not_ssl-------------https 不带证书post请求

​	3.send_post_ssl-------------------https 带证书post请求

方法参数说明：

```php
/*
* 普通get请求
* string $url               请求的url地址，需要包含协议头
* array $data[可选]               请求的数据
* boolean $header[可选]           是否将头文件的信息作为数据流输出
* boolean $returntransfer[可选]   是否直接输出内容
*/
send_get($url,$data='',$header=false,$returntransfer=true)

/*
* https 不带证书get请求
* string $url                     请求的url地址，需要包含协议头
* array $data[可选]               请求的数据
* boolean $header[可选]           是否将头文件的信息作为数据流输出
* boolean $returntransfer[可选]   是否直接输出内容
*/
send_get_not_ssl($url,$data='',$header=false,$returntransfer=true)

/**
* https 带证书get请求
* $url[必选]            请求地址
* $pem[必选]            客户端请求证书
* $key[必选]            客户端请求证书密钥
* $data[可选]           请求内容
* $verify[可选]         是否验证携带的证书
* $header[可选]         是否返回响应头信息
* $returntransfer[可选] 是否将结果返回
*/
send_get_ssl($url,$pem,$key,$data='',$verify=false,$header=false,$returntransfer=true)
    
/**
* [send_post 普通post请求]
* @param  string  $url            [必选]请求地址
* @param  mixed   $data           [可选]请求数据
* @param  string  $httpHeader     [可选]请求报文格式如：xml,json等
* @param  boolean $header         [可选]是否输出头信息
* @param  boolean $returntransfer [可选]是否将请求结果返回
* @return string                  [请求结果]
*/
send_post($url,$data='',$httpHeader='',$header=false,$returntransfer=true)
    
/**
* [send_post https 不带证书post请求]
* @param  string  $url            [必选]请求地址
* @param  mixed   $data           [可选]请求数据
* @param  string  $httpHeader     [可选]请求报文格式如：xml,json等
* @param  boolean $header         [可选]是否输出头信息
* @param  boolean $returntransfer [可选]是否将请求结果返回
* @return mixed                   请求结果
*/
send_post_not_ssl($url,$data='',$httpHeader='',$header=false,$returntransfer=true)

/**
* [send_post_ssl https 带证书post请求]
* @param  string  $url            [必选]请求地址
* @param  string  $pem            [必选]证书路径 如：/xxx目录/client_504569.crt'; 注意目录和文件的可读权限
* @param  string  $key            [必选]证书密钥路径 如'/xxx目录//client_504569.key'; 注意目录和文件的可读权限
* @param  mixed   $data           [可选]请求数据
* @param  string  $httpHeader     [可选]请求报文格式如：xml,json等
* @param  boolean $verify         [可选]是否验证携带的证书
* @param  boolean $header         [可选]是否输出头信息
* @param  boolean $returntransfer [可选]是否将请求结果返回
* @return mixed                   请求结果
*/
send_post_ssl($url,$pem,$key,$data='',$httpHeader='',$verify=false,$header=false,$returntransfer=true)

```

调用示例：

```php
reqiuire_once 'XXX/Khttps.php';   //引入Khttps类

$https = new Khttps();
$data = [   //构建请求数据
    'id' => 1,
    'title' => '哈哈',
    'page' => 6
];
//示例1：
$res = $https->send_get('http://www.baidu.com');   //发起一个普通get请求
echo $res;   //输出响应信息

//示例2：
$res = $https->send_get('http://www.baidu.com/?id=1&title=哈哈&page=6');  //发起一个普通请求
echo $res;

/*
*说明：
*这两个请求其实一个样的
*示例1使用了第二个参数，将构建好的参数传入，方法内部会将构建好的参数进行自动拼接成*http://www.baidu.com/?id=1&title=哈哈&page=6 这种形式
*示例2：没有使用第二个参数，直接将参数拼接在地址后面请求，无论使用那种方式最终请求的链接都将是
*http://www.baidu.com/?id=1&title=哈哈&page=6 这种形式
*这种自动拼接参数的功能只有get方式才有，因为post请求可以直接将构建好的参数直接发送，而不需要拼接
*成链接的形式
```