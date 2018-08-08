<!DOCTYPE html> 
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<link rel="stylesheet" href="components/bootstrap/bootstrap.min.css"/>
	<link rel="stylesheet" href="components/fontawesome/css/solid.min.css"/>
	<link rel="stylesheet" href="components/fontawesome/css/fontawesome.min.css"/>
	<link rel="stylesheet" href="css/style.css"/>
	<script src="components/jquery/jquery-3.3.1.min.js"></script>
	<script src="components/bootstrap/bootstrap.min.js"></script>
	<script src="components/audio.js/audio.min.js"></script>
	<title>Music</title>
</head>
<body>
<div id="wrapper" class="col-12 col-md-6">
	<div class="track-name-and-player">
		<div id="track-name">Name of the track</div>
		<div id="track-info"></div>
		<audio id="player" preload="auto"></audio>
		<div class="playerBtns">
			<button type="button" onclick="openMenu()" class="btn btn-dark menuBtn"><i class="fas fa-ellipsis-h"></i></button>
			<button type="button" onclick="previousTrack()" class="btn btn-dark"><i class="fas fa-angle-double-left"></i></button>
			<button type="button" onclick="playPause()" class="btn btn-dark playPauseBtn"><i class="fas fa-play"></i></button>
			<button type="button" onclick="nextTrack()" class="btn btn-dark"><i class="fas fa-angle-double-right"></i></button>
		</div>
	</div>

	<ul class="list-group menuPage">
		<li class="list-group-item list-group-item-action" onclick="openCurrentPlaylist()">Current playlist</li>
		<li class="list-group-item list-group-item-action" onclick="savePlaylist()">Save current playlist</li>
		<li class="list-group-item list-group-item-action" onclick="loadPlaylist()">Load playlist</li>
		<li class="list-group-item list-group-item-action" onclick="openMenuSort()">Sort by
			<ul class="list-group menuSort">
				<li class="list-group-item list-group-item-action" data-sort="modifiedDate">Modified date</li>
				<li class="list-group-item list-group-item-action" data-sort="name">Name</li>
			</ul>
			<ul class="list-group menuSort">
				<li class="list-group-item list-group-item-action" data-order-by="asc">ASC</li>
				<li class="list-group-item list-group-item-action" data-order-by="desc">DESC</li>
			</ul>
		</li>
		<li class="list-group-item list-group-item-action" onclick="ajaxCallFiles()">File manager</li>
		<li class="list-group-item list-group-item-action" onclick="searchFile()">Search</li>
		<li class="list-group-item list-group-item-action" onclick="openPathOfPlayingFile()">
			<div class="file-info">Playing file: none</div>
			<div class="pathOfPlayingFile">Path: none</div>
		</li>
	</ul>

	<ul id="playlist" class="list-group"></ul>
	<div class="up" onclick="toUpPage()"></div>
</div>

<script>
var pathURL = ""; //active folder
var audioPlayer; 
var playlist = []; //playlist
var playId = 0; // id track in playlist
var typeSort = "modifiedDate";
var orderBy = "desc";
var whatIsOpen = "fileManager";

function resizeWindow(){
	$(".track-name-and-player").width($("#wrapper").width());
	$(".scrubber").width($("#wrapper").width() - $(".play-pause").outerWidth() - $(".time").outerWidth() - $(".scrubber").css("margin-left").replace("px", "") - $(".scrubber").css("margin-right").replace("px", ""));
	$(".audiojs").width($("#wrapper").width());
	$("#wrapper").css("margin-top", $(".track-name-and-player").outerHeight() + 20 + "px");
}

function funcBefore(){
	$("#playlist").html("<img class='loading' alt='loading...' src='img/loading.gif'/>");
	$(".up").css("display", "none");
}

function funcSuccess(data){
	$("#playlist").html(data);
	initPlaylist();
	$(".up").css("display", "block");
}

function initPlaylist(){
	$("[data-path]").on("click", function(){
		var elem = this;
		playlist = []; //clear playlist, for a new playlist
		$("[data-path]").map(function(index){
			playlist.push({"path": $(this).attr("data-path"), "name": $(this).text(), "time": $(this).attr("data-time")});
			if($(this).attr("data-path") == $(elem).attr("data-path")){
				playId = index;
				localStorage.setItem('playId', playId);
				selectTrack(index);
			}
			localStorage.setItem('currentPlaylist', JSON.stringify(playlist));
		});
	});
	$("[data-directory]").on("click", function(){
		pathURL = $(this).attr("data-directory");
		localStorage.setItem('path', pathURL);
		ajaxCallFiles();
	});
	$("[data-path]").prepend("<img class='icon' src='img/music.png' alt=''/>");
	$("[data-directory]").prepend("<img class='icon' src='img/folder.png' alt=''/>");
	if(playlist != ""){
		selectionPlayTrackInFileList();
	}
}

audiojs.events.ready(function() {
	audioPlayer = audiojs.createAll({
		trackEnded: function() {
			nextTrack();
		}
	})[0];
});
  
