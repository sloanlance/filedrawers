<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Favorites Panel</title>
<link href="/css/favorites.css" rel="stylesheet" type="text/css" />
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
<body onload="{$loadCmds}">
<div id="menu"><a href="/viewfavorites.php?target={$target}">View</a> | <a href="/addfavorites.php?target={$target}" class="sel">Add</a> | <a href="/renamefavorites.php?target={$target}">Rename</a> | <a href="/deletefavorites.php?target={$target}">Delete</a></div>
{if $usedFavs < $maxFavs}
<form method="post" name="fav_editor" action="/viewfavorites.php?target={$target}">
    <p>Enter a name and click 'Add' to add the current location to your favorites.</p>
	<input type="hidden" name="formKey" value="{$formKey}" />
    <input type="text" name="selectedItems" size="18" />
	<input type="submit" name="command" value="Add" />
</form>
{else}
<p>You have stored the maximum number of favorite locations.</p>
<p align="center"><a href="/viewfavorites.php?target={$target}">OK</a></p>
{/if}
</body>
</html>
