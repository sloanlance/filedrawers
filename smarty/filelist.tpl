<div id="content">
<table id="fileList">
    <thead id="fileListHead">
        <tr>
            <th width="5%"><img src="/images/checkbox.gif" width="14" height="14" /></th>
            <th width="7%" id="typesel"><a href="javascript:reorderFileList('type');">Type</a> <span id="sortType"></span></th>
            <th width="52%" align="left" id="titlesel"><a href="javascript:reorderFileList('title');">Title</a> <span id="sortTitle"></span></th>
            <th>&nbsp;</th>
            <th width="13%" id="sizesel"><a href="javascript:reorderFileList('size');">Size</a> <span id="sortSize"></span></th>
            <th width="23%" id="datesel"><a href="javascript:reorderFileList('date');">Last Modified</a> <span id="sortDate"></span></th>
        </tr>
    </thead>
    <tbody id="sortResults">
    </tbody>
</table>
<form name="cmd" id="cmd" method="post" action="">
    <input type="hidden" name="command" value="" />
    <input type="hidden" name="selectedItems" value="" />
    <input type="hidden" name="newName" value="" />
    <input type="hidden" name="originPath" value="" />
</form>
</div>
