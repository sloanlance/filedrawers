<div id="infoBar">

<ul id="infoMenu">
{ if $type eq 'file' }
	<li><a href="/download/?path={$path_url}">Download</a></li>
{/if}
<li><a href="/?path={$parentPath}">Go Up &uarr;</a></li>
<li><a href="/?path={$path_url}">Refresh</a></li>
</ul>

<div id="notifyArea"> Location: 
<span id="location" style="width: 150px; overflow: hidden; white-space: nowrap;">
{$location}
</span>
<form name="changeDirForm" id="changeDirForm" action="" method="get"
style="display: inline;" >

<input type="button" name="changeDir" id="changeDir" value="Change"
onclick="activateLocationCtrl(true);" >

<input type="text" name="path" id="newLoc" size="40"
maxlength="100" value="" style="display: none;" >

<input type="submit" name="goChange" id="goChange" value="Go"
style="display: none;" >

<input type="button" name="cancelChange" id="cancelChange"
value="Cancel" style="display: none;"
onclick="activateLocationCtrl(false);" >

</form>
</div>

</div>
