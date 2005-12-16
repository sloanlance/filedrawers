{include file="masthead.tpl"}

<div id="viewfile">
{php}
	global $afs;
	preg_match( '/^([^\/]+)\/?([^; ]*).*$/', $afs->mimetype, $Matches );
	$mType = $Matches[1];
	$mSubtype = $Matches[2];

	switch( $mType ) {
		case 'image': 
			echo '<img src="/download/view.php?path='.$afs->path.'" />';
			break;
		case 'audio': 
		case 'video': 
			#echo '<embed src="/download/view.php?path='.$afs->path.'">';
			echo '<embed src="/download/view.php?path='.$afs->path.'" '.
				'type="'.$afs->mimetype.'"/>';
			break;
		case 'text': 
		case 'html': 
			highlight_file( $afs->path );
			break;
		default:
			if ( $afs->mimetype == 'application/x-shockwave-flash' ) {
				echo '<embed src="/download/view.php?path='.$afs->path.'" '.
					'type="'.$afs->mimetype.'"/>';
				break;
			}

			echo 
				'<div id="error">'.
				"<h2>Unsupported MIME type</h2>\n".
				'<p>This file uses a MIME type ('.$afs->mimetype.
				") which is not viewable in most web browers.</p>\n".
				'<p>You may <a href="/download/?path='.$afs->path.
				'">download</a> this file, or <a href="/?path='.$afs->parPath.
				'">return to the parent directory</a>.</p>'.
				'</div>';
	}
{/php}
</div>

{include file="footer.tpl"}
