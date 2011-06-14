<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php 
$homeDir = '/afs/umich.edu/user/'. substr($_SERVER['REMOTE_USER'], 0, 1) .'/'. substr($_SERVER['REMOTE_USER'], 1, 1) .'/'. $_SERVER['REMOTE_USER'];
?>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

<title>Plupload - Queue widget example</title>

<style type="text/css">
	body {
		font-family:Verdana, Geneva, sans-serif;
		font-size:13px;
		color:#333;
		background:url(bg.jpg);
	}
</style>

<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>

<script type="text/javascript" src="plupload/javascript/plupload.js"></script>
<script type="text/javascript" src="plupload/javascript/plupload.gears.js"></script>
<script type="text/javascript" src="plupload/javascript/plupload.silverlight.js"></script>
<script type="text/javascript" src="plupload/javascript/plupload.flash.js"></script>
<script type="text/javascript" src="plupload/javascript/plupload.browserplus.js"></script>
<script type="text/javascript" src="plupload/javascript/plupload.html4.js"></script>
<script type="text/javascript" src="plupload/javascript/plupload.html5.js"></script>

<!-- <script type="text/javascript"  src="http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js"></script> -->

</head>
<body>

<form id="submit-form" method="post" action="dump.php">
	<div>
		<div id="filelist">No runtime found.</div>
		<br />
		<a id="pickfiles" href="javascript:;">[Select files]</a> 
		<a id="uploadfiles" href="javascript:;">[Upload files]</a>
	</div>
</form>

<script type="text/javascript">
// Custom example logic
function $(id) {
	return document.getElementById(id);	
}


var uploader = new plupload.Uploader({
	runtimes : 'gears,html5,flash,silverlight,browserplus',
	browse_button : 'pickfiles',
        multipart: false,
        url : 'webservices/upload?service=ifs&path=<?=$homeDir?>',
	//url : 'upload.php',
	resize : {width : 320, height : 240, quality : 90},
	flash_swf_url : '../js/plupload.flash.swf',
	silverlight_xap_url : '../js/plupload.silverlight.xap',
});

uploader.bind('Init', function(up, params) {
	$('filelist').innerHTML = "<div>Current runtime: " + params.runtime + "</div>";
});

uploader.bind('FilesAdded', function(up, files) {
	for (var i in files) {
		$('filelist').innerHTML += '<div id="' + files[i].id + '">' + files[i].name + ' (' + plupload.formatSize(files[i].size) + ') <b></b></div>';
	}
});

uploader.bind('UploadFile', function(up, file) {
	$('submit-form').innerHTML += '<input type="hidden" name="file-' + file.id + '" value="' + file.name + '" />';
});

uploader.bind('UploadProgress', function(up, file) {
	$(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
});

$('uploadfiles').onclick = function() {
	uploader.start();
	return false;
};

uploader.init();
</script>
</body>
</html>