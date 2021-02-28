<?php
$set=0;//设置公告是否开启 1开启 0关闭
$gg="测试公告";//测试公告
$passkey="sswordnedd";//为了防止注入这里限制到了10位
$vip_bduss = array("GNHQmtSSHJ6NW9lVVJ2RUdMVnl5NG9VdHEzRTRaOWlUMmFnRERleTZSbTR2ZkpmSUFBQUFBJCQAAAAAAAAAAAEAAABXLSO7uvC68LbuMzQ5AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAALgwy1-4MMtfN","SVIP2","SVIP3");


//$ua1="netdisk;P2SP;2.2.60.26";
//$ua1="netdisk;2.2.51.6;netdisk;10.0.63;PC;android-android;QTP/1.0.32.2";
$ua1="netdisk;P2SP;3.0.0.3;netdisk;11.5.3;PC;PC-Windows;android-android;11.0;JSbridge4.4.0";

$xiancheng="8";//最大线程
class MyDB extends SQLite3
{
	function __construct()
	{
		$this->open('user111.db');//这里最好自己改下
	}
}
function reqq($url,$data,$ua,$bduss){
	
	//strpos($ret, '31045')!==false || strpos($ret, 'qdall01')!==false
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

$a=$_GET['method'];
if($a=="isok"){
	$meage=array(
	'code'=> 200,
	'messgae'=> '维护中',//code不为200时显示的错误提示
	'open'=>$set,
	'gg'=>$gg
	);
	echo json_encode($meage);
	exit ;
}
if($a=="regist"){
	$user=$_GET['code'];
	$time=$_GET['time'];
	$pass=$_GET['pass'];
	if(strlen($user) > 8 || strlen($passkey)>10 || $passkey!=$pass){
		
		$meage=array(
		'code'=> 403,
		'messgae'=> 'Forbiiden'
		);
		echo json_encode($meage);
		exit;
	}
	$db = new MyDB();
	$ret = $db->query('INSERT INTO user VALUES ("'.$user.'",'.(int)$time.');');
	
	$meage=array(
	'code'=> 200,
	'messgae'=> 'Registe OK'
	);
	echo json_encode($meage);
	exit ;
	
}
if($a=="request"){
	$user=$_GET['code'];
	$data=$_GET['data'];
	$data1=base64_decode($data);
	$db = new MyDB();
	
	$ret = $db->query('
	SELECT * FROM user WHERE code="'.$user.'";
	');
	while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
		//echo $data1;
		$ret1 = reqq("https://d.pcs.baidu.com/rest/2.0/pcs/file?method=locatedownload&app_id=250528&ver=4.0&vip=2".$data1,"",$ua1,$vip_bduss);
		/**if(strpos($ret, '31045')!==false || strpos($ret, 'qdall01')!==false){
			$meage=array(
			'code'=> 405,
			'messgae'=> '账号过期或黑号，请提醒更换，此次不计数',
			);
			echo json_encode($meage);
			exit ;
		}**/
		//echo $ret1;
		if ($ret1=="Er"){
			$meage=array(
			'code'=> 405,
			'messgae'=> '账号过期或黑号，请提醒更换，此次不计数',
			);
			echo json_encode($meage);
			exit ;
		}
		$ret1=base64_encode($ret1);
		//echo $ret1;
		$tt= (int)$row['time']-1;
		if($tt==0 || $tt <0){
			$ret = $db->exec('DELETE FROM user WHERE code="'.$user.'";');
		}
		else{
			$ret = $db->exec('UPDATE user SET time='.$tt.' WHERE code="'.$user.'";');
		}
		$meage=array(
		'code'=> 200,
		'messgae'=> 'Done',
		'data'=> $ret1,
		'split'=> $xiancheng,
		'ua' => $ua1
		);
		echo json_encode($meage);
		exit ;
	}
	$meage=array(
		'code'=> 404,
		'messgae'=> 'User not found'
	);
	echo json_encode($meage);
	exit ;
}
if($a=="createdb"){
	
	$db = new MyDB();
	
	$ret = $db->query('
	CREATE TABLE user(
	code TEXT,
	time INT);
	');
	if($ret)
	{
		$meage=array(
		'code'=> 200,
		'messgae'=> 'Create OK'
		);
	}
	else 
	{
		$meage=array(
		'code'=> 403,
		'messgae'=> 'Exists or No promission'
		);
	}
	echo json_encode($meage);
	exit ;
}
?>
