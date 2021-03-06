<?php
/*
	王梧权
*/

define("TOKEN", "wander");

$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    //验证签名
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
            exit;
        }
    }

    //响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R ".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
             
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
            $this->logger("T ".$result);
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    //接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $content = "欢迎关注禄宏装饰 ";
                $content .= (!empty($object->EventKey))?("\n来自二维码场景 ".str_replace("qrscene_","",$object->EventKey)):"";
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
            case "SCAN":
                $content = "扫描场景 ".$object->EventKey;
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "COMPANY":
						$content = array();
                        $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://sxlhjz.com");
                        break;
                    default:
                        $content = "点击菜单：".$object->EventKey;
                        break;
                }
                break;
            case "LOCATION":
                $content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                break;
            case "VIEW":
                $content = "跳转链接 ".$object->EventKey;
                break;
            case "MASSSENDJOBFINISH":
                $content = "消息ID：".$object->MsgID."，结果：".$object->Status."，粉丝数：".$object->TotalCount."，过滤：".$object->FilterCount."，发送成功：".$object->SentCount."，发送失败：".$object->ErrorCount;
                break;
            default:
                $content = "receive a new event: ".$object->Event;
                break;
        }
        if(is_array($content)){
            if (isset($content[0])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }

        return $result;
    }

    //接收文本消息
    private function receiveText($object)
    {
        $keyword = trim($object->Content);
        //多客服人工回复模式
        if (strstr($keyword, "您好") || strstr($keyword, "你好") || strstr($keyword, "在吗")){
            $result = $this->transmitService($object);
        }
        //自动回复模式
        else{
            if (strstr($keyword, "文本")){
                $content = "这是个文本消息";
            }else if (strstr($keyword, "单图文")){
                $content = array();
                $content[] = array("Title"=>"单图文标题",  "Description"=>"单图文内容", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://sxlhjz.com");
            }else if (strstr($keyword, "图文") || strstr($keyword, "多图文")){
                $content = array();
                $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://sxlhjz.com");
                $content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://sxlhjz.com");
                $content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://sxlhjz.com");
            }else if (strstr($keyword, "音乐")){
                $content = array();
                $content = array("Title"=>"最炫民族风", "Description"=>"歌手：凤凰传奇", "MusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3", "HQMusicUrl"=>"http://bbss.shangdu.com/Editor/UserFiles/File/4982/1869/9600/1241790819536.mp3");
            }else{
                $content = date("Y-m-d H:i:s",time())."\n".$object->FromUserName."\n王梧权最爱你啦";
            }
            
            if(is_array($content)){
                if (isset($content[0]['PicUrl'])){
                    $result = $this->transmitNews($object, $content);
                }else if (isset($content['MusicUrl'])){
                    $result = $this->transmitMusic($object, $content);
                }
            }else{
                $result = $this->transmitText($object, $content);
            }
        }
        
       
        return $result;
    }

    //接收图片消息
    private function receiveImage($object)
    {
        $imageUrl = $object->PicUrl;      //"http%3A%2F%2Ffaceplusplus.com%2Fstatic%2Fimg%2Fdemo%2F1.jpg";   //$object->PicUrl;
        //$imageUrl = array("MediaId"=>$object->MediaId);
        $result = $this->face($object, $imageUrl);
				
       // $content = array("MediaId"=>$object->MediaId);
       // $result = $this->transmitImage($object, $content);
        
			return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "你刚才说的是：".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }

        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
    <MediaId><![CDATA[%s]]></MediaId>
</Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
    <MediaId><![CDATA[%s]]></MediaId>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
</Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        $itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复多客服消息
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 10000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
		
		private function face($object,$img)
		{
         	//$imageUrl = array("MediaId"=>$object->MediaId);
       				
       		// $content = array("MediaId"=>$object->MediaId);
            
            $content[] = array("Title"=>"点击这里查看有惊喜哦ooo",  "Description"=>"看看你的微笑指数，年龄估算`(*∩_∩*)′", "PicUrl"=>$img, "Url" =>"http://1.15102228127.sinaapp.com/info.php?name=".$object."&img=".$img);        
            $result = $this->transmitNews($object, $content);
            
            return $result;
            
				// face++ 链接
            //$jsonStr = file_get_contents("http://apicn.faceplusplus.com/v2/detection/detect?api_key=8fcd254a1f6e392576fb054870303bfc&api_secret=UQGabPrQk7gyzewH4QAcu5PzQ-dIe4XE&url=http%3A%2F%2Ffaceplusplus.com%2Fstatic%2Fimg%2Fdemo%2F1.jpg&attribute=glass,pose,gender,age,race,smiling");
            /**	
            $jsonStr =	file_get_contents("http://apicn.faceplusplus.com/v2/detection/detect?url=".$img."&api_key=8fcd254a1f6e392576fb054870303bfc&api_secret=UQGabPrQk7gyzewH4QAcu5PzQ-dIe4XE&&attribute=glass,pose,gender,age,race,smiling");
				//return $jsonStr;
        
            
				$replyDic = json_decode($jsonStr);
				$resultStr = "";
				$faceArray = $replyDic->{'face'};

				$resultStr .= "图中共检测到".count($faceArray)."张脸！\n";
				for ($i= 0;$i< count($faceArray); $i++){
					$resultStr .= "第".($i+1)."张脸\n";
				$tempFace = $faceArray[$i];
				// 获取所有属性
				$tempAttr = $tempFace->{'attribute'};

				// 年龄：包含年龄分析结果
				// value的值为一个非负整数表示估计的年龄, range表示估计年龄的正负区间
				$tempAge = $tempAttr->{'age'};

				// 性别：包含性别分析结果
				// value的值为Male/Female, confidence表示置信度
				$tempGenger	= $tempAttr->{'gender'};	

				// 种族：包含人种分析结果
				// value的值为Asian/White/Black, confidence表示置信度
				$tempRace = $tempAttr->{'race'};		

				// 微笑：包含微笑程度分析结果
				//value的值为0-100的实数，越大表示微笑程度越高
				$tempSmiling = $tempAttr->{'smiling'};

				// 眼镜：包含眼镜佩戴分析结果
				// value的值为None/Dark/Normal, confidence表示置信度
				$tempGlass = $tempAttr->{'glass'};	

				// 造型：包含脸部姿势分析结果
				// 包括pitch_angle, roll_angle, yaw_angle
				// 分别对应抬头，旋转（平面旋转），摇头
				// 单位为角度。
				$tempPose = $tempAttr->{'pose'};
				
				//返回年龄
				$minAge = $tempAge->{'value'} - $tempAge->{'range'};
				$minAge = $minAge < 0 ? 0 : $minAge;
				$maxAge = $tempAge->{'value'} + $tempAge->{'range'};
				$resultStr .= "年龄：".$minAge."-".$maxAge."岁\n";

				// 返回性别
				if($tempGenger->{'value'} === "Male")
					$resultStr .= "性别：男\n";	
				else if($tempGenger->{'value'} === "Female")
					$resultStr .= "性别：女\n";

				// 返回种族
				if($tempRace->{'value'} === "Asian")
					$resultStr .= "种族：黄种人\n";	
				else if($tempRace->{'value'} === "Male")
					$resultStr .= "种族：白种人\n";	
				else if($tempRace->{'value'} === "Black")
					$resultStr .= "种族：黑种人\n";	

				// 返回眼镜
				if($tempGlass->{'value'} === "None")
					$resultStr .= "眼镜：木有眼镜\n";	
				else if($tempGlass->{'value'} === "Dark")
					$resultStr .= "眼镜：目测墨镜\n";	
				else if($tempGlass->{'value'} === "Normal")
					$resultStr .= "眼镜：普通眼镜\n";	

				//返回微笑
				$resultStr .= "微笑：".round($tempSmiling->{'value'})."%\n";
			}	
			
			if(count($faceArray) === 2){
				// 获取face_id
				$tempFace = $faceArray[0];
				$tempId1 = $tempFace->{'face_id'};
				$tempFace = $faceArray[1];
				$tempId2 = $tempFace->{'face_id'};


				// face++ 链接
				$jsonStr =
					file_get_contents("https://apicn.faceplusplus.com/v2/recognition/compare?api_secret=ViX19uvxkT_A0a6d55Hb0Q0QGMTqZ95f&api_key=5eb2c984ad24ffc08c352bdb53ee52f8&face_id2=".$tempId2 ."&face_id1=".$tempId1);
				$replyDic = json_decode($jsonStr);

				//取出相似程度
				$tempResult = $replyDic->{'similarity'};
				$resultStr .= "相似程度：".round($tempResult)."%\n";

				//具体分析相似处
				$tempSimilarity = $replyDic->{'component_similarity'};
				$tempEye = $tempSimilarity->{'eye'};
				$tempEyebrow = $tempSimilarity->{'eyebrow'};
				$tempMouth = $tempSimilarity->{'mouth'};
				$tempNose = $tempSimilarity->{'nose'};
				
				$resultStr .= "相似分析：\n";
				$resultStr .= "眼睛：".round($tempEye)."%\n";
				$resultStr .= "眉毛：".round($tempEyebrow)."%\n";
				$resultStr .= "嘴巴：".round($tempMouth)."%\n";
				$resultStr .= "鼻子：".round($tempNose)."%\n";
			}


			//如果没有检测到人脸
			if($resultStr === "")
				$resultStr = "照片中木有人脸=.=";
            
            
             $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
            // $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $resultStr);    //微信对话格式
            
                     
            $content[] = array("Title"=>"点击这里查看有惊喜哦ooo",  "Description"=>"看看你的微笑指数，年龄估算`(*∩_∩*)′", "PicUrl"=>$img, "Url" =>"http://1.15102228127.sinaapp.com/info.php?name=".$object->FromUserName."&info=".$resultStr);        
            $result = $this->transmitNews($object, $content);
            
            return $result;
                        
            //return $resultStr;
            **/
		}
       
    
}
?>