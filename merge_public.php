<?php
$allianceDom = new DOMDocument();
$allianceDom->load($argv[1]);
$allianceDom->preserveWhiteSpace = false;
$allianceDom->formatOutput = true;
$arrayAlliance = getXML($allianceDom->documentElement);

$gameDom = new DOMDocument();
$gameDom->load($argv[2]);
$gameDom->preserveWhiteSpace = false;
$gameDom->formatOutput = true;
$arrayGame = getXML($gameDom->documentElement);

$xml = simplexml_import_dom($gameDom);
$lastNode = $xml->xpath("/resources/public[last()]/@id");
$lastNodeId = (string)$lastNode[0]['id'];

mergePublic($arrayAlliance,$arrayGame,$gameDom);

function getXML($root){
	$array = false;
	if($root->nodeName == 'resources'){
		if($root->hasChildNodes()){
			foreach ($root->childNodes as $childNode) {
				$array[$childNode->nodeName][]=$childNode;
			}
		}
	}
	return $array;
}

function mergePublic($arrayFrom,$arrayTo,$dom){
	
	$dom = merge($arrayFrom,$arrayTo,$dom);
  	$dom->save('ResultPublic.xml');
}

function merge($arrayFrom,$arrayTo,$dom){
	global $lastNodeId;
	$count=0;
	$file = fopen("packagename","w");
	$idArray = array();
	$typeLastNodeArray = array();
	$lastNodeType="";
	$lastNode;
	$categoryNodeArray = array();
	//type category
	foreach($arrayTo["public"] as $toNode){
		foreach ($toNode->attributes as $toAttr) {
			$toNodeName = trim($toAttr->nodeName);
			$toNodeValue = trim($toAttr->nodeValue);
			$tmpToArray[$toNodeName]=$toNodeValue;
		}
		array_push($categoryNodeArray,array($tmpToArray["type"],$lastNode));
		if($lastNodeType != $tmpToArray["type"]){
			if($lastNodeType != ""){
				array_push($typeLastNodeArray,$lastNode);
			}
		}
		$lastNodeType = $tmpToArray["type"];
		$lastNode = $toNode;
	}
	array_push($typeLastNodeArray,$lastNode);
	
	print_r($categoryNodeArray);
	
	//merge public node
	foreach($arrayFrom["public"] as $fromNode){
		foreach ($fromNode->attributes as $fromAttr) {
			$fromNodeName = trim($fromAttr->nodeName);
			$fromNodeValue = trim($fromAttr->nodeValue);
			$tmpFromArray[$fromNodeName]=$fromNodeValue;
		}
		$has=false;
		$idConfilict=false;
		foreach($arrayTo["public"] as $toNode){
			foreach ($toNode->attributes as $toAttr) {
				$toNodeName = trim($toAttr->nodeName);
				$toNodeValue = trim($toAttr->nodeValue);
				$tmpToArray[$toNodeName]=$toNodeValue;
			}
			if($tmpFromArray["type"] == $tmpToArray["type"] && $tmpFromArray["name"] == $tmpToArray["name"]){
				$has=true;
				break;
			}
			if($tmpFromArray["id"] == $tmpToArray["id"]){
				$lastNodeId = $lastNodeId+1;
				$tmpHex = base_convert($lastNodeId,10,16);
				$tmpStr = "0x".$tmpHex;
				$oldId = $tmpFromArray["id"];
				$idConfilict=true;
			}
		}
		if(!$has){
			if($idConfilict){
				array_push($idArray,$tmpStr);
				$fromNode -> setAttribute('id', $tmpStr);
			}else{
				$hasNewConfilict = false;
				foreach($idArray as $idStr){
					if($tmpStr == $idStr){
						$tmpStr = $tmpStr+1;
						$tmpHex = base_convert($tmpStr,10,16);
						$tmpStr = "0x".$tmpHex;
						$hasNewConfilict = true;
						break;
					}
				}
				if($hasNewConfilict){
					array_push($idArray,$tmpStr);
					$fromNode -> setAttribute('id', $tmpStr);
				}
			}
			fwrite($file,$tmpStr.",".$oldId."\n");
			addSubNode($fromNode,$dom);
			$count++;
		}
	}
	fclose($file);
	echo "count delta = ".$count."\n";
	echo "count from = ".count($arrayFrom["public"])."\n";
	echo "count to = ".count($arrayTo["public"])."\n";
	echo "from - to = ".(count($arrayFrom["public"])-count($arrayTo["public"]))."\n";
	return $dom;
}

function getPublicNodeId($node){
	foreach ($node->attributes as $attr) {
		$name = trim($attr->nodeName);
		$value = trim($attr->nodeValue);
		if($name == "id"){
			return $value;
		}
	}
}

function addSubNode($node,$dom){
	$node = $dom->importNode($node, true);
	$root = $dom->documentElement;
	$root->appendChild($node);
}
?>
