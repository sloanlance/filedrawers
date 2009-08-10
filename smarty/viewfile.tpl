{include file="masthead.tpl"}

<div id="viewfile">
{php}
	global $afs;
    $previewType = Mime::getPreviewType( $afs->mimetype );

    echo "<!-- MIME: [$afs->mimetype] -->\n";

    if ( $previewType == 'image' ) {
        echo '<img src="/download/view.php?path='.
				urlencode( $afs->path ).'" />';
    } else if ( $previewType == 'embed' ) {
        echo '<embed src="/download/view.php?path='.
				urlencode( $afs->path ).'" type="'.$afs->mimetype.'"/>';
    } else if ( $previewType == 'text' ) {
        highlight_file( $afs->path );
    } else {
		$dl_link = '<a href="/download/?path='.urlencode( $afs->path ).'">';
		echo '<div id="error">'.
			"<h2>Unsupported MIME type</h2>\n".
			'<p>This file uses a MIME type ('.$afs->mimetype.
			") which is not viewable in most web browers.</p>\n".
			'<p>You may '.$dl_link.'download</a> '.
			$dl_link.'<img src="/images/download.gif" width="16" '.
			'height="16" alt="Download"></a> '.
			'this file, or <a href="/?path='.$afs->parPath.'">return '.
			'to the parent directory</a>.</p>'.
			'</div>';
    }
{/php}
</div>

{include file="footer.tpl"}