$(document).ready(function() {
	if(localStorage.getItem('sort')){
		typeSort = localStorage.getItem('sort');
	}
	if(localStorage.getItem('order')){
		orderBy = localStorage.getItem('order');
	}
	if(localStorage.getItem('path')){
		pathURL = localStorage.getItem('path');
	}
	if(localStorage.getItem('currentPlaylist')){
		playlist = JSON.parse(localStorage.getItem('currentPlaylist'));
	}
	if(localStorage.getItem('currentPlaylist')){
		playId = localStorage.getItem('playId');
		selectTrack(playId);
		pause();
	}
	$("[data-sort="+typeSort+"]").addClass("selectionSort");
	$("[data-order-by="+orderBy+"]").addClass("selectionSort");
	$.ajax({
		url: "files.php",
		type: "POST",
		data: ({path: pathURL, sort: typeSort, order: orderBy}),
		dataType: "html",
		beforeSend: funcBefore,
		success: function(data){
			funcSuccess(data);
			resizeWindow();
		}
	});
	$("[data-sort]").on("click", function(){
		typeSort = $(this).attr("data-sort");
		localStorage.setItem('sort', $(this).attr("data-sort"));
		$("[data-sort]").removeClass("selectionSort");
		$(this).addClass("selectionSort");
		switch(whatIsOpen){
			case "fileManager":
				ajaxCallFiles();
				break;
			case "currentPlaylist":
				//sort a playlist. Not yet implemented
				openCurrentPlaylist();
				break;
		}
		return false;
	});
	$("[data-order-by]").on("click", function(){
		orderBy = $(this).attr("data-order-by");
		localStorage.setItem('order', $(this).attr("data-order-by"));
		$("[data-order-by]").removeClass("selectionSort");
		$(this).addClass("selectionSort");
		switch(whatIsOpen){
			case "fileManager":
				ajaxCallFiles();
				break;
			case "currentPlaylist":
				//sort a playlist. Not yet implemented
				openCurrentPlaylist();
				break;
		}
		return false;
	});
});

$(window).resize(function() {
	resizeWindow();
});

if ('mediaSession' in navigator){
	navigator.mediaSession.setActionHandler('play', function() { play(); });
	navigator.mediaSession.setActionHandler('pause', function() { pause(); });
	navigator.mediaSession.setActionHandler('nexttrack', function() { nextTrack(); });
	navigator.mediaSession.setActionHandler('previoustrack', function() { previousTrack(); });
}

function nextTrack(){
	if(playId + 1 == playlist.length){
		selectTrack(0);
	}else{
		selectTrack(playId + 1);
	}
}

function previousTrack(){
	if(playId == 0){
		selectTrack(playlist.length - 1);
	}else{
		selectTrack(playId - 1);
	}
}

function selectTrack(idTrack){
	idTrack = +idTrack;
	audioPlayer.load($(playlist).get(idTrack).path);
	play();
	$("#track-name").html($(playlist).get(idTrack).name);
	playId = idTrack;
	localStorage.setItem('playId', playId);
	selectionPlayTrackInFileList();
	resizeWindow();
	if ('mediaSession' in navigator){
		navigator.mediaSession.metadata = new MediaMetadata({
			title: $(playlist).get(idTrack).name
		});
	}
	$(".file-info").text("Playing file: " + playlist[idTrack].name);
	$(".pathOfPlayingFile").text("Path: " + playlist[idTrack].path);
}

function playPause(){
	audioPlayer.playPause();
	if(audioPlayer.playing){
		$(".playPauseBtn").html("<i class='fas fa-pause'></i>");
	}else{
		$(".playPauseBtn").html("<i class='fas fa-play'></i>");
	}
}

function play(){
	audioPlayer.play();
	$(".playPauseBtn").html("<i class='fas fa-pause'></i>");
}

function pause(){
	audioPlayer.pause();
	$(".playPauseBtn").html("<i class='fas fa-play'></i>");
}

function selectionPlayTrackInFileList(){
	$("[data-path]").removeClass("selectionPlayTrack");
	$("[data-path]").each(function(index){
		if($(this).attr("data-path") == playlist[playId].path){
			$($("[data-path]").get(index)).addClass("selectionPlayTrack");
			return false;
		}
	});
}

function openMenu(){
	$(".menuPage").slideToggle(300);
	toUpPage();
}

function openMenuSort(){
	$(".menuSort").slideToggle(300);
}

function openPathOfPlayingFile(){
	$(".pathOfPlayingFile").slideToggle(300);
}

function toUpPage(){
	$('body,html').animate({
		scrollTop: 0
	}, 400);
}

function openCurrentPlaylist(){
	var tempCurrentPlaylist = "";
	$(playlist).each(function(index){
		tempCurrentPlaylist+='<li class="list-group-item list-group-item-action" data-playId="'+index+'" data-path="'+playlist[index].path+'"><img class="icon" src="img/music.png" alt=""/>'+playlist[index].name+'</li>';
	});
	$("#playlist").html(tempCurrentPlaylist);
	$("[data-playId]").on("click", function(){
		selectTrack($(this).attr("data-playId"));
	});
	selectionPlayTrackInFileList();
	whatIsOpen = "currentPlaylist";
}

function ajaxCallFiles(){
	$.ajax({
		url: "files.php",
		type: "POST",
		data: ({path: pathURL, sort: typeSort, order: orderBy}),
		dataType: "html",
		beforeSend: funcBefore,
		success: funcSuccess
	});
	whatIsOpen = "fileManager";
}

function savePlaylist(){
	var namePlaylist = prompt("Playlist name", "name");
	if(namePlaylist != null){
		localStorage.setItem('playlist-' + namePlaylist, JSON.stringify(playlist));
	}
}

function loadPlaylist(){
	var namePlaylist = prompt("Playlist name", "name");
	if(namePlaylist != null){
		playlist = JSON.parse(localStorage.getItem('playlist-' + namePlaylist));
	}
}

function searchFile(){
	//search in the directory and subdirectories in which the user is now. The output is not sorted
	//поиск в директории и поддиректориях в которой сейчас пользователь. Вывод не сортируется
	var nameFile = prompt("Search in "+((pathURL=="")?"/":pathURL), "name");
	if(nameFile != null){
		$.ajax({
			url: "search.php",
			type: "POST",
			data: ({path: pathURL, search: nameFile}),
			dataType: "html",
			beforeSend: funcBefore,
			success: funcSuccess
		});
	}
}
</script>
</body>
</html>