<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Favorites Panel</title>
<link href="favorites.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
{literal}
body {
	background-color: #ffffcc;
}
{/literal}
-->
</style>
</head>
<body>
<div id="menu"><a href="viewfavorites.php?target={$target}" class="sel">View</a> | <a href="addfavorites.php?target={$target}">Add</a> | <a href="renamefavorites.php?target={$target}">Rename</a> | <a href="deletefavorites.php?target={$target}">Delete</a></div>
<br />
Go to a favorite location:<br />
{foreach key=name item=link from=$favorites}
<a href="./?path={$link}" target="_parent">{$name}</a>&nbsp;<br />
{foreachelse}
<p class="instruct">You have not stored any favorite locations. Select 'Add' to add the
    present location.</p>
{/foreach}
</body>
</html>
