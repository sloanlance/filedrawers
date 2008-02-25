<div id="content">
<table id="fileList">
	<thead id="fileListHead">
		<tr>
			{php}
			global $afs;
			/*
			 * only include checkbox column if appropriate privileges are set
			 * these should jive with adding the column for createItemSelect 
			 * in js/filemanage.js 
			 *
			 * Yes, insert and write are needed for delete
			 */
			if (( $afs->insertPriv && $afs->writePriv && $afs->deletePriv ) ||
					$afs->adminPriv || $afs->readPriv ) {
				echo '<th width="5%"><img src="/images/checkbox.gif" width="14" height="14" /></th>' . "\n";
			}
			{/php}
			<th width="7%" id="typesel"><a href="javascript:reorderFileList('type');">Type</a> <span id="sortType"></span></th>
			<th width="52%" align="left" id="titlesel"><a href="javascript:reorderFileList('title');">Title</a> <span id="sortTitle"></span></th>

			{php} if ( $afs->readPriv ) { {/php}
			<th>&nbsp;</th>
			{php} } {/php}

			<th width="13%" id="sizesel"><a href="javascript:reorderFileList('size');">Size</a> <span id="sortSize"></span></th>
			<th width="23%" id="datesel"><a href="javascript:reorderFileList('date');">Last Modified</a> <span id="sortDate"></span></th>
		</tr>
	</thead>
	<tbody id="sortResults">
	</tbody>
</table>
<form name="cmd" id="cmd" method="post" action="">
	<input type="hidden" name="formKey" value="{$formKey}" />
	<input type="hidden" name="command" value="" />
	<input type="hidden" name="selectedItems" value="" />
	<input type="hidden" name="newName" value="" />
	<input type="hidden" name="originPath" value="" />
</form>
</div>
