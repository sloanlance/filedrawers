{include file="masthead.tpl"}
{include file="sidebar.tpl"}
{include file="filelist.tpl"}

<div id=adminsupport>

<h2 class=content-text >
Support Group Access administration.
</h2>

<p class=content-text >
Administrate mappings from U-M Affiliations to U-M Departmental
Support groups below.
</p>

<p class=content-text >
Users with the listed U-M Affiliation will be given the option
to allow support to the associated Departmental Support Group.
Add a new mapping by entering a new U-M Affliation and Departmental
Support Group into the form below and then clicking the "add new mapping"
button. The Departmental Support Group must be valid PTS group.
</p>

<p class=content-text >
Mappings may be deleted by selecting the "Delete?" checkbox and then
clicking the "delete selected mapping" button. A mapping may only
be deleted by the user who originally submitted it.
</p>

<p class=content-text >
Only one Departmental Support Group is allowed per U-M Affiliation.
If you wish to change an Affiliation's mapping, delete the previous
mapping, using the "delete selected mapping" button and then re-add
the mapping using the "add new mapping" button.
</p>

{if $mappings ne null}
<h2 class=content-text>
U-M Affiliation to Support Group Mapping:
</h2>

<form method=post action={$smarty.server.PHP_SELF} name="adminsupport">
<input type="hidden" name="give_support" value="">
<input type="hidden" name="remove_support" value="">

<table class=supportmap>
<tr>
<th width="40%">
U-M Affiliation
</th>
<th width="40%">
Departmental Support Group
</th>
<th width="10%">
Submitter
</th>
<th width="10%">
Delete?
</th>
</tr>

{foreach from=$mappings item="mapping" name="mappings"}
<tr bgcolor="{cycle values="#ffffff,#f1f1f1" name="mappings"}">
<td>
{$mapping.affiliation_name}
</td>
<td>
{$mapping.name}
</td>
<td>
{$mapping.submitter}
</td>
<td>
{if $uniqname eq $mapping.submitter}
<input type="checkbox" name="delete_{$mapping.id}">
{/if}
</td>
</tr>
{/foreach}
<tr>
<td>
<input type="text" name="new_map_aff" value="" class="textin" >
</td>
<td>
<input type="text" name="new_map_group" value="" class="textin" >
</td>
<td>
</td>
</tr>
</table>
<input type="submit" name="delete_mapping" value="delete selected mapping">
<input type="submit" name="add_mapping" value="add new mapping">
</form>
{/if}

</div>

{include file="footer.tpl"}
