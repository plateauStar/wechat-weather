<?php
	//获得参数 signature nonce token timestamp echostr
    $nonce     = $_GET['nonce'];
    $token     = 'haha';
    $timestamp = $_GET['timestamp'];
    $echostr   = $_GET['echostr'];
    $signature = $_GET['signature'];
    //形成数组，然后按字典序排序
    $array = array();
    $array = array($nonce, $timestamp, $token);
    sort($array);
    //拼接成字符串,sha1加密 ，然后与signature进行校验
    $str = sha1( implode( $array ) );
    if( $str == $signature && $echostr ){
        //第一次接入weixin api接口的时候
        echo  $echostr;
        exit;
    }


else{

        //1.获取到微信推送过来post数据（xml格式）
        $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
        //2.处理消息类型，并设置回复类型和内容
        $postObj = simplexml_load_string( $postArr );
        //判断该数据包是否是订阅的事件推送
        if( strtolower( $postObj->MsgType) == 'event'){
            //如果是关注 subscribe 事件
            if( strtolower($postObj->Event == 'subscribe') ){
                //回复用户消息(纯文本格式)
                $toUser   = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
                $time     = time();
                $msgType  =  'text';
                $content  = '欢迎关注我们的微信公众账号,此公众号为测试公众号！';
                $template = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <Content><![CDATA[%s]]></Content>
                                </xml>";
                $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
                echo $info;
            }
        }
      
      //判断该数据包是否是文本消息
        if( strtolower( $postObj->MsgType) == 'text'){
             //接受文本信息
    		$content = $postObj->Content;
             //回复用户消息(纯文本格式)
                $toUser   = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
                $time     = time();
                $msgType  =  'text';
               //	$content  = $content.":"."\n"."晴转多云，气温5 ~ -1℃，偏北风4-5级"."\n"."您出门注意保暖";
          		//接入接口
          		if(strstr($content,"天气") )//包含天气关键字，才有自动回复，否则没有
				{
              		 $name= mb_substr($content , 0 , 2 , 'utf-8');//获取中文字符“南京天气”前两个中文    
   					 $getcontent = file_get_contents("http://62.234.215.193/city?name={$name}");
   					 if(empty($getcontent))
          				{ $content = '暂无天气数据';}
   					 else{
           				 $getresult = json_decode($getcontent);
            			//dump($getresult);
           				 if($getresult->code == 404||$getresult->info->status == 1002 )
              					 {   $content = '暂无天气数据';}            
            			else{
                            $content = "{$getresult->info->data->city}:\n";
                			$content .= "{$getresult->info->data->forecast[0]->date}\n";
                 			$content .= "{$getresult->info->data->forecast[0]->type}\n";
                			$content .= "{$getresult->info->data->forecast[0]->high},    {$getresult->info->data->forecast[0]->low}\n";
               				$content .= "{$getresult->info->data->ganmao}";
            				}
        				}
                     $template = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <Content><![CDATA[%s]]></Content>
                                </xml>";
                $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
                echo $info;
				}
          
               
        }
    }
    