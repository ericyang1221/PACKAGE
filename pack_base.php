<?php
// $gamePath = '/Users/hui.yang/Downloads/tmpapk/test_game_ori/AndroidManifest.xml';
$gameDom = new DOMDocument();
$gameDom->load($argv[2]);
$gameDom->preserveWhiteSpace = false;
$gameDom->formatOutput = true;
$arrayGame = getManifest($gameDom->documentElement);

// $alliancePath = '/Users/hui.yang/Downloads/tmpapk/test_alliance_mobage/AndroidManifest.xml';
$allianceDom = new DOMDocument();
$allianceDom->load($argv[1]);
$allianceDom->preserveWhiteSpace = false;
$allianceDom->formatOutput = true;
$arrayAlliance = getManifest($allianceDom->documentElement);

mergeManifest($arrayAlliance,$arrayGame,$gameDom);

function getManifest($root){
	$array = false;
	if($root->nodeName == 'manifest'){
		$array[$root->nodeName][]=$root->attributes;
	}
	if($root->hasChildNodes()){
		foreach ($root->childNodes as $childNode) {
			if ($childNode->nodeType != XML_TEXT_NODE) {
				$array[$childNode->nodeName][]=$childNode;
				if($childNode->nodeName == 'application'){
					if($childNode->hasChildNodes()){
						foreach ($childNode->childNodes as $appSubNode) {
							if ($appSubNode->nodeType != XML_TEXT_NODE) {
								$array[$appSubNode->nodeName][]=$appSubNode;
							}
						}
					}
				}
			}
		}
	}
	return $array;
}

function mergeManifest($arrayFrom,$arrayTo,$dom){
	foreach(array_keys($arrayFrom) as $key){
		$dom = merge($key,$arrayFrom,$arrayTo,$dom);
	}
	$dom = mergePackageName($arrayFrom,$arrayTo,$dom);
	$dom = mergeApplicationName($arrayFrom,$arrayTo,$dom);
//  	echo $dom->saveXML()."\n";
  	$dom->save('ResultAndroidManifest.xml');
}

