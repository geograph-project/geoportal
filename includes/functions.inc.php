<?

function he($in) {
	return  htmlentities($in);
}
function dis($in) {
	return "<b>".number_format($in,0)."</b>";
}

function init_session()
{
	session_start();
}

function print_rp($q) {
	print "<pre style='border:1px solid red; padding:10px; text-align:left; background-color:silver'>";
	print_r($q);
	print "</pre>";
}

function getGeographUrl($gridimage_id,$hash,$size = 'small') {
	$ab=sprintf("%02d", floor(($gridimage_id%1000000)/10000));
	$cd=sprintf("%02d", floor(($gridimage_id%10000)/100));
	$abcdef=sprintf("%06d", $gridimage_id);
	if ($gridimage_id<1000000) {
		$fullpath="/photos/$ab/$cd/{$abcdef}_{$hash}";
	} else {
		$yz=sprintf("%02d", floor($gridimage_id/1000000));
		$fullpath="/geophotos/$yz/$ab/$cd/{$abcdef}_{$hash}";
	}
	$server =  "https://s".($gridimage_id%4).".geograph.org.uk";

	switch($size) {
		case 'full': return "https://s0.geograph.org.uk$fullpath.jpg"; break;
		case 'med': return "$server{$fullpath}_213x160.jpg"; break;
		case 'small':
		default: return "$server{$fullpath}_120x120.jpg";

	}
}

//todo, compat for json_decode

function externalLink($params)
{
	global $CONF;
  	//get params and use intelligent defaults...
  	$href=str_replace(' ','+',$params['href']);
  	if (strpos($href,'http://') !== 0)
  		$href ="http://$href";

  	if (isset($params['text']))
  		$text=$params['text'];
  	else
  		$text=$href;

  	if (isset($params['title']))
		$title=$params['title'];
	else
		$title=$text;
	
	if (isset($params['nofollow']))
		$title .= "\" rel=\"nofollow"; 	

  	if ($params['target'] == '_blank') {
  		return "<span class=\"nowrap\"><a title=\"$title\" href=\"$href\" target=\"_blank\">$text</a>".
  			"<img style=\"padding-left:2px;\" alt=\"External link\" title=\"External link - opens in a new window\" src=\"http://s0.geograph.org.uk/img/newwin.png\" width=\"10\" height=\"10\"/></span>";
  	} else {
  		return "<span class=\"nowrap\"><a title=\"$title\" href=\"$href\">$text</a>".
  			"<img style=\"padding-left:2px;\" alt=\"External link\" title=\"External link - shift click to open in new window\" src=\"http://s0.geograph.org.uk/img/external.png\" width=\"10\" height=\"10\"/></span>";
  	}
}



function fetchFeed($url,$tim = 3600, $refresh = false) {
	//connect

	$cachepath = "cache/".md5($url).".cache";

	if (empty($refresh) && file_exists($cachepath) && @filemtime($cachepath) > time() - $tim) {
		$file = file_get_contents($cachepath);
	} else {
		if ($_GET['debug'])
			print "FETCHING $url<hr/>";
		$url = html_entity_decode($url);
		$file = file_get_contents($url);

		file_put_contents($cachepath,$file);
	}
	return $file;
}

function customCacheControl($mtime,$uniqstr,$useifmod = true,$gmdate_mod = 0) {
	global $encoding;
	if (isset($encoding) && $encoding != 'none') {
		$uniqstr .= $encoding;
	}
	
	$hash = "\"".md5($mtime.'-'.$uniqstr)."\"";

	
	if(isset($_SERVER['HTTP_IF_NONE_MATCH'])) { // check ETag
		if($_SERVER['HTTP_IF_NONE_MATCH'] == $hash ) {
			header("HTTP/1.0 304 Not Modified");
			header ("Etag: $hash"); 
			header('Content-Length: 0'); 
			exit;
		}
		
		//also check legacy Etag
		$hash2 = "\"".$mtime.'-'.md5($uniqstr)."\"";
		
		if($_SERVER['HTTP_IF_NONE_MATCH'] == $hash2 ) {
			header("HTTP/1.0 304 Not Modified");
			header ("Etag: $hash2"); 
			header('Content-Length: 0'); 
			exit;
		}
	}	

	header ("Etag: $hash"); 

	if (!$gmdate_mod)
		$gmdate_mod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

	if ($useifmod && !empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);

		if ($if_modified_since == $gmdate_mod) {
			header("HTTP/1.0 304 Not Modified");
			header('Content-Length: 0'); 
			exit;
		}
	}

	header("Last-Modified: $gmdate_mod");
}

