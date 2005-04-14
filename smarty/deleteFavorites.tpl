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
<form method="post" name="fav_editor" action="viewfavorites.php?target={$target}">
    <div id="menu"><a href="viewfavorites.php?target={$target}">View</a> | <a href="addfavorites.php?target={$target}">Add</a> | <a href="renamefavorites.php?target={$target}">Rename</a> | <a href="deletefavorites.php?target={$target}" class="sel">Delete</a></div>
    <p>Select the favorites to delete and press 'Delete'.</p>
    <table>
        {foreach key=name item=link from=$favorites}
        <tr>
            <td>{$name}</td>
            <td><input type="checkbox" name="selectedItems[]" value="{$name}" /></td>
        </tr>
        {foreachelse}
        <tr>
            <td class="instruct" colspan="2">You have not stored any favorite locations.</td>
        </tr>
        {/foreach}
    </table>
    <p align="center">
        <input type="submit" name="command" value="Delete" />
    </p>
</form>
</body>
</html>
