<?php




/**
 * 高效取出两个数组的差集 ,比原生的array_diff快N倍
 * warning: 函数体内 $array_2 会被键值对调，如果有重复的元素将不能使用这个函数
 */
	function array_diff_fast($array_1, $array_2) {
	    $array_2 = array_flip($array_2);
	    foreach ($array_1 as $key => $item) {
	        if (isset($array_2[$item])) {
	            unset($array_1[$key]);
	        }
	     }

	    return $array_1;
	}

/**
 * 为url自动添加上http://
 * @param string $url
 * @return string $url
 */
	function add_http( $url )
	{
		if (!preg_match( "/^(http|https):/i", trim($url) )) $url = 'http://' . $url;

		return $url;
	}


/**
 * 输出指定数量的换行标签
 * @param int $num
 */
	function br( $num = 1 )
	{
		return str_repeat("<br />", $num);
	}

/**
 * 获取Unix时间戳浮点数
 * 参数为空返回当前Unix时间戳浮点数
 * 参数为 start 或 s :标记程序开始时间浮点数；返回空
 * 参数为 end 或 e :返回程序执行所消耗的时间
 * @return 单位：秒
 * eg:
 * current_microtime('start');
 * sleep(3);
 * echo current_microtime('end');
 */
	function current_microtime ( $type = '' )
	{

		$current_time = microtime(true);

		if('' == $type) return $current_time;

		static $_start_time;

		switch ($type) {
			case 'start':
			case 's':
				$_start_time = $current_time;
				break;

			case 'end':
			case 'e':
				if(empty($_start_time)) return 'not found start position.';
				$use_time = $current_time - $_start_time;
				unset($_start_time);
				return $use_time;
				break;

			default:
				return $current_time;
				break;
		}

	}

/**
 * 有些服务器禁止使用file_get_contents()函数，代替file_get_contents函数的curl
 * @param string $url
 * @param array $options
 */

	function curl_get_contents($url , $options = array() , $get_code = FALSE)
	{
		$ch = curl_init();

		$default_options = array(
			'CURLOPT_URL'            => $url ,
			'CURLOPT_CUSTOMREQUEST'  => 'POST' ,
			'CURLOPT_RETURNTRANSFER' => 1 ,
			'CURLOPT_TIMEOUT'        => 3 ,
		);

		$options = $options ? array_merge($default_options , $options) : $default_options;
		

		foreach ($options as $option => $value)
		{
			curl_setopt($ch, constant($option), $value);
		}

		$rt = curl_exec($ch);

		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE); 

		curl_close($ch);

		return $get_code ? $httpCode : $rt ;

	 }

/**
 * curl 获取HTTP 状态码
 * @param string $url
 * @param array $options
 */
	 function curl_get_http_code($url , $options = array())
	 {
	 	return curl_get_contents($url, $options , true);
	 }

/**
 * 使用虚假IP，发起CURL请求 
 * @param string $url
 * @param string $ip
 * @param array $options
 */
	function curl_fake_ip($url, $ip = '', $options = array())
	{

		$ip              = $ip ?: make_ip();
		
		$client_ip       = 'CLIENT-IP:' . $ip;
		$x_forwarded_for = 'X-FORWARDED-FOR:' . $ip;
		
		$http_header 	 = array( $client_ip, $x_forwarded_for );

		if($options && array_key_exists('CURLOPT_HTTPHEADER', $options))
			$http_header = array_merge($http_header , $options['CURLOPT_HTTPHEADER']);

		$request_data    = array('CURLOPT_HTTPHEADER' => $http_header);
		
		$options         = $options ? array_merge($options, $request_data) : $request_data;

		return curl_get_contents($url, $options);

	}

/**
 * 随机生产一个有效的IP地址
 */
	function make_ip()
	{
		return rand(1,255) . '.' . rand(0,255) . '.' . rand(0,255) . '.' . rand(0,255);
	}


/**
 * 友好的输入变量 dump [+ die]
 * @param mixed $var
 * @param bool $die
 */

	function dd( $var , $die = false)
	{
		echo '<pre>';

		var_dump( $var );

		echo '</pre>';

		if(!$die) die;
	}

/**
 * 文件下载
 * @param string $file
 */
	function download_file($file)
	{


		$file = parse_document_root($file);

		if ((isset($file))&&(file_exists($file)))
		{
			header("Content-length: ".filesize($file));
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' .basename( $file ) . '"');
			return readfile("$file");
		}
		else
		{
			return "File Not Found!";
		}
	}



