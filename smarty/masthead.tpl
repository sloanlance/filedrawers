<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<noscript><meta http-equiv=refresh content="0; url=/noscript.php"></noscript>
<title>mfile: {$trouser_title}</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
{foreach from="$stylesheets" item="stylesheet"}
<link href="{$stylesheet}" rel="stylesheet" type="text/css" />
{/foreach}
<!--[if IE 5]> 
 <link href="/ie5specific.css" rel="stylesheet" type="text/css"> 
 <![endif]-->
<!--[if IE 6]> 
 <link href="/ie6specific.css" rel="stylesheet" type="text/css"> 
 <![endif]-->
{foreach from="$javascripts" item="script"}
<script language="JavaScript" type="text/javascript" src="{$script}"></script>
{/foreach}
{$js_vars}
</head>
<body onload="startPage('{$notifyMsg}','{$warnUser}');">
<div class="masthead">
{include file="banner.tpl"}
{include file="infobar.tpl"}
</div>
