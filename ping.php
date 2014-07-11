<?

include "includes/start.inc.php";

if (empty($CONF['query'])) 
	die("no query");

##getImages($CONF['query']);

$start = 0;

do {
	$row = $db->getRow("SELECT *,IF(fetched>DATE_SUB(NOW(),INTERVAL 30 DAY),1,0) `recent` FROM {$db->table_fetcher} WHERE `start` = $start AND `query` = ".$db->quote($CONF['query']) );

	if (!empty($row) && $row['recent']) {
		//recently fetched!
		$start = $row['last']+1;
	} else {
		list($total,$images,$last) = getImages($CONF['query'],$start);

                $updates= array();
		$updates['query'] = $CONF['query'];
		$updates['images'] = $images;
		$updates['start'] = $start;
		$updates['last'] = $last;
		$updates['total'] = $total;
		$updates['fetched'] = "NOW()";
			
                $sql = $db->updates_to_insertupdate($db->table_fetcher,$updates);

print "$sql<br>";
                $db->query($sql);

		if (empty($images))
			break;

		$start = $last+1;
	}
	$c++;
} while ($c<10);




print "<hr/>.";

function getImages($q,$start=0,$limit=1000,$offset=0) {
	global $db, $CONF;
	
	$select = "takenday,submitted,imageclass,wgs84_lat,wgs84_long";
	$select .= ",".($asis = 'title,user_id,realname,grid_reference,hash,tags,sequence,place,county,country,format,direction,distance,status,contexts,scenti');

	$url = "http://{$CONF['geograph_domain']}/api-facet.php?a=1&q=".urlencode($q)."&sort=id+asc&rank=2&limit=$limit&offset=$offset&select=$select&key={$CONF['geograph_apikey']}";

	if ($start)
		$url .= "&range=".intval($start).",99999999";

	$string = file_get_contents($url);
	$decode = json_decode($string);
	
	$asis = explode(',',$asis);
	$last = -1;
	if (!empty($decode->matches)) {
		foreach ($decode->matches as $row) {
		
			$updates= array();
			$updates['image_id'] = $row->id;
			$updates['taken'] = $row->attrs->takenday;
			$updates['submitted'] = "FROM_UNIXTIME({$row->attrs->submitted})";
			$updates['category'] = $row->attrs->imageclass;
			$updates['wgs84_lat'] = rad2deg($row->attrs->wgs84_lat);
			$updates['wgs84_long'] = rad2deg($row->attrs->wgs84_long);
			
			foreach ($asis as $key) {
				$updates[$key] = $row->attrs->{$key};
			}
			
			//todo, lookup description somehow!
			
			$updates['fetched'] = "NOW()";
			
			$sql = $db->updates_to_insertupdate($db->table_image,$updates);

//print "$sql<br>";
			
			$db->query($sql);
			
			print "{$row->id} ";
			$last = $row->id;
		}
	}
	return array($decode->total_found,count($decode->matches),$last);
}