/**
 * 获取当前顶级域名
 */
	function top_domain()
	{
		$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT']=='80' ? '' : ':'.$_SERVER['SERVER_PORT']));

		$host=strtolower($host);
		if(strpos($host,'/')!==false){
			$parse = @parse_url($host);
			$host = $parse['host'];
		}
		$topleveldomaindb=array('com','edu','gov','int','mil','net','org','biz','info','pro','name','museum','coop','aero','xxx','idv','mobi','cc','me','asia','mobi','tech','wang');
		$str='';
		foreach($topleveldomaindb as $v){
			$str.=($str ? '|' : '').$v;
		}
		$matchstr="[^\.]+\.(?:(".$str.")|\w{2}|((".$str.")\.\w{2}))$";
		if(preg_match("/".$matchstr."/ies",$host,$matchs)){
			$domain=$matchs['0'];
		}else{
			$domain=$host;
		}
		return $domain;
	}



/**
 * 把HTMl代码转换为实体,支持多维数组递归转换
 *
 * @param  mixed  $value
 * @return string
 */
	function e($value)
	{
		return is_array($value) ? array_map('e', $value) :  htmlentities($value, ENT_QUOTES, 'UTF-8', false);
	}



/**
 * 检测是否是Ajax提交
 *
 * @return void
 */
	function is_ajax_request()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}

/**
 * 判断是否SSL协议
 * @return boolean
 */
	function is_ssl() {
		if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS'])))
		{
			return true;
		}
		elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] ))
		{
			return true;
		}
		return false;
	}

/**
 * 检测是否是本地IP
 */
	function is_local_ip()
	{
		$serverIP = $_SERVER['SERVER_ADDR'];
		if($serverIP == '127.0.0.1') return true;
		if(strpos($serverIP, '10.60') !== false) return false;
		return !filter_var($serverIP, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
	}

/**
 * 检测是否是邮箱地址
 */
	function is_email($str)
	{
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
	}


/**
 * 检测是否是UTF8字符集
 */
	function is_utf8($string)
	{
		$c    = 0;
		$b    = 0;
		$bits = 0;
		$len  = strlen($string);

		for($i=0; $i<$len; $i++)
		{
			$c = ord($string[$i]);

			if($c > 128)
			{
				if(($c >= 254)) return false;
				elseif($c >= 252) $bits=6;
				elseif($c >= 248) $bits=5;
				elseif($c >= 240) $bits=4;
				elseif($c >= 224) $bits=3;
				elseif($c >= 192) $bits=2;
				else return false;

				if(($i+$bits) > $len) return false;

				while($bits > 1)
				{
					$i++;
					$b=ord($string[$i]);
					if($b < 128 || $b > 191) return false;
					$bits--;
				}
			}
		}
			return true;
		}




/*
 * 列出目录下的所有匹配文件
 * @param string $dir
 * @param string $pattern
 * @return array $files
 */
	function ls($dir, $pattern = '')
	{
		$files = array();
		$dir = realpath($dir);
		if(is_dir($dir)) $files = glob($dir . DIRECTORY_SEPARATOR . '*' . $pattern);
		return empty($files) ? array() : $files;
	}

/**
 * 记录调试信息到文件
 * @param mixed $msg,支持直接传入数组
 * @param string $filename; @default = debug_时间年月日.txt
 * @param string $path; @default = "."; 根目录请传参： “/”
 */
	function log_record( $msg , $filename = '' , $path = '.')
	{

		$msg = is_array($msg) ? var_export($msg, true) : $msg ;

		$message  = '[DATE] '. date('Y-m-d H:i:s') . ' - ';
		$message .= '[REMOTE_ADDR] '. $_SERVER['REMOTE_ADDR'] . ' - ';
		$message .= '[MESSAGE] '. $msg . PHP_EOL;

		$filename = $filename ?
						rtrim($filename,'.txt') . '.txt' :
					'debug_'. date('Ymd') . '.txt' ;

		if( $path == '/' ) $path = $_SERVER['DOCUMENT_ROOT'];


		return file_put_contents( rtrim($path,'/') . '/' . $filename, $message , FILE_APPEND);
	}




/**
 * 递归创建目录
 * @example  mkdirs('/a/b/c/d');
 */
	function mkdirs($dir, $mode=0777)
	{
		if(substr($dir, 0, 1) == '/') $dir = $_SERVER['DOCUMENT_ROOT'] . $dir;

	    if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
	    if (!mkdirs(dirname($dir), $mode)) return FALSE;

	    return @mkdir($dir, $mode);
	}






/**
 * 伪造404页面
 */
	function not_found()
	{
		send_http_status(404);

		exit( '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL ' . $_SERVER["SCRIPT_NAME"] .' was not found on this server.</p></body></html>' );
	}

/**
 * 把“/”解析为根目录
 * @param string $path
 * @return string $path
 */
	function parse_document_root( $path )
	{
		return substr($path,0,1) == '/' ? $_SERVER['DOCUMENT_ROOT'] .  $path : $path;
	}

	
	/**
	 * 生产一个唯一的订单号
	 * @param string $prefix
	 * @return string $order_no
	 */
	function build_order_no( $prefix = '' )
	{
		return $prefix . date('YmdHi').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
	}
	

/**
 * 生产一个可指定长度的随机字符串
 * @param number $length zh - max:1000
 * @param string $type {1:0-9 ; a:a-z ; A:A-Z ; default:mixed}
 * @return string
 */
	function build_random($length = 16, $type = "mix")
	{

		switch ($type) {
			case '1':
			case 'num':
				$pool = '0123456789';
				break;
			case 'a':
			case 'lower':
				$pool = 'abcdefghijklmnopqrstuvwxyz';
				break;
			case 'A':
			case 'upper':
				$pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
			case 'zh':
			case 'cn':
				$pool = simple_chinese(true);
				return join("",array_rand(array_flip($pool), ($length > 1000) ? 1000 : $length));
				break;
			case 'mix':
			default:
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
		}

		return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);

	}

