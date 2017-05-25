<?php
/**
 * Created by PhpStorm.
* User: Administrator
* Date: 2017/5/17
* Time: 10:42
*/

class XmlToArray{
	/**
	* @param $file  xml文件地址或路径
	* @param $version xml的版本号
	* @param $encode  xml的编码
	*/
	public function __construct($file='',$version='1.0',$encode='utf-8'){
		$this->file = $file;
		$this->version = $version;
		$this->encode = $encode;
	}


	/**
	 * @name 将xml文件内容转为array
	 * @author jia253<jia253@qq.com> by 2017-05-19
	 */
	public function xmlArray(){
		$domComment = new \DOMDocument($this->version,$this->encode);
		if(!file_exists($this->file)){
			echo $this->file.'文件不存在';die;
		}
		$xml = file_get_contents($this->file);
		//如果是utf-8的可以忽略下面这个步骤 以及后续的转化
		$xml = mb_convert_encoding( $xml, "HTML-ENTITIES",$this->version); //将gbk的编码转成HTML-ENTITIES
		//加载xml字符串源
		$domComment->loadXML($xml);

		$list = [];
		$index = 0;
		//获取根节点 遍历
		foreach ($domComment->getElementsByTagName('item') as $node){
			foreach ($node->childNodes as $chlidNode){
				if(isset($chlidNode->tagName)){
					$key = mb_convert_encoding($chlidNode->tagName,'utf-8','HTML-ENTITIES');
					if($chlidNode->childNodes->length > 1){
						$list['item'.$index][$key] = $this->digui($chlidNode,$list['item'.$index][$key]);
					}else{
						if(isset($chlidNode->tagName)){
							$value = mb_convert_encoding($chlidNode->nodeValue,'utf-8','HTML-ENTITIES');
							$list['item'.$index][$key] = $value;
						}
					}
				}
			}
			if($index > 2){
				break;
			}
			$index++;
		}
		return $list;
	}

	/**
	 * @name 递归处理xml内容各个节点
	 * @param $node
	 * @param $chlidArr 引用数组
	 * @return mixed
	 */
	function digui($node,&$chlidArr){
		$lastChild = $node->lastChild; //获取此节点的最后一个子节点 用来判断什么时候返回组装好的数组
		$item = 0;
		//遍历子节点
		foreach ($node->childNodes as $chlidNode){
			//每一个节点对应一个有tagName的对象和无#text类型的对象  故做此判断
			if($chlidNode->tagName){
				$chlidKey = mb_convert_encoding($chlidNode->tagName,'utf-8','HTML-ENTITIES');
				if($chlidNode->childNodes->length > 1){
					//表示有子节点 递归开始
					if(isset($chlidArr[$chlidKey]) && !empty($chlidArr[$chlidKey])){
						//有可能有同名节点多个展示  故做此处理
						$chlidKey.='=>'.$item;
					}
					$item++;
					$this->digui($chlidNode,$chlidArr[$chlidKey]);
				}else{
					$chlidValue =  mb_convert_encoding($chlidNode->nodeValue,'utf-8','HTML-ENTITIES');
					$chlidArr[$chlidKey] = $chlidValue;
				}
			}else{
				//当是最后一个子节点时  返回组装好的数组
				if($chlidNode === $lastChild){
					return $chlidArr;
				}
			}
		}
	}
}







