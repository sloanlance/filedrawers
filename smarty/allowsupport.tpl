{include file="masthead.tpl"}
{include file="sidebar.tpl"}
{include file="filelist.tpl"}

<div id=allowsupport>

<h2 class=content-text >
Allowing Support Access to Your Home Directory
</h2>

<p class=content-text >
The departmental support group(s) available to help you with your
home directory (based on your University Affiliation) are listed below.
Give
<a href="">access</a>
to, or remove it from, these groups as needed.
</p>

{*
 * List U-M Affiliations.
 *}

{if $affiliations ne null}
<h2 class=content-text >
Your U-M Affiliations:
</h2>
<table class="affiliations">
{foreach from=$affiliations item="affiliation"}
<tr bgcolor="{cycle values="#ffffff,#f1f1f1" name="affiliations"}">
<td>
{$affiliation.name}
</td>
</tr>
{/foreach}
</table>
{/if}

{if $supportgroups ne null}
<h2 class=content-text>
Your U-M Departmental Support Groups:
</h2>
<form method=post action={$smarty.server.PHP_SELF} name="changesupport">
<input type="hidden" name="give_support" value="">
<input type="hidden" name="remove_support" value="">
<table class=supportgroups>

<tr>
<th width="50%">
Departmental Support Group
</th>
<th width="20%">
Permitted
</th>
<th width="30%">
Change Support Access
</th>
</tr>

{foreach from=$supportgroups item="supportgroup" name="supportgroups"}
<tr bgcolor="{cycle values="#ffffff,#f1f1f1" name="supportgroups"}">
<td>
{if $supportgroup.affiliated eq false && $supportgroup.permitted eq true}
<span class="supportgroups_warn">
{$supportgroup.name}
</span>
{else}
{$supportgroup.name}
{/if}
</td>
<td>
{if $supportgroup.permitted eq true}
<span class="supportgroups_warn">
YES
</span>
{else}
NO
{/if}
</td>
<td>
{if $supportgroup.permitted eq true}
<a href="javascript:remove_submit('{$supportgroup.name}');">
<img src="/images/support_remove.gif" width="158" height="22" border="0">
</a>
{else}
<a href="javascript:give_submit('{$supportgroup.name}');">
<img src="/images/support_give.gif" width="158" height="22" border="0">
</a>
{/if}
</td>
</tr>
{/foreach}
</table>
</form>
{/if}

{* scam XXX remove once location bar status starts working *}
{if $notifyMsg ne null}
<p class=allow-results>
{$notifyMsg}
</p>
{/if}

</div>

{include file="footer.tpl"}