function merge($element,$arrayFrom,$arrayTo,$dom){
	if($element=='activity'||$element=='receiver'||$element=='service'||$element=='uses-permission'||$element=='meta-data'){
		if(count($arrayFrom[$element])>0){
			echo "--------------add $element nodes----------------------\n";
			foreach($arrayFrom[$element] as $node){
				$array = false;
				foreach ($node->attributes as $attr) {
					$nodeName = trim($attr->nodeName);
					$nodeValue = trim($attr->nodeValue);
					if($nodeName=='android:name'&&($nodeValue=='com.mobage.android.sample.MobageNativeSDKSampleActivity'
					||$nodeValue=='com.mobage.android.sample.Test1Activity'
					||$nodeValue=='com.mobage.android.sample.Test2Activity'
					||$nodeValue=='com.mobage.android.MobageActivity'
					||$nodeValue=='com.mobage.android.cn.shortcut.ShortMobageActivity'
					||$nodeValue=='com.mobage.android.cn.downloadmanager.DmActivity'
					||$nodeValue=='com.mobage.android.cn.downloadmanager.DmSettingActivity'
					||$nodeValue=='com.mobage.android.cn.autoupdate.DownloadService'
					||$nodeValue=='com.denachina.androidpn.client.NotificationService'
					||$nodeValue=='com.mobage.android.iab.MobageBillingService'
					||$nodeValue=='com.mobage.android.cn.downloadmanager.DmService'
					||$nodeValue=='com.mobage.android.cn.autoupdate.InstallSDKReceiver'
					||$nodeValue=='com.mobage.android.C2DMBaseReceiver'
					||$nodeValue=='com.denachina.androidpn.client.BootReceiver'
					||$nodeValue=='com.mobage.android.cn.autoupdate.OnlineChangeReceiver'
					||$nodeValue=='com.mobage.android.iab.BillingReceiver'
					||$nodeValue=='com.mobage.android.cn.downloadmanager.ConnectionChangeReceiver'
					||$nodeValue=='com.denachina.account.weakaccount.WeakAccountActivity'
					||$nodeValue=='com.mobage.android.cn.download.DmActivity'
					)){
						continue 2;
					}
					$array[$nodeName] = $nodeValue;
  		  		}
  		  		$has = false;
  		  		if(array_key_exists($element,$arrayTo)){
  		  			foreach($arrayTo[$element] as $ga){
    					$destArray = false;
    					foreach ($ga->attributes as $attr) {
							$destArray[trim($attr->nodeName)] = trim($attr->nodeValue);
						}
						if($array['android:name']==$destArray['android:name']){
							$has = true;
						}
    				}
  		  		}
    			if(!$has){
    				if($element=='activity'||$element=='receiver'||$element=='service'||$element=='meta-data'){
    					addApplicationSubNode($node,$dom);
    				}else if($element=='uses-permission'){
    					addSubNode($node,$dom);
    				}
    			}
			}
			echo '------------------------------------------------------'."\n";
		}
	}
	else if($element=='permission'){
		if(count($arrayFrom[$element])>0){
			echo "--------------add $element nodes----------------------\n";
			foreach($arrayFrom[$element] as $node){
				$array = false;
				foreach ($node->attributes as $attr) {
					$array[trim($attr->nodeName)] = trim($attr->nodeValue);
  		  		}
  		  		$has = false;
  		  		$protectionLevel = false;
  		  		$oldProtectionLevel = false;
  		  		if(array_key_exists($element,$arrayTo)){
  		  			foreach($arrayTo[$element] as $ga){
    					$destArray = false;
    					foreach ($ga->attributes as $attr) {
							$destArray[trim($attr->nodeName)] = trim($attr->nodeValue);
						}
						if($array['android:name']==$destArray['android:name']){
							$has = true;
							if($array['android:protectionLevel']!=$destArray['android:protectionLevel']){
								$protectionLevel = $array['android:protectionLevel'];
								$oldProtectionLevel = $destArray['android:protectionLevel'];
							}
						}
    				}
  		  		}
    			if(!$has){
    				addSubNode($node,$dom);
    			}else{
    				if($protectionLevel){
    					$ga->setAttribute('android:protectionLevel',$protectionLevel);
    					echo 'Modify==='.getAttributeAndroidName($node) ."===protectionLevel===FROM===$oldProtectionLevel===TO===$protectionLevel\n";
    				}
    			}
			}
			echo '------------------------------------------------------'."\n";
		}
	}else if($element=='uses-feature'){
		if(count($arrayFrom[$element])>0){
			echo "--------------add $element nodes----------------------\n";
			foreach($arrayFrom[$element] as $node){
				$array = false;
				foreach ($node->attributes as $attr) {
					$array[trim($attr->nodeName)] = trim($attr->nodeValue);
  		  		}
  		  		$has = false;
  		  		$required = false;
  		  		$oldRequired = false;
  		  		$glEsVersion = false;
  		  		$oldGlEsVersion = false;
  		  		if(array_key_exists($element,$arrayTo)){
  		  			foreach($arrayTo[$element] as $ga){
    					$destArray = false;
    					foreach ($ga->attributes as $attr) {
							$destArray[trim($attr->nodeName)] = trim($attr->nodeValue);
						}
						if($array['android:name']==$destArray['android:name']){
							$has = true;
							if($array['android:required']!=$destArray['android:required']){
								$required = $array['android:required'];
								$oldRequired = $destArray['android:required'];
							}
							if($array['android:glEsVersion']!=$destArray['android:glEsVersion']){
								$glEsVersion = $array['android:glEsVersion'];
								$oldGlEsVersion = $destArray['android:glEsVersion'];
							}
						}
    				}
  		  		}
    			if(!$has){
    				addSubNode($node,$dom);
    			}else{
    				if($required){
    					$ga->setAttribute('android:required',$required);
    					echo 'Modify==='.getAttributeAndroidName($node) ."===android:required===FROM===$oldRequired===TO===$required\n";
    				}
    				if($required){
    					$ga->setAttribute('android:glEsVersion',$glEsVersion);
    					echo 'Modify==='.getAttributeAndroidName($node) ."===android:glEsVersion===FROM===$oldGlEsVersion===TO===$glEsVersion\n";
    				}
    			}
    			
			}
			echo '------------------------------------------------------'."\n";
		}
	}
	return $dom;
}

