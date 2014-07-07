<?

include "includes/start.inc.php";

if (empty($CONF['query'])) 
	die("no query");

getImages($CONF['query']);



print "<hr/>.";

function getImages($q,$limit=100,$offset=0) {
	global $db, $CONF;
	
	$select = "title,user_id,realname,grid_reference,takenday,submitted,imageclass,wgs84_lat,wgs84_long,hash,tags";

	$url = "http://{$CONF['geograph_domain']}/api-facet.php?a=1&q=".urlencode($q)."&sort=id+asc&rank=2&limit=$limit&offset=$offset&select=$select&key={$CONF['geograph_apikey']}";
	$string = file_get_contents($url);

	$decode = json_decode($string);
	

	
	if (!empty($decode->matches)) {
		foreach ($decode->matches as $row) {
			print_rp($row);
		
			$updates= array();
			
			$updates['image_id'] = $row->id;
			$updates['title'] = $row->attrs->title;
			$updates['user_id'] = $row->attrs->user_id;
			$updates['realname'] = $row->attrs->realname;
			$updates['grid_reference'] = $row->attrs->grid_reference;
			$updates['taken'] = $row->attrs->takenday;
			$updates['submitted'] = "FROM_UNIXTIME({$row->attrs->submitted})";
			$updates['category'] = $row->attrs->imageclass;
			$updates['wgs84_lat'] = rad2deg($row->attrs->wgs84_lat);
			$updates['wgs84_long'] = rad2deg($row->attrs->wgs84_long);
			$updates['hash'] = $row->attrs->hash;
			$updates['tags'] = $row->attrs->tags;
			$updates['fetched'] = "NOW()";
			
			
			
			$sql = $db->updates_to_dupinsert($db->table_image,$updates);
			
			$db->query($sql);
			
			print "{$row->id} ";
		}
	}

}