/**
 * 获取中文
 * return string or array/string $chinese
 */
	function simple_chinese($type = false)
	{
		$pool = '们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借';

		if($type)
		{
			preg_match_all("/./su", $pool, $array);

			return $array[0] ;
		}

		return $pool;
	}


/**
 * 去除BOM
 */
	function remove_utf8_bom($string)
	{
		if(substr($string, 0, 3) == pack('CCC', 239, 187, 191)) return substr($string, 3);

		return $string;
	}

/**
 * 去除JS的代码
 */
	function remove_script( $string )
	{
		return preg_replace("'<script(.*?)<\/script>'is", "", $string);
	}


/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
	function redirect($url, $time=0, $msg='')
	{
		//多行URL地址支持
		$url        = str_replace(array("\n", "\r"), '', $url);
		if (empty($msg))
			$msg    = "Automatically jump to {$url} after {$time} seconds.";
		if (!headers_sent()) {
			// redirect
			if (0 === $time) {
				header('Location: ' . $url);
			} else {
				header("refresh:{$time};url={$url}");
				echo($msg);
			}
			exit();
		} else {
			$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if ($time != 0)
				$str .= $msg;
			exit($str);
		}
	}

	/**
	 * 发送HTTP状态
	 * @param integer $code 状态码
	 * @return void
	 */
	function send_http_status($code) {
		$_status = array(
				200 => 'OK',
				301 => 'Moved Permanently',
				302 => 'Moved Temporarily ',
				400 => 'Bad Request',
				403 => 'Forbidden',
				404 => 'Not Found',
				500 => 'Internal Server Error',
				503 => 'Service Unavailable',
		);
		if(isset($_status[$code])) {
			header('HTTP/1.1 '.$code.' '.$_status[$code]);
			// 确保FastCGI模式下正常
			header('Status:'.$code.' '.$_status[$code]);
		}
	}



	/**
	 * 退出,清空session
	 */
	function session_logout()
	{
		session_destroy();
		unset($_SESSION);
	}

/**
 * 增强版 截取字符串，支持多语言，例如中文
 * @param string $string
 * @param int $offset 非负，从0计数
 * @param int $length 截取长度
 * @param string $append 结尾拼接字符，如“...”
 * @return string $string
 * @example str_slice($chinese , 0, 5 ,'...');
 */
	function str_slice($string, $offset = 0, $length = '')
	{
		if('' == $length)
		{
			$length = $offset;
			$offset = 0;
		}
		if(function_exists('mb_substr')) return mb_substr($string, $offset, $length, 'utf-8');

		preg_match_all("/./su", $string, $data);

		return join("", array_slice($data[0],  $offset, $length)) ;
	}