function addApplicationSubNode($node,$dom){
	$node = $dom->importNode($node, true);
	$root = $dom->documentElement;
	if($root->hasChildNodes()){
		foreach ($root->childNodes as $childNode) {
			if ($childNode->nodeType != XML_TEXT_NODE) {
				if($childNode->nodeName == 'application'){
					$childNode->appendChild($node);
					echo getAttributeAndroidName($node) ."\n";
				}
			}
		}
	}
}

function addSubNode($node,$dom){
	$node = $dom->importNode($node, true);
	$root = $dom->documentElement;
	$root->appendChild($node);
	echo getAttributeAndroidName($node) ."\n";
}

function getAttributeAndroidName($node){
	$attrContent = false;
	foreach ($node->attributes as $attr) {
		if($attr->nodeName == 'android:name'){
			return $attr->nodeValue;
		}
		$attrContent = $attrContent.$attr->nodeName.'="'.$attr->nodeValue.'" ';
	}
	return '<'.$node->nodeName.' '.$attrContent.'/>';
}

function mergePackageName($arrayFrom,$arrayTo,$dom){
	foreach ($arrayFrom['manifest'][0] as $attr){
		if($attr->nodeName == 'package'){
			$fromPackageName = $attr->nodeValue;
			break;
		}
	}
	$root = $dom->documentElement;
	foreach ($root->attributes as $attr){
		if($attr->nodeName == 'package'){
			$originToPackageName = $attr->nodeValue;
			$attr->nodeValue = $fromPackageName;
			$editedToPackageName = $attr->nodeValue;
			break;
		}
	}
	echo "--------------merge packageName----------------------\n";
	echo 'change packageName from '.$originToPackageName.' to '.$editedToPackageName."\n";
	echo '------------------------------------------------------'."\n";
	$file = fopen("packagename","w");
	fwrite($file,$originToPackageName."\n");
	fwrite($file,$editedToPackageName."\n");
	fclose($file);
	return $dom;
}

function mergeApplicationName($arrayFrom,$arrayTo,$dom){
	foreach ($arrayFrom['application'][0]->attributes as $attr){
		if($attr->nodeName == 'android:name'){
			$fromApplicationName = $attr->nodeValue;
			break;
		}
	}
	echo "--------------merge applicationName----------------------\n";
	if($fromApplicationName != ""){
		$root = $dom->documentElement;
		$hasApplicationName = false;
		foreach ($root->childNodes as $childNode) {
			if ($childNode->nodeType != XML_TEXT_NODE) {
				if($childNode->nodeName == 'application'){
					$destApplicationNode = $childNode;
					break;
				}
			}
		}
		foreach ($destApplicationNode->attributes as $attr){
			if($attr->nodeName == 'android:name'){
				$originToApplicationName = $attr->nodeValue;
				$attr->nodeValue = $fromApplicationName;
				$editedToApplicationName = $attr->nodeValue;
				$hasApplicationName = true;
				break;
			}
		}
		if($hasApplicationName){
			echo 'change packageName from '.$originToApplicationName.' to '.$editedToApplicationName."\n";
		}else{
			$destApplicationNode->setAttribute('android:name',$fromApplicationName);
			echo 'add applicationName '.$fromApplicationName."\n";
		}
	}
	echo '------------------------------------------------------'."\n";
	return $dom;
}
?>
