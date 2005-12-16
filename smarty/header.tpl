<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
{if $redirect ne true}
<noscript><meta http-equiv=refresh content="0; url=https://{$smarty.server.SERVER_NAME}/scripterror.php"></noscript>
{/if}
<title>mfile: {$filedrawers_title}</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
{foreach from="$stylesheets" item="stylesheet"}
<link href="{$stylesheet}" rel="stylesheet" type="text/css" />
{/foreach}
{foreach from="$javascripts" item="script"}
<script language="JavaScript" type="text/javascript" src="{$script}"></script>
{/foreach}
<script language="JavaScript" type="text/JavaScript">
{$js_vars}
var displayfileman = {$js_displayfileman};
</script>
</head>
