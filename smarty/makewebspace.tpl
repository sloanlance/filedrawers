{include file="masthead.tpl"}
{include file="sidebar.tpl"}

<div id=makewebspace>

<h2 class=content-text >
Make-webspace
</h2>

<p class=content-text >
This utility helps prepare your UofM webspaces.

For new webspaces, this utility creates a webspace directory to hold
your webspace files, and an example
XHTML 1.1 UofM Home Page for the new webspace.

This utility also sets the correct webspace directory permissions for both
your new and your existing webspace directories.

We have scanned the various
<a href="http://www.itd.umich.edu/itcsdocs/s4033/">pts groups</a> 
to determine which webspaces you may have administrative privileges
over. If you feel that you're missing an entry, please contact the <a
href="http://www.itd.umich.edu/accounts/">ITCS Accounts Office</a>.
</p>

{*
 * If there are any results from a webspace preparation,
 * post them here.
 *}

{if $prep_results ne null}
<table class=prep-results>
{foreach from=$prep_results item="prep_result"}
{if $prep_result.success eq 0 }
{assign var="problem" value="true"}
{/if}
<tr bgcolor="#ffffff">
<td>
Webspace {$prep_result.site} {$prep_result.result}
</td>
</tr>
{/foreach}
</table>
{/if}

{if $problem eq true }
<p class=prep-results>
There have been problems preparing one or more of your webspaces.
For further help, please contact the ITCS accounts office at:
<a href="http://www.itcs.umich.edu/accounts/">
http://www.itcs.umich.edu/accounts/</a>
.
</p>
{/if}

{*
 *  If there are any webspaces that can be prepared, display a form
 *  to select them here.
 *}
{if $public_unprepared ne null || $private_unprepared ne null}
<h2 class=content-text >
Please select all webspaces you'd like to prepare.
</h2>
<p>
</p>
<form method=post action={$smarty.server.PHP_SELF}>
<table class=content-text>

{*
 * Display Public data.
 *}
{foreach from=$public_unprepared item="space" name="public_unprepared"
         key="name" }
{if $smarty.foreach.public_unprepared.first eq true}
<tr>
<th colspan=3>
Public
</th>
</tr>
{/if}
<tr bgcolor="{cycle values="#ffffff,#f1f1f1" name="iw_cycle_1"}">
<td width="5%">
<input type="checkbox" name="{$name}">
</td>
<td width="35%">
{$space.name}
</td>
<td width="60%">
{$space.status_readable}
</td>
</tr>
{foreachelse}
{/foreach}


{*
 * Display private data.
 *}
{foreach from=$private_unprepared item="space" name="private_unprepared"
         key="name"}
{if $smarty.foreach.private_unprepared.first eq true}
<tr>
<th colspan=3>
Private
</th>
</tr>
{/if}
<tr bgcolor="{cycle values="#ffffff,#f1f1f1" name="iw_cycle_2"}">
<td width="5%">
<input type="checkbox" name="{$name}">
</td>
<td width="35%">
{$space.name}
</td>
<td width="60%">
{$space.status_readable}
</td>
</tr>
{foreachelse}
{/foreach}

</table>
<input type="submit" value="prepare selected webspaces">
</form>
{else}
<p class=content-text >
Currently, all of your potential webspaces have been prepared.
</p>
{/if}

{*
 * Display all prepared webspaces here.
 *}
{if $public_prepared ne null || $private_prepared ne null}
<table class=content-text>

<h2 class=content-text >
Your prepared webspaces:
</h2>

{*
 * Display Public data.
 *}
{foreach from=$public_prepared item="space" name="public_prepared" }
{if $smarty.foreach.public_prepared.first eq true}
<tr>
	<th colspan=3>Public</th>
</tr>
{/if}
<tr bgcolor="{cycle values="#ffffff,#f1f1f1" name="iw_cycle_3"}">
	<td width="40%"><a href="/?path={$space.path|escape:"url"}">{$space.name}</a></td>
	<td width="60%"><a href="{$space.url}">{$space.url}</a></td>
</tr>
{foreachelse}
{/foreach}

{*
 * Display private data.
 *}
{foreach from=$private_prepared item="space" name="private_prepared" }
{if $smarty.foreach.private_prepared.first eq true}
<tr>
	<th colspan=3>Private</th>
</tr>
{/if}
<tr bgcolor="{cycle values="#ffffff,#f1f1f1" name="iw_cycle_4"}">
	<td width="40%"><a href="/?path={$space.path|escape:"url"}">{$space.name}</a></td>
	<td width="60%"><a href="{$space.url}">{$space.url}</a></td>
</tr>
{foreachelse}
{/foreach}
</table>
{else}
<h2 class=content-text >
You have no prepared webspaces.
</h2>
{/if}

{*
    Display "further assistance" information here.
*}
<p class=content-text>
Further assistance is available on how to
<a class=content-text href="http://www.umich.edu/~umweb/how-to/homepage.html">
Create your own UM web page
</a>
.
</p>

</div>

{include file="footer.tpl"}