function customNoCacheHeader($type = 'nocache',$disable_auto = false) {
	//none/nocache/private/private_no_expire/public
	if ($type == 'nocache') {
		header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1 
		header("Cache-Control: post-check=0, pre-check=0", false); 
		header("Pragma: no-cache"); 
		customExpiresHeader(-1);
	} 	
	if ($disable_auto) {
		//call to disable the auto session one, could then call another here if needbe
		session_cache_limiter('none');
	}
}

function customExpiresHeader($diff,$public = false) {
	if ($diff > 0) {
		$expires=gmstrftime("%a, %d %b %Y %H:%M:%S GMT", time()+$diff);
		header("Expires: $expires");
		header("Cache-Control: max-age=$diff",false);
	} else {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past 
		header("Cache-Control: max-age=0",false);
	}
	if ($public)
		header("Cache-Control: Public",false);
}

function getEncoding() {
	global $encoding;
	if (!empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
		$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
		$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

		$encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : '');

		if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') && 
				preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
			$version = floatval($matches[1]);

			if ($version < 6)
				$encoding = '';

			if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) 
				$encoding = '';
		}
	} else {
		$encoding = '';
	}
	return $encoding;
}

function customGZipHandlerStart() {
	global $encoding;
	if ($encoding = getEncoding()) {
		ob_start();
		register_shutdown_function('customGZipHandlerEnd');
		return true;
	}
	return false;
}

function customGZipHandlerEnd() {
	global $encoding,$cachepath;
	
	$contents =& ob_get_clean();

	if (isset($encoding) && $encoding) {
		// Send compressed contents
		$contents = gzencode($contents, 9,  ($encoding == 'gzip') ? FORCE_GZIP : FORCE_DEFLATE);
		header ('Content-Encoding: '.$encoding);
		header ('Vary: Accept-Encoding');
	}
	//else ... we could still send Vary: but because a browser that doesnt will accept non gzip in all cases, doesnt matter if the cache caches the non compressed version (the otherway doesnt hold true, hence the Vary: above)
	header('Content-Length: '.strlen($contents));
	
		
	if (!empty($cachepath) && empty($nocache)) {
		file_put_contents($cachepath,$contents);
		
		$mtime = @filemtime($cachepath);

		customExpiresHeader(3600*24*24,true);
		customCacheControl($mtime,$cachepath);
			
	}
	
	echo $contents;
}


function htmlspecialchars2( $myHTML,$quotes = ENT_COMPAT,$char_set = 'ISO-8859-1')
{
	return preg_replace( "/&amp;([A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};|#x[0-9a-fA-F]{2,4};)/", '&$1' ,htmlspecialchars($myHTML,$quotes,$char_set));
} 

function htmlentities2( $myHTML,$quotes = ENT_COMPAT,$char_set = 'ISO-8859-1')
{
	return preg_replace( "/&amp;([A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};|#x[0-9a-fA-F]{2,4};)/", '&$1' ,htmlentities($myHTML,$quotes,$char_set));
} 

function htmlnumericentities($myXML){
	return str_replace('&#38;amp;','&amp;',preg_replace('/[^!-%\x27-;=?-~ ]/e', '"&#".ord("$0").chr(59)', htmlspecialchars($myXML)));
}


function MakeLinks($posterText) {
	$posterText = preg_replace('/(?<!["\'>F=])(https?:\/\/[\w\.-]+\.\w{2,}\/?[\w\~\-\.\?\,=\'\/\\\+&%\$#\(\)\;\:]*)(?<!\.)(?!["\'])/e',"externalLink(array('href'=>\"\$1\",'text'=>\"\$1\",'nofollow'=>1,'title'=>\"\$1\"))",$posterText);

	$posterText = preg_replace('/(?<![\/F\.])(www\.[\w\.-]+\.\w{2,}\/?[\w\~\-\.\?\,=\'\/\\\+&%\$#\(\)\;\:]*)(?<!\.)(?!["\'])/e',"externalLink(array('href'=>\"http://\$1\",'text'=>\"\$1\",'nofollow'=>1,'title'=>\"\$1\"))",$posterText);

	$posterText = str_replace("/\n\n\n/",'<br/><br/>',$posterText);

	return $posterText;
}


