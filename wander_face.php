<?php
/*
	����Ȩ
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
    //��֤ǩ��
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

    //��Ӧ��Ϣ
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R ".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
             
            //��Ϣ���ͷ���
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

    //�����¼���Ϣ
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $content = "��ӭ��ע»��װ�� ";
                $content .= (!empty($object->EventKey))?("\n���Զ�ά�볡�� ".str_replace("qrscene_","",$object->EventKey)):"";
                break;
            case "unsubscribe":
                $content = "ȡ����ע";
                break;
            case "SCAN":
                $content = "ɨ�賡�� ".$object->EventKey;
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "COMPANY":
						$content = array();
                        $content[] = array("Title"=>"��ͼ��1����", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://sxlhjz.com");
                        break;
                    default:
                        $content = "����˵���".$object->EventKey;
                        break;
                }
                break;
            case "LOCATION":
                $content = "�ϴ�λ�ã�γ�� ".$object->Latitude.";���� ".$object->Longitude;
                break;
            case "VIEW":
                $content = "��ת���� ".$object->EventKey;
                break;
            case "MASSSENDJOBFINISH":
                $content = "��ϢID��".$object->MsgID."�������".$object->Status."����˿����".$object->TotalCount."�����ˣ�".$object->FilterCount."�����ͳɹ���".$object->SentCount."������ʧ�ܣ�".$object->ErrorCount;
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

    //�����ı���Ϣ
    private function receiveText($object)
    {
        $keyword = trim($object->Content);
        //��ͷ��˹��ظ�ģʽ
        if (strstr($keyword, "����") || strstr($keyword, "���") || strstr($keyword, "����")){
            $result = $this->transmitService($object);
        }
        //�Զ��ظ�ģʽ
        else{
            if (strstr($keyword, "�ı�")){
                $content = "���Ǹ��ı���Ϣ";
            }else if (strstr($keyword, "��ͼ��")){
                $content = array();
                $content[] = array("Title"=>"��ͼ�ı���",  "Description"=>"��ͼ������", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://sxlhjz.com");
            }else if (strstr($keyword, "ͼ��") || strstr($keyword, "��ͼ��")){
                $content = array();
                $content[] = array("Title"=>"��ͼ��1����", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://sxlhjz.com");
                $content[] = array("Title"=>"��ͼ��2����", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://sxlhjz.com");
                $content[] = array("Title"=>"��ͼ��3����", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://sxlhjz.com");
            }else if (strstr($keyword, "����")){
                $content = array();
                $content = array("Title"=>"���������", "Description"=>"���֣���˴���", "MusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3", "HQMusicUrl"=>"http://bbss.shangdu.com/Editor/UserFiles/File/4982/1869/9600/1241790819536.mp3");
            }else{
                $content = date("Y-m-d H:i:s",time())."\n".$object->FromUserName."\n����Ȩ�����";
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

    //����ͼƬ��Ϣ
    private function receiveImage($object)
    {
        $imageUrl = $object->PicUrl;      //"http%3A%2F%2Ffaceplusplus.com%2Fstatic%2Fimg%2Fdemo%2F1.jpg";   //$object->PicUrl;
        //$imageUrl = array("MediaId"=>$object->MediaId);
        $result = $this->face($object, $imageUrl);
				
       // $content = array("MediaId"=>$object->MediaId);
       // $result = $this->transmitImage($object, $content);
        
			return $result;
    }

    //����λ����Ϣ
    private function receiveLocation($object)
    {
        $content = "�㷢�͵���λ�ã�γ��Ϊ��".$object->Location_X."������Ϊ��".$object->Location_Y."�����ż���Ϊ��".$object->Scale."��λ��Ϊ��".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //����������Ϣ
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "��ղ�˵���ǣ�".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }

        return $result;
    }

    //������Ƶ��Ϣ
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //����������Ϣ
    private function receiveLink($object)
    {
        $content = "�㷢�͵������ӣ�����Ϊ��".$object->Title."������Ϊ��".$object->Description."�����ӵ�ַΪ��".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //�ظ��ı���Ϣ
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

    //�ظ�ͼƬ��Ϣ
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

    //�ظ�������Ϣ
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

    //�ظ���Ƶ��Ϣ
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

    //�ظ�ͼ����Ϣ
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

    //�ظ�������Ϣ
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

    //�ظ���ͷ���Ϣ
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

    //��־��¼
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
				// face++ ����
            //$jsonStr = file_get_contents("http://apicn.faceplusplus.com/v2/detection/detect?api_key=8fcd254a1f6e392576fb054870303bfc&api_secret=UQGabPrQk7gyzewH4QAcu5PzQ-dIe4XE&url=http%3A%2F%2Ffaceplusplus.com%2Fstatic%2Fimg%2Fdemo%2F1.jpg&attribute=glass,pose,gender,age,race,smiling");
				 $jsonStr =	file_get_contents("http://apicn.faceplusplus.com/v2/detection/detect?url=".$img."&api_key=8fcd254a1f6e392576fb054870303bfc&api_secret=UQGabPrQk7gyzewH4QAcu5PzQ-dIe4XE&&attribute=glass,pose,gender,age,race,smiling");
				//return $jsonStr;
				$replyDic = json_decode($jsonStr);
				$resultStr = "";
				$faceArray = $replyDic->{'face'};

				$resultStr .= "ͼ�й���⵽".count($faceArray)."������\n";
				for ($i= 0;$i< count($faceArray); $i++){
					$resultStr .= "��".($i+1)."����\n";
				$tempFace = $faceArray[$i];
				// ��ȡ��������
				$tempAttr = $tempFace->{'attribute'};

				// ���䣺��������������
				// value��ֵΪһ���Ǹ�������ʾ���Ƶ�����, range��ʾ�����������������
				$tempAge = $tempAttr->{'age'};

				// �Ա𣺰����Ա�������
				// value��ֵΪMale/Female, confidence��ʾ���Ŷ�
				$tempGenger	= $tempAttr->{'gender'};	

				// ���壺�������ַ������
				// value��ֵΪAsian/White/Black, confidence��ʾ���Ŷ�
				$tempRace = $tempAttr->{'race'};		

				// ΢Ц������΢Ц�̶ȷ������
				//value��ֵΪ0-100��ʵ����Խ���ʾ΢Ц�̶�Խ��
				$tempSmiling = $tempAttr->{'smiling'};

				// �۾��������۾�����������
				// value��ֵΪNone/Dark/Normal, confidence��ʾ���Ŷ�
				$tempGlass = $tempAttr->{'glass'};	

				// ���ͣ������������Ʒ������
				// ����pitch_angle, roll_angle, yaw_angle
				// �ֱ��Ӧ̧ͷ����ת��ƽ����ת����ҡͷ
				// ��λΪ�Ƕȡ�
				$tempPose = $tempAttr->{'pose'};
				
				//��������
				$minAge = $tempAge->{'value'} - $tempAge->{'range'};
				$minAge = $minAge < 0 ? 0 : $minAge;
				$maxAge = $tempAge->{'value'} + $tempAge->{'range'};
				$resultStr .= "���䣺".$minAge."-".$maxAge."��\n";

				// �����Ա�
				if($tempGenger->{'value'} === "Male")
					$resultStr .= "�Ա���\n";	
				else if($tempGenger->{'value'} === "Female")
					$resultStr .= "�Ա�Ů\n";

				// ��������
				if($tempRace->{'value'} === "Asian")
					$resultStr .= "���壺������\n";	
				else if($tempRace->{'value'} === "Male")
					$resultStr .= "���壺������\n";	
				else if($tempRace->{'value'} === "Black")
					$resultStr .= "���壺������\n";	

				// �����۾�
				if($tempGlass->{'value'} === "None")
					$resultStr .= "�۾���ľ���۾�\n";	
				else if($tempGlass->{'value'} === "Dark")
					$resultStr .= "�۾���Ŀ��ī��\n";	
				else if($tempGlass->{'value'} === "Normal")
					$resultStr .= "�۾�����ͨ�۾�\n";	

				//����΢Ц
				$resultStr .= "΢Ц��".round($tempSmiling->{'value'})."%\n";
			}	
			
			if(count($faceArray) === 2){
				// ��ȡface_id
				$tempFace = $faceArray[0];
				$tempId1 = $tempFace->{'face_id'};
				$tempFace = $faceArray[1];
				$tempId2 = $tempFace->{'face_id'};


				// face++ ����
				$jsonStr =
					file_get_contents("https://apicn.faceplusplus.com/v2/recognition/compare?api_secret=ViX19uvxkT_A0a6d55Hb0Q0QGMTqZ95f&api_key=5eb2c984ad24ffc08c352bdb53ee52f8&face_id2=".$tempId2 ."&face_id1=".$tempId1);
				$replyDic = json_decode($jsonStr);

				//ȡ�����Ƴ̶�
				$tempResult = $replyDic->{'similarity'};
				$resultStr .= "���Ƴ̶ȣ�".round($tempResult)."%\n";

				//����������ƴ�
				$tempSimilarity = $replyDic->{'component_similarity'};
				$tempEye = $tempSimilarity->{'eye'};
				$tempEyebrow = $tempSimilarity->{'eyebrow'};
				$tempMouth = $tempSimilarity->{'mouth'};
				$tempNose = $tempSimilarity->{'nose'};
				
				$resultStr .= "���Ʒ�����\n";
				$resultStr .= "�۾���".round($tempEye)."%\n";
				$resultStr .= "üë��".round($tempEyebrow)."%\n";
				$resultStr .= "��ͣ�".round($tempMouth)."%\n";
				$resultStr .= "���ӣ�".round($tempNose)."%\n";
			}


			//���û�м�⵽����
			if($resultStr === "")
				$resultStr = "��Ƭ��ľ������=.=";
            
            
             $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $resultStr);
        return $result;
            
            
            
            //return $resultStr;
		}
}
?>