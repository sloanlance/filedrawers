<div id="sidebar">
<div id="inspector">
    <h3 id="inspTitle">Folder Properties</h3>
    <div id="selectedItem" style="background-image: url(/images/folder.gif);">item
	title</div>
    <h4>Actions</h4>
    <ul>
	<li id="uploadCtrl"></li>
	<li id="cutCtrl"></li>
	<li id="copyCtrl"></li>
	<li id="pasteCtrl"></li>
	<li id="deleteCtrl"></li>
	<li id="newFolderCtrl"></li>
	<li id="renameCtrl"></li>
	<li id="permsCtrl"></li>
	<li id="favCtrl"><a href="javascript:expandItem('favCtrl','favorites');setFavPath();">Favorite
		Locations</a></li>
    </ul>
    <div id="itemInfo"></div>
    <h4>View Options</h4>
    <ul>
	<li><a href="javascript:setShowHidden();" id="hidnFilesCtrl">Show Hidden Files</a></li>
    </ul>
    <div id="favorites" class="expandItem" style="display: none;">
	<div class="titlebar"> <a href="javascript:closeItem('favCtrl','favorites');">[X]</a>
	    <h2>My Favorite Locations</h2>
	</div>
	<iframe name="favpanel" id="favpanel" scrolling="no" src="/blankpage.html" frameborder="0"></iframe>
    </div>
    <div id="permissions" class="expandItem" style="display: none;">
	<div class="titlebar"> <a href="javascript:closeItem('permsCtrl','permissions');">[X]</a>
	    <h2>Permissions Manager</h2>
	</div>
	<iframe name="perpanel" id="permpanel" scrolling="no" src="/perm_manager.php?target={$path_url}" frameborder="0"></iframe>
    </div>
    <form name="newfold" id="newFolder" class="expandItem" style="display: none;" method="post" action="">
	<div class="titlebar"> <a href="javascript:closeItem('newFolderCtrl','newFolder');">[X]</a>
	    <h2>Create a New Folder</h2>
	</div>
	<img src="/images/mime/small/folder.gif" width="16" height="16" />
	<input type="hidden" name="command" value="newfolder" />
	<input type="text" name="selectedItems" id="newFold" size="34" value="Please enter a name for your new folder." onfocus="this.value=''" />
	<input type="submit" name="save" value="Create" />
    </form>
    <form name="upload2" id="upload" class="expandItem" style="display: none;" enctype="multipart/form-data" action="/mfile-bin/upload.cgi" method="post">
	<div class="titlebar"> <a href="javascript:closeItem('uploadCtrl','upload');document.getElementById('upload').reset();">[X]</a>
	    <h2>Upload Files to AFS</h2>
	</div>
	<input type="hidden" name="sessionid" id="sessionid" value="" />
	<input type="hidden" name="path" id="uploadpath" value="" />
	<input type="hidden" name="returnToURI" id="returnToURI" value="" />
	<input type="hidden" name="overwrite_file" id="overwrite_file" value="" />
	<ul>
	    <li id="file1">
		<input type="file" name="file" />
		<p><a href="javascript:toggleDisplay('file2');">Add Another File</a></p>
	    </li>
	    <li id="file2" style="display: none;">
		<input type="file" name="file" />
		<p><a href="javascript:toggleDisplay('file3');">Add Another File</a></p>
	    </li>
	    <li id="file3" style="display: none;">
		<input type="file" name="file" />
		<p><a href="javascript:toggleDisplay('file4');">Add Another File</a></p>
	    </li>
	    <li id="file4" style="display: none;">
		<input type="file" name="file" />
		<p><a href="javascript:toggleDisplay('file5');">Add Another File</a></p>
	    </li>
	    <li id="file5" style="display: none;">
		<input type="file" name="file" />
	    </li>
	</ul>
	<div class="uploadctrl" id="uploadctrl">
	    Overwrite files during upload?
	    <input type="checkbox" name="overwrite_box" id="overwrite_box" onClick="processClobberCheckbox();">

	    <input type="button" name="upload" value="Upload File(s)" onClick="uploadloopcnt=0; ajaxupload();" />
&nbsp;&nbsp; </div>
	<div id="lbtop">
		<div id="lbinner"></div>
		<table width="100%">
		<tr><td id=fileinfo></td><td align="right" id="lbpercent"></td></tr>
		</table>
	</div>

    </form>
</div>
<div id=version>
filedrawers version: {$filedrawers_version}
</div>
</div>
