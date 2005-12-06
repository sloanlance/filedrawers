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
<body>
<form method="post" name="fav_editor" action="/viewfavorites.php?target={$target}">
    <div id="menu"><a href="/viewfavorites.php?target={$target}">View</a> | <a href="/addfavorites.php?target={$target}">Add</a> | <a href="/renamefavorites.php?target={$target}" class="sel">Rename</a> | <a href="/deletefavorites.php?target={$target}">Delete</a></div>
    {foreach key=name item=link from=$favorites}
    <input type="text" name="selectedItems[{$name}]" value="{$name}" size="18" maxlength="25" />
    <br />
    {foreachelse}
    <p class="instruct">You have not stored any favorite locations.</p>
    {/foreach}
    <p align="center">
        <input type="submit" name="command" value="Rename" />
    </p>
</form>
</body>
</html>
