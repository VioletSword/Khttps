<?php
/**
 *https请求类---------可实现普通http请求与https携带证书验证请求
 *@author TaurusK
 *@version 2.1.1
 *@date 2019-01-05 19:55:36
 *@update 2019-04-02 15:18:30
 */
class Khttps {

	private static $https = null;   //保存单例对象
	private $option;                //自定义选项
	private $option_is_perpetual;   //设置的选项是否在实例生命周期内有效，默认为设置的第一次有效


	/*
	 * 普通get请求
	 * string $url               请求的url地址，需要包含协议头
	 * array $data[可选]               请求的数据
	 * boolean $header[可选]           是否将头文件的信息作为数据流输出
	 * boolean $returntransfer[可选]   是否直接输出内容
	 */
	public function send_get($url,$data='',$header=false,$returntransfer=true){
		
		//初始化一个curl会话
		$ch = curl_init();
		//是否将头文件的信息作为数据流输出
		curl_setopt($ch, CURLOPT_HEADER, $header);
		//不直接输出内容
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);

		//选项设置处理
		$this->setOptionDispose($ch);
		
		//判断$data是否为空，不为空且为数据则组合请求数据
		$url = $this->getRequestDataRegroup($url,$data);

		//设置请求url
		curl_setopt($ch, CURLOPT_URL, $url);

		// 执行
		$result = curl_exec($ch);

		// 检查是否有错误发生
		if($i = curl_errno($ch))
		{
		    $result = [
		    	'errno' => $i,
		    	'info'    => 'request error['.$i.']: ' . curl_error($ch)
		    ];
		}

		//关闭cURL资源，并且释放系统资源
		curl_close($ch);
		
		return $result;
	}


	/*
	 * https 不带证书get请求
	 * string $url                     请求的url地址，需要包含协议头
	 * array $data[可选]               请求的数据
	 * boolean $header[可选]           是否将头文件的信息作为数据流输出
	 * boolean $returntransfer[可选]   是否直接输出内容
	 */
	public function send_get_not_ssl($url,$data='',$header=false,$returntransfer=true){
		//初始化一个curl会话
		$ch = curl_init();
		//是否将头文件的信息作为数据流输出
		curl_setopt($ch, CURLOPT_HEADER, $header);
		//不直接输出内容
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);

		//选项设置处理
		$this->setOptionDispose($ch);
		
		//判断$data是否为空，不为空且为数据则组合请求数据
		$url = $this->getRequestDataRegroup($url,$data);

		//设置请求url
		curl_setopt($ch, CURLOPT_URL, $url);

		//下面的设置很重要===============SSL请求设置=========================================
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   //【重要设置】此设置表示无论我方有没有携带证书，都跳过证书验证

		// 执行
		$result = curl_exec($ch);

		// 检查是否有错误发生
		if($i = curl_errno($ch))
		{
		    $result = [
		    	'errno' => $i,
		    	'info'    => 'request error['.$i.']: ' . curl_error($ch)
		    ];
		}

		//关闭cURL资源，并且释放系统资源
		curl_close($ch);
		
		return $result;
	}


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
	public function send_get_ssl($url,$pem,$key,$data='',$verify=false,$header=false,$returntransfer=true){
		//初始化一个curl会话
		$ch = curl_init();
	    
		
	    $params[CURLOPT_HEADER] = $header; //是否返回响应头信息
	    $params[CURLOPT_RETURNTRANSFER] = $returntransfer; //是否将结果返回
	    $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向

	    //选项设置处理
		$this->setOptionDispose($ch);
	    
	    //判断$data是否为空，不为空且为数据则组合请求数据
		$url = $this->getRequestDataRegroup($url,$data);

		$params[CURLOPT_URL] = $url;    //请求url地址
		
		//下面的设置很重要===============SSL请求设置=======================================================
	    $params[CURLOPT_SSL_VERIFYPEER] = $verify;      //【重要设置】此设置表示是否对我方携带的证书进行验证
	    $params[CURLOPT_SSL_VERIFYHOST] = $verify ? 2 : 0;      //【重要设置】此设置表示是否对我方携带的证书进行域名验证
	    
	    //以下是证书相关代码
	    $params[CURLOPT_SSLCERTTYPE] = 'PEM';  //证书类型，一般为PEM还有其它类型
	    $params[CURLOPT_SSLCERT] = $pem;       //证书路径 如：/xxx目录/client_504569.crt';
	    $params[CURLOPT_SSLKEYTYPE] = 'PEM';  //证书密钥类型，一般为PEM还有其它类型
	    $params[CURLOPT_SSLKEY] = $key;       //证书密钥路径 如'/xxx目录//client_504569.key';
	    curl_setopt_array($ch, $params);     //传入curl参数
	    
	    // 执行
		$result = curl_exec($ch);

		// 检查是否有错误发生
		if($i = curl_errno($ch))
		{
		    $result = [
		    	'errno' => $i,
		    	'info'    => 'request error['.$i.']: ' . curl_error($ch)
		    ];
		}

		//关闭cURL资源，并且释放系统资源
		curl_close($ch);
		
		return $result;
	}

