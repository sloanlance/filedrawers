{include file="pieces/masthead.tpl"}

<div class="sidebar">
    {if $newWebSpaceUI == false }
    <div id="inspector">
        <h3 id="inspTitle">Folder Properties</h3>
        <div id="selectedItem" style="background-image: url(images/folder.gif);">item
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
            <iframe name="favpanel" id="favpanel" scrolling="no" src="blankpage.html" frameborder="0"></iframe>
        </div>
        <div id="permissions" class="expandItem" style="display: none;">
            <div class="titlebar"> <a href="javascript:closeItem('permsCtrl','permissions');">[X]</a>
                <h2>Permissions Manager</h2>
            </div>
            <iframe name="perpanel" id="permpanel" scrolling="no" src="perm_manager.php?target={$path}" frameborder="0"></iframe>
        </div>
        <form name="newfold" id="newFolder" class="expandItem" style="display: none;" method="post" action="">
            <div class="titlebar"> <a href="javascript:closeItem('newFolderCtrl','newFolder');">[X]</a>
                <h2>Create a New Folder</h2>
            </div>
            <img src="images/mime/small/folder.gif" width="16" height="16" />
            <input type="hidden" name="command" value="newfolder" />
            <input type="text" name="selectedItems" id="newFold" size="34" value="Please enter a name for your new folder." onfocus="this.value=''" />
            <input type="submit" name="save" value="Create" />
        </form>
        <form name="upload2" id="upload" class="expandItem" style="display: none;" enctype="multipart/form-data" action="/mfile-bin/upload.cgi" method="post">
            <div class="titlebar"> <a href="javascript:closeItem('uploadCtrl','upload');document.getElementById('upload').reset();">[X]</a>
                <h2>Upload Files to AFS</h2>
            </div>
            <input type="hidden" name="sessionid" id="sessionid" value="{$sid}" />
            <input type="hidden" name="path" id="path" value="{$path}" />
            <input type="hidden" name="returnToURI" id="returnToURI" value="{$returnToURI}" />
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
                <input type="button" name="upload" value="Upload File(s)" onclick="startUpload();" />
&nbsp;&nbsp; </div>
            <iframe class="progbar" src="blankpage.html" marginwidth="0" align="center" marginheight="0" scrolling="no" id="progbar" frameborder="0"></iframe>
        </form>
    </div>
    {/if}
</div>
<div id="content">
{if $newWebSpaceUI == false }
<table id="fileList">
    <thead id="fileListHead">
        <tr>
            <th width="5%"><img src="images/checkbox.gif" width="14" height="14" /></th>
            <th width="7%" id="typesel"><a href="javascript:reorderFileList('type');">Type</a> <span id="sortType"></span></th>
            <th width="52%" align="left" id="titlesel"><a href="javascript:reorderFileList('title');">Title</a> <span id="sortTitle"></span></th>
            <th>&nbsp;</th>
            <th width="13%" id="sizesel"><a href="javascript:reorderFileList('size');">Size</a> <span id="sortSize"></span></th>
            <th width="23%" id="datesel"><a href="javascript:reorderFileList('date');">Last
                    Modified</a> <span id="sortDate"></span></th>
        </tr>
    </thead>
    <tbody id="sortResults">
    </tbody>
</table>
{else}
<div id="newWebSpaceMsg">
    <h1>Create a New Webspace</h1>
    <p>Before you can create a web site, you must first create a webspace to store all
        of the files in your site. This is a special folder that can be accessed by the
        web server.</p>
    <p>In order to properly set up this folder, please use the <a href="./make-webspace/">Make
            Webspace utility</a>.</p>
</div>
{/if}
<form name="cmd" id="cmd" method="post" action="">
    <input type="hidden" name="command" value="" />
    <input type="hidden" name="selectedItems" value="" />
    <input type="hidden" name="newName" value="" />
    <input type="hidden" name="originPath" value="" />
</form>
{include file="pieces/footer.tpl"}
