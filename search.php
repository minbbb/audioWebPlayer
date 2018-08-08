<?php

$pathRoot = 'path/to/music/dir'; //root folder for music.
$dir = $pathRoot.$_POST['path']; //active folder



foreach(explode('/', $dir) as $partURL){ //check url
	if($partURL == ".."){
		echo 'Error of path((';
		return;
	}
}
	$it = new RecursiveDirectoryIterator($dir);
	foreach(new RecursiveIteratorIterator($it) as $file){
		if(stristr($file, $_POST['search']) != FALSE && preg_match('~\.(mp3|wav|wma|aac|flac|m4a|mp4)$~', $file)) {
			$listFiles[] = $file;
		}
	}
foreach($listFiles as $file){
	echo '<li class="list-group-item list-group-item-action" data-path="'.$file.'">'.explode('/', $file)[count(explode('/', $file))-1].'</li><li class="list-group-item list-group-item-action searchItem">'.$file.'</li>';
}
?>