//==================================================post请求方法======================================
	/**
	 * [send_post 普通post请求]
	 * @param  string  $url            [必选]请求地址
	 * @param  mixed   $data           [可选]请求数据
	 * @param  boolean $header         [可选]是否输出头信息
	 * @param  boolean $returntransfer [可选]是否将请求结果返回
	 * @return string                  [请求结果]
	 */
	public function send_post($url,$data='',$header=false,$returntransfer=true){
		//初始化一个curl会话
		$ch = curl_init();
		//请求地址
		curl_setopt($ch, CURLOPT_URL, $url);
		//是否将头文件的信息作为数据流输出
		curl_setopt($ch, CURLOPT_HEADER, $header);
		//不直接输出内容，即将请求结果返回
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);
		//设置post方式提交
		curl_setopt($ch, CURLOPT_POST, 1);
		//设置全部数据以post提交
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		//选项设置处理
		$this->setOptionDispose($ch);

		
		// 执行
		$result = curl_exec($ch);

		// 检查是否有错误发生
		if($i = curl_errno($ch))
		{
		    $result = [
		    	'errno' => $i,
		    	'info'    => 'request error['.$i.']: ' . curl_error($ch)
		    ];
		}

		//关闭cURL资源，并且释放系统资源
		curl_close($ch);
		
		return $result;
	}


	/**
	 * [send_post https 不带证书post请求]
	 * @param  string  $url            [必选]请求地址
	 * @param  mixed   $data           [可选]请求数据
	 * @param  boolean $header         [可选]是否输出头信息
	 * @param  boolean $returntransfer [可选]是否将请求结果返回
	 * @return mixed                   请求结果
	 */
	public function send_post_not_ssl($url,$data='',$header=false,$returntransfer=true){
		//初始化一个curl会话
		$ch = curl_init();
		//请求地址
		curl_setopt($ch, CURLOPT_URL, $url);
		//是否将头文件的信息作为数据流输出
		curl_setopt($ch, CURLOPT_HEADER, $header);
		//不直接输出内容，即将请求结果返回
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);
		//设置post方式提交
		curl_setopt($ch, CURLOPT_POST, 1);
		//设置全部数据以post提交
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		//选项设置处理
		$this->setOptionDispose($ch);

		
    	//下面的设置很重要===============SSL请求设置=========================================
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   //【重要设置】此设置表示无论我方有没有携带证书，都跳过证书验证

		
		// 执行
		$result = curl_exec($ch);

		// 检查是否有错误发生
		if($i = curl_errno($ch))
		{
		    $result = [
		    	'errno' => $i,
		    	'info'    => 'request error['.$i.']: ' . curl_error($ch)
		    ];
		}

		//关闭cURL资源，并且释放系统资源
		curl_close($ch);
		
		return $result;
	}


	/**
	 * [send_post_ssl https 带证书post请求]
	 * @param  string  $url            [必选]请求地址
	 * @param  string  $pem            [必选]证书路径 如：/xxx目录/client_504569.crt'; 注意目录和文件的可读权限
	 * @param  string  $key            [必选]证书密钥路径 如'/xxx目录//client_504569.key'; 注意目录和文件的可读权限
	 * @param  mixed   $data           [可选]请求数据
	 * @param  boolean $verify         [可选]是否验证携带的证书
	 * @param  boolean $header         [可选]是否输出头信息
	 * @param  boolean $returntransfer [可选]是否将请求结果返回
	 * @return mixed                   请求结果
	 */
	public function send_post_ssl($url,$pem,$key,$data='',$verify=false,$header=false,$returntransfer=true){
		//初始化一个curl会话
		$ch = curl_init();
		
		$params[CURLOPT_URL] = $url;                         //请求地址
		$params[CURLOPT_HEADER] = $header;                   //是否将头文件的信息作为数据流输出            
		$params[CURLOPT_RETURNTRANSFER] = $returntransfer;   //不直接输出内容，即将请求结果返回
		$params[CURLOPT_FOLLOWLOCATION] = true;              //是否重定向
		$params[CURLOPT_POST] = 1;                           //设置post方式提交
		$params[CURLOPT_POSTFIELDS] = $data;                 //设置全部数据以post提交 

		//选项设置处理
		$this->setOptionDispose($ch);

		
    	//下面的设置很重要===============SSL请求设置=======================================================
	    $params[CURLOPT_SSL_VERIFYPEER] = $verify;      //【重要设置】此设置表示是否对我方携带的证书进行验证
	    $params[CURLOPT_SSL_VERIFYHOST] = $verify ? 2 : 0;      //【重要设置】此设置表示是否对我方携带的证书进行域名验证
	    
	    //以下是证书相关代码
	    $params[CURLOPT_SSLCERTTYPE] = 'PEM';  //证书类型，一般为PEM还有其它类型
	    $params[CURLOPT_SSLCERT] = $pem;       //证书路径 如：/xxx目录/client_504569.crt';
	    $params[CURLOPT_SSLKEYTYPE] = 'PEM';  //证书密钥类型，一般为PEM还有其它类型
	    $params[CURLOPT_SSLKEY] = $key;       //证书密钥路径 如'/xxx目录//client_504569.key';
	    curl_setopt_array($ch, $params);     //传入curl参数
		
		
		// 执行
		$result = curl_exec($ch);

		// 检查是否有错误发生
		if($i = curl_errno($ch))
		{
		    $result = [
		    	'errno' => $i,
		    	'info'    => 'request error['.$i.']: ' . curl_error($ch)
		    ];
		}

		//关闭cURL资源，并且释放系统资源
		curl_close($ch);
		
		return $result;
	}


	/**
	 * [getRequestDataRegroup get请求数据内容组装]
	 * @param  [string] $url  [请求地址]
	 * @param  [array] $data [请求数据]
	 * @return [string]       [组装后的请求地址]
	 */
	public function getRequestDataRegroup($url,$data){
		if(!empty($data) && is_array($data)){
			$url .= '?';
			$count = 1;
			foreach($data as $k => $v){
				if($count == 1){
					$url .= $k .'='. $v;
					$count++;
				}else{
					$url .= '&'.$k .'='. $v;
				}
				
			}
		}

		return $url;
	}
	
	/**
	 * [getRequestDataRegroup get请求数据内容组装-url参数编码版]
	 * @param  [string] $url  [请求地址]
	 * @param  [array] $data [请求数据]
	 * @return [string]       [组装后的请求地址]
	 */
	public function getRequestDataRegroup_encode($url,$data){
		if(!empty($data) && is_array($data)){
			$url .= '?';
			$count = 1;
			foreach($data as $k => $v){
				if($count == 1){
					$url .= $k .'='. urlencode($v);
					$count++;
				}else{
					$url .= '&'.$k .'='. urlencode($v);
				}
				
			}
		}

		return $url;
	}

	/**
	 * [setOption 设置自定义选项]
	 * @param [array] $option              [必选] 要设置的选项值，必须是个数组
	 * @param [array] $option_is_perpetual [可选] 设置的选项是否在实例生命周期内有效，默认为设置的第一次有效
	 * 如：$https->setOption([
	 *		CURLOPT_HTTPHEADER => ["Content-Type:text/xml; charset=utf-8"]
	 *	]);
	 *	参数数组的键值必须是个curl设置的常量
	 */
	public function setOption($option,$option_is_perpetual=false){
		if(is_array($option)){
			$this->option = $option;
			$this->option_is_perpetual = $option_is_perpetual;
		}else{
			throw new \Exception("parameter error:option need is array");
		}
		
	}

	/**
	 * [setOptionDispose 自定义选项设置处理]
	 * @param [array] $ch              [必选] 一个curl资源
	 */
	private function setOptionDispose($ch){
		//设置选项
		if(is_array($this->option)){
			curl_setopt_array($ch, $this->option); //批量设置选项
		}

		//是否持久保存设置的选项
		if($this->option_is_perpetual === false){
			$this->option = '';
		}
	}
	
	/**
	 * 单例
	 */
	public static function singleObject(){
		if(self::$https === null){
			self::$https = new self();
			return self::$https;
		}else{
			return self::$https;
		}
	}
}