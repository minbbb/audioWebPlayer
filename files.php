<?php

$pathRoot = 'path/to/music/dir'; //root folder for music.
$dir = $pathRoot.$_POST['path']; //active folder
$typeSort = $_POST['sort'];// modifiedDate | name
$orderBy = $_POST['order'];// asc | desc

foreach(explode('/', $dir) as $partURL){ //check url
	if($partURL == ".."){
		echo 'Error of path((';
		return;
	}
}

$listFiles = scandir($dir);
$listFiles = preg_grep('/^[^.].+/', $listFiles);

switch($typeSort){
	case 'modifiedDate':
		foreach($listFiles as $file){
			$listFilesWithTime[$file] = filemtime($dir.'/'.$file);
		}
		if($orderBy == "asc"){
			asort($listFilesWithTime, SORT_NUMERIC);
		}else{
			arsort($listFilesWithTime, SORT_NUMERIC);
		}
		break;
	case 'name':
	default:
		foreach($listFiles as $file){
			$listFilesWithTime[$file] = $file;
		}
		if($orderBy == "asc"){
			asort($listFilesWithTime);
		}else{
			arsort($listFilesWithTime);
		}
		break;
}

 
if($dir != $pathRoot){
	$dirUrl = substr($_POST['path'], 0, strripos($_POST['path'], "/"));
	echo '<li class="list-group-item list-group-item-action" data-directory="'.$dirUrl.'">..</li>';
}

foreach(array_keys($listFilesWithTime) as $file){
	if(is_dir($dir.'/'.$file)){
		echo '<li class="list-group-item list-group-item-action" data-directory="'.$_POST['path'].'/'.$file.'">'.$file.'</li>';
	}
}
foreach($listFilesWithTime as $file => $time){
	if (preg_match('~\.(mp3|wav|wma|aac|flac|m4a|mp4)$~', $file)) {
		echo '<li class="list-group-item list-group-item-action" data-path="'.$dir.'/'.$file.'" data-time="'.$time.'">'.$file.'</li>';
	}
}
?>