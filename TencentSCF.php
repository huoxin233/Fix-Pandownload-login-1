<?php
function reqq($url,$data,$ua,$bduss){
	$num = count($bduss);
	for($i=0;$i<$num;$i++){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url."&devuid=O|".md5($bduss[$i])."&origin=dlna");//&origin=dlna 无需Range头即可下载
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_USERAGENT,$ua);
	curl_setopt($curl, CURLOPT_COOKIE, "BDUSS=".$bduss[$i]);
	//curl_setopt($curl, CURLOPT_POST,1)
	//curl_setopt($curl, CURLOPT_POSTFIELDS,$data)
	
	$data222 = curl_exec($curl);
	curl_close($curl);
	//return $data222;
	if( strpos($data222, '31045')===false && strpos($data222, 'qdall01')===false)
	{
		return $data222;
	}
	}
	return "Er";
}
function hander($ev,$test){
    $set=0;//设置公告是否开启 1开启 0关闭
    $gg="测试公告";//测试公告
    $passkey="sswordnedd";//即为key
    $vip_bduss = array("GNHQmtSSHJ6NW9lVVJ2RUdMVnl5NG9VdHEzRTRaOWlUMmFnRERleTZSbTR2ZkpmSUFBQUFBJCQAAAAAAAAAAAEAAABXLSO7uvC68LbuMzQ5AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAALgwy1-4MMtfN","SVIP2","SVIP3");
    //$ua1="netdisk;P2SP;2.2.60.26";
    //$ua1="netdisk;2.2.51.6;netdisk;10.0.63;PC;android-android;QTP/1.0.32.2";
    $ua1="netdisk;P2SP;3.0.0.3;netdisk;11.5.3;PC;PC-Windows;android-android;11.0;JSbridge4.4.0";
    $xiancheng="8";//最大线程
	$event = json_decode(json_encode($ev), true);
	$_GET = $event['queryString'];
	
	$a=$_GET['method'];
	if($a=="isok"){
		$meage=array(
		'code'=> 200,
		'messgae'=> '维护中',//code不为200时显示的错误提示
		'open'=>$set,
		'gg'=>$gg
		);
		return json_encode($meage);
	}
	if($a=="request"){
		$user=$_GET['code'];
		$data=$_GET['data'];
		$data1=base64_decode($data);
		if ($user === $passkey){
			$ret1 = reqq("https://d.pcs.baidu.com/rest/2.0/pcs/file?method=locatedownload&app_id=250528&ver=4.0&vip=2".$data1,"",$ua1,$vip_bduss);
			if ($ret1=="Er"){
				$meage=array(
				'code'=> 405,
				'messgae'=> '账号过期或黑号，请提醒更换'
				);
				return json_encode($meage);
			}
			$ret1=base64_encode($ret1);
			$meage=array(
			'code'=> 200,
			'messgae'=> 'Done',
			'data'=> $ret1,
			'split'=> $xiancheng,
			'ua' => $ua1
			);
			return json_encode($meage);
		}
		$meage=array(
			'code'=> 404,
			'messgae'=> 'User not found',
			'inpu'=> 'key过期或错误，重新输入'
		);
		return json_encode($meage);
	}
}
function main_handler($event, $context) {
	$html = hander($event,$context);
    return [
        'isBase64Encoded' => false,
        'statusCode' => 200,
        'headers' => [ 'Content-Type' => 'text/html' ],
        'body' => $html
    ];
}
?>
