<body background = "bg.jpeg">

<?php
$paras = $_GET;

if(is_array($paras) && count($paras)>0) {
        foreach($paras as $key=>$value){

            //  echo '����:'.$key.' = '.$value.'<br />';

        }

}

            $object = $paras[name];
            $img = $paras[img];

$jsonStr =	file_get_contents("http://apicn.faceplusplus.com/v2/detection/detect?url=".$img."&api_key=8fcd254a1f6e392576fb054870303bfc&api_secret=UQGabPrQk7gyzewH4QAcu5PzQ-dIe4XE&&attribute=glass,pose,gender,age,race,smiling");

//echo $jsonStr;

$replyDic = json_decode($jsonStr);
				$resultStr = "";
				$faceArray = $replyDic->{'face'};

$resultStr .= "ͼ�й���⵽".count($faceArray)."������\n </br>";
				for ($i= 0;$i< count($faceArray); $i++){
					$resultStr .= "</br>��".($i+1)."����\n</br>";
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
				$resultStr .= "���䣺".$minAge."-".$maxAge."��\n</br>";

				// �����Ա�
				if($tempGenger->{'value'} === "Male")
					$resultStr .= "�Ա���\n</br>";	
				else if($tempGenger->{'value'} === "Female")
					$resultStr .= "�Ա�Ů\n</br>";

				// ��������
				if($tempRace->{'value'} === "Asian")
					$resultStr .= "���壺������\n</br>";	
				else if($tempRace->{'value'} === "Male")
					$resultStr .= "���壺������\n</br>";	
				else if($tempRace->{'value'} === "Black")
					$resultStr .= "���壺������\n</br>";	

				// �����۾�
				if($tempGlass->{'value'} === "None")
					$resultStr .= "�۾���ľ���۾�\n</br>";	
				else if($tempGlass->{'value'} === "Dark")
					$resultStr .= "�۾���Ŀ��ī��\n</br>";	
				else if($tempGlass->{'value'} === "Normal")
					$resultStr .= "�۾�����ͨ�۾�\n</br>";	

				//����΢Ц
				$resultStr .= "΢Ц��".round($tempSmiling->{'value'})."%\n</br>";
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
				$resultStr .= "</br>���Ƴ̶ȣ�".round($tempResult)."%\n</br>";

				//����������ƴ�
				$tempSimilarity = $replyDic->{'component_similarity'};
				$tempEye = $tempSimilarity->{'eye'};
				$tempEyebrow = $tempSimilarity->{'eyebrow'};
				$tempMouth = $tempSimilarity->{'mouth'};
				$tempNose = $tempSimilarity->{'nose'};
				
				$resultStr .= "���Ʒ�����\n</br>";
				$resultStr .= "�۾���".round($tempEye)."%\n</br>";
				$resultStr .= "üë��".round($tempEyebrow)."%\n</br>";
				$resultStr .= "��ͣ�".round($tempMouth)."%\n</br>";
				$resultStr .= "���ӣ�".round($tempNose)."%\n</br>";
			}
				
			//���û�м�⵽����
			if($resultStr === "")
				$resultStr = "��Ƭ��ľ������=.=";
			
			echo "<title>";
			echo "����".$minAge."-".$maxAge."�꣬΢Цָ��".round($tempSmiling->{'value'})."��Ҳ�����ɣ�»��װ��";
			echo "</title>";


			echo "<h1>";            
			echo $resultStr;
			echo "</h1>";

echo "<image src =".$img.">";


/*  
            $content = array("MediaId"=>$object->MediaId);
			echo  "<img src=".$content[0].">";
            echo $content[0];
            **/
/**
             $xmlTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";
             
			$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $resultStr);    //΢�ŶԻ���ʽ
            
			 return $result;
**/

?>

        
                

