<body background = "bg.jpeg">

<?php
$paras = $_GET;

if(is_array($paras) && count($paras)>0) {
        foreach($paras as $key=>$value){

            //  echo '参数:'.$key.' = '.$value.'<br />';

        }

}

            $object = $paras[name];
            $img = $paras[img];

$jsonStr =	file_get_contents("http://apicn.faceplusplus.com/v2/detection/detect?url=".$img."&api_key=8fcd254a1f6e392576fb054870303bfc&api_secret=UQGabPrQk7gyzewH4QAcu5PzQ-dIe4XE&&attribute=glass,pose,gender,age,race,smiling");

//echo $jsonStr;

$replyDic = json_decode($jsonStr);
				$resultStr = "";
				$faceArray = $replyDic->{'face'};

$resultStr .= "图中共检测到".count($faceArray)."张脸！\n </br>";
				for ($i= 0;$i< count($faceArray); $i++){
					$resultStr .= "</br>第".($i+1)."张脸\n</br>";
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
				$resultStr .= "年龄：".$minAge."-".$maxAge."岁\n</br>";

				// 返回性别
				if($tempGenger->{'value'} === "Male")
					$resultStr .= "性别：男\n</br>";	
				else if($tempGenger->{'value'} === "Female")
					$resultStr .= "性别：女\n</br>";

				// 返回种族
				if($tempRace->{'value'} === "Asian")
					$resultStr .= "种族：黄种人\n</br>";	
				else if($tempRace->{'value'} === "Male")
					$resultStr .= "种族：白种人\n</br>";	
				else if($tempRace->{'value'} === "Black")
					$resultStr .= "种族：黑种人\n</br>";	

				// 返回眼镜
				if($tempGlass->{'value'} === "None")
					$resultStr .= "眼镜：木有眼镜\n</br>";	
				else if($tempGlass->{'value'} === "Dark")
					$resultStr .= "眼镜：目测墨镜\n</br>";	
				else if($tempGlass->{'value'} === "Normal")
					$resultStr .= "眼镜：普通眼镜\n</br>";	

				//返回微笑
				$resultStr .= "微笑：".round($tempSmiling->{'value'})."%\n</br>";
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
				$resultStr .= "</br>相似程度：".round($tempResult)."%\n</br>";

				//具体分析相似处
				$tempSimilarity = $replyDic->{'component_similarity'};
				$tempEye = $tempSimilarity->{'eye'};
				$tempEyebrow = $tempSimilarity->{'eyebrow'};
				$tempMouth = $tempSimilarity->{'mouth'};
				$tempNose = $tempSimilarity->{'nose'};
				
				$resultStr .= "相似分析：\n</br>";
				$resultStr .= "眼睛：".round($tempEye)."%\n</br>";
				$resultStr .= "眉毛：".round($tempEyebrow)."%\n</br>";
				$resultStr .= "嘴巴：".round($tempMouth)."%\n</br>";
				$resultStr .= "鼻子：".round($tempNose)."%\n</br>";
			}
				
			//如果没有检测到人脸
			if($resultStr === "")
				$resultStr = "照片中木有人脸=.=";
			
			echo "<title>";
			echo "年龄".$minAge."-".$maxAge."岁，微笑指数".round($tempSmiling->{'value'})."你也来测测吧，禄宏装饰";
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
             
			$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $resultStr);    //微信对话格式
            
			 return $result;
**/

?>

        
                

