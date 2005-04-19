<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>mFile: afs file management</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="fileman.css" rel="stylesheet" type="text/css" />
<!--[if IE 5]> 
 <link href="ie5specific.css" rel="stylesheet" type="text/css"> 
 <![endif]-->
<!--[if IE 6]> 
 <link href="ie6specific.css" rel="stylesheet" type="text/css"> 
 <![endif]-->
<script language="JavaScript" type="text/javascript" src="js/filemanage.js"></script>
<script language="JavaScript" type="text/JavaScript">
var path       = "{$path}";
var foldername = "{$folderName}";
var folderIcon = '{$folderIcon}';
var homepath   = "{$homePath}";
var sid        = "{$sid}";
var readonly   = {$readonly};

files=new Array();
{$folderContents}
</script>
</head>
<body onload="startPage('{$notifyMsg}','{$warnUser}');">
<div class="masthead">
    <div id="itcsBanner">
        <div id="date">
            {$smarty.now|date_format:"%A"}
            <strong>
            {$smarty.now|date_format:"%B %e"}
            </strong>
            {$smarty.now|date_format:"%Y"}
        </div>
        <div id="itcs"><a href="http://www.itcs.umich.edu"><strong>Information Technology
                    Central Services</strong></a> at the <a href="http://www.umich.edu">University
                    of Michigan</a></div>
    </div>
    <div id="banner">
        <h1>mFile: afs file management</h1>
        <ul id="menubar">
            {if $homeSelected}
            <li><a href="/trouser/" class="active">Home</a></li>
            {else}
            <li><a href="/trouser/">Home</a></li>
            {/if}
            {if $webSelected}
            <li><a href="/trouser/?path={$homePath}/Public/html" class="active">Web Sites</a></li>
            {else}
            <li><a href="/trouser/?path={$homePath}/Public/html">Web Sites</a></li>
            {/if}
            <li><a href="https://weblogin.umich.edu/cgi-bin/logout?http://mfile.umich.edu/">Logout</a></li>
        </ul>
    </div>
    <div id="infoBar">
        <ul id="infoMenu">
            <li><a href="/trouser/?path={$parentPath}">Go Up &uarr;</a></li>
            <li><a href="/trouser/?path={$path}">Refresh</a></li>
        </ul>
        <div id="notifyArea"> Location: <span id="location" style="width: 150px; overflow: hidden; white-space: nowrap;">
            {$location}
            </span>
            <form name="changeDirForm" id="changeDirForm" action="" method="get" style="display: inline;">
                <input type="button" name="changeDir" id="changeDir" value="Change" onclick="activateLocationCtrl(true);" />
                <input type="text" name="path" id="newLoc" size="40" maxlength="100" value="{$path}" style="display: none;" />
                <input type="submit" name="goChange" id="goChange" value="Go" style="display: none;" />
                <input type="button" name="cancelChange" id="cancelChange" value="Cancel" style="display: none;" onclick="activateLocationCtrl(false);" />
            </form>
        </div>
    </div>
</div>