/**
 * 让 str_replace() 只替换一次
 * @param $needle 将要被替换的内容
 * @param $replace 替换后的内容
 * @param $haystack 需要处理的内容
 */
	function str_replace_once($needle, $replace, $haystack) {

	    $pos = strpos($haystack, $needle);
	    
	    if ($pos === false)  return $haystack;

	    return substr_replace($haystack, $replace, $pos, strlen($needle));
	}


	/**
	 * 去除代码中的空白和注释
	 * @param string $content 代码内容
	 * @return string
	 */
	function strip_php_whitespace($content)
	{
		$stripStr   = '';
		//分析php源码
		$tokens     = token_get_all($content);
		$last_space = false;
		for ($i = 0, $j = count($tokens); $i < $j; $i++) {
			if (is_string($tokens[$i])) {
				$last_space = false;
				$stripStr  .= $tokens[$i];
			} else {
				switch ($tokens[$i][0]) {
					//过滤各种PHP注释
					case T_COMMENT:
					case T_DOC_COMMENT:
						break;
						//过滤空格
					case T_WHITESPACE:
						if (!$last_space) {
							$stripStr  .= ' ';
							$last_space = true;
						}
						break;
					case T_START_HEREDOC:
						$stripStr .= "<<<HEREDOC\n";
						break;
					case T_END_HEREDOC:
						$stripStr .= "HEREDOC;\n";
						for($k = $i+1; $k < $j; $k++) {
							if(is_string($tokens[$k]) && $tokens[$k] == ';') {
								$i = $k;
								break;
							} else if($tokens[$k][0] == T_CLOSE_TAG) {
								break;
							}
						}
						break;
					default:
						$last_space = false;
						$stripStr  .= $tokens[$i][1];
				}
			}
		}
		return $stripStr;
	}


	/**
	 * 删除php源码中的注释
	 * @param string $content
	 * @return No Comment content
	 */
	function strip_php_comment($content)
	{
		$stripStr   = '';
		//分析php源码
		$tokens     = token_get_all($content);

		for ($i = 0, $j = count($tokens); $i < $j; $i++) {
			if (is_string($tokens[$i])) {

				$stripStr  .= $tokens[$i];
			} else {
				switch ($tokens[$i][0]) {
					//过滤各种PHP注释
					case T_COMMENT:
					case T_DOC_COMMENT:
						break;

					default:
						$stripStr  .= $tokens[$i][1];
				}
			}
		}
		return $stripStr;
	}

/* 去除html空格与换行 */
	function strip_html_whitespace($content)
	{
		$find    = array("~>\s+<~","~>(\s+\n|\r)~");
		$replace = array('><','>');

        return preg_replace($find, $replace, $content);
	}



/**
 * XML编码
 * @param mixed $data 数据
 * @param string $encoding 数据编码
 * @param string $root 根节点名
 * @return string
 */
	function xml_encode($data, $encoding='utf-8', $root='root') {
		$xml    = '<?xml version="1.0" encoding="' . $encoding . '"?>';
		$xml   .= '<' . $root . '>';
		$xml   .= data_to_xml($data);
		$xml   .= '</' . $root . '>';
		return $xml;
	}

/**
 * 数据XML编码
 * @param mixed $data 数据
 * @return string
 */
	function data_to_xml($data) {
		$xml = '';
		foreach ($data as $key => $val) {
			is_numeric($key) && $key = "item id=\"$key\"";
			$xml    .=  "<$key>";
			$xml    .=  ( is_array($val) || is_object($val)) ? data_to_xml($val) : $val;
			list($key, ) = explode(' ', $key);
			$xml    .=  "</$key>";
		}
		return $xml;
	}




/**
 * 把驼峰命名法转换成下划线拼接命名法
 * @param string $str
 * @return string
 */
	function camecase_to_underline($str)
	{
		$temp_array = array();
		for($i=0;$i<strlen($str);$i++)
		{
			$ascii_code = ord($str[$i]);
			if($ascii_code >= 65 && $ascii_code <= 90){
				if($i == 0){
					$temp_array[] = chr($ascii_code + 32);
				}else{
					$temp_array[] = '_'.chr($ascii_code + 32);
				}
			}else{
				$temp_array[] = $str[$i];
			}
		}
		return implode('',$temp_array);

	}

/**
 * 把下划线拼接命名法转换成驼峰命名法
 * @param string $str
 * @param boolean $ucfirst
 * @return string
 */
	function underline_to_camecase($str , $ucfirst = true)
	{
		$str = explode('_' , $str);

		foreach($str as $key=>$val)
			$str[$key] = ucfirst($val);

		if(!$ucfirst)
			$str[0] = strtolower($str[0]);

		return implode('' , $str);
	}

/**
 * 获取内存使用情况
 * @return string
 */
	function memory_usage()
	{
		return ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';

	}




/**
 * 字节格式化 把字节数格式为 B K M G T 描述的大小
 * @return string
 */
	function byte_format($size, $dec = 2) {
		$unit = array("B", "KB", "MB", "GB", "TB", "PB");
		$pos = 0;
		while ($size >= 1024) {
			$size /= 1024;
			$pos++;
		}
		return round($size, $dec) . " " . $unit[$pos];
	}


/**
 * 检测是否是一个匿名函数
 * @param mixed $var
 * @return boolean
 */
	function is_closure( $var )
	{
		return $var instanceof Closure;
	}

/**
 * Return the default value of the given value.
 *
 * @param  mixed  $value
 * @return mixed
 */
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}

/**
 * 备案号外链,工业和信息化部（工信部）网站地址
 */
	function icp_link()
	{
		return 'http://www.miitbeian.gov.cn';
	}



 ?>
