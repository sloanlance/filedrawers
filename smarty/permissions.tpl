<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Share Members</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="fileman.css" rel="stylesheet" type="text/css" />
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
<body onload="">
<form name="acl" id="acl" method="post" action="">
    <div id="permissionsBox">
        <table>
            <thead>
                <tr>
                    <th width="185px">Normal Rights for
                        {$folder}</th>
                    <th>lookup</th>
                    <th>read</th>
                    <th>write</th>
                    <th>insert</th>
                    <th>delete</th>
                    <th>lock</th>
                    <th>admin</th>
                </tr>
            </thead>
            <tbody>
                {foreach key=group item=item from=$normal}
                <tr>
                    <td>{$group}<input type="hidden" name="nrm[{$group}][]" value=" " /></td>
                    {foreach key=right item=set from=$item}
                    {if $set}
                    <td><input type="checkbox" name="nrm[{$group}][]" value="{$right}" checked="checked" /></td>
                    {else}
                    <td><input type="checkbox" name="nrm[{$group}][]" value="{$right}" /></td>
                    {/if}
                    {/foreach}
                </tr>
                {/foreach}
                <tr>
                    <td><input type="text" name="nrm_add_name" width="40" maxlength="40" value="" /></td>
                    <td><input type="checkbox" name="nrm[nrm_add][]" value="l" /></td>
                    <td><input type="checkbox" name="nrm[nrm_add][]" value="r" /></td>
                    <td><input type="checkbox" name="nrm[nrm_add][]" value="w" /></td>
                    <td><input type="checkbox" name="nrm[nrm_add][]" value="i" /></td>
                    <td><input type="checkbox" name="nrm[nrm_add][]" value="d" /></td>
                    <td><input type="checkbox" name="nrm[nrm_add][]" value="k" /></td>
                    <td><input type="checkbox" name="nrm[nrm_add][]" value="a" /></td>
                </tr>
            </tbody>
            <thead>
                <tr>
                    <th width="185px">Negative Rights for
                        {$folder}</th>
                    <th>lookup</th>
                    <th>read</th>
                    <th>write</th>
                    <th>insert</th>
                    <th>delete</th>
                    <th>lock</th>
                    <th>admin</th>
                </tr>
            </thead>
            <tbody>
                {foreach key=group item=item from=$negative}
                <tr>
                    <td>{$group}<input type="hidden" name="neg[{$group}][]" value=" " /></td>
                    {foreach key=right item=set from=$item}
                    {if $set}
                    <td><input type="checkbox" name="neg[{$group}][]" value="{$right}" checked="checked" /></td>
                    {else}
                    <td><input type="checkbox" name="neg[{$group}][]" value="{$right}" /></td>
                    {/if}
                    {/foreach}
                </tr>
                {/foreach}
                <tr>
                    <td><input type="text" name="neg_add_name" width="40" maxlength="40" value="" /></td>
                    <td><input type="checkbox" name="neg[neg_add][]" value="l" /></td>
                    <td><input type="checkbox" name="neg[neg_add][]" value="r" /></td>
                    <td><input type="checkbox" name="neg[neg_add][]" value="w" /></td>
                    <td><input type="checkbox" name="neg[neg_add][]" value="i" /></td>
                    <td><input type="checkbox" name="neg[neg_add][]" value="d" /></td>
                    <td><input type="checkbox" name="neg[neg_add][]" value="k" /></td>
                    <td><input type="checkbox" name="neg[neg_add][]" value="a" /></td>
                </tr>
            </tbody>
        </table>
    </div>
    <input name="save" type="submit" class="saveButton" value="Save Permissions" />
</form>
</body>
</html>
