<?


######################

function update_square_images() {
	global $db;

	$db->query("CREATE TEMPORARY TABLE coverage_updator (KEY(grid_reference)) SELECT s.grid_reference,COUNT(image_id) AS images 
		FROM {$db->table_square} s LEFT JOIN {$db->table_image} i ON (i.grid_reference = s.grid_reference AND active != 'deleted') GROUP BY s.grid_reference ORDER BY NULL");

	$db->query("UPDATE {$db->table_square} SET images = 0, updated=updated");
	$db->query("UPDATE {$db->table_square} s LEFT JOIN coverage_updator u USING(grid_reference) SET s.images = u.images, s.updated=s.updated");
}

######################

function update_image_point() {
	global $db;

	$db->query("CREATE TEMPORARY TABLE point_updator (KEY(image_id)) SELECT grid_reference,MIN(image_id) AS image_id
                FROM {$db->table_image} WHERE active != 'deleted' GROUP BY grid_reference ORDER BY NULL");

	$db->query("UPDATE {$db->table_image} SET point = 0, updated=updated");
	$db->query("UPDATE {$db->table_image} i INNER JOIN point_updator u USING(image_id) SET i.point = 1, i.updated=i.updated");
}

#######################

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
			if (!empty($row->attrs->imageclass))
				$updates['category'] = $row->attrs->imageclass;
			$updates['wgs84_lat'] = rad2deg($row->attrs->wgs84_lat);
			$updates['wgs84_long'] = rad2deg($row->attrs->wgs84_long);
			
			foreach ($asis as $key) {
				$updates[$key] = $row->attrs->{$key};
			}
			
			//todo, lookup description somehow!
			
			$updates['fetched'] = "NOW()";
			
			$sql = $db->updates_to_insertupdate($db->table_image,$updates);
			
			$db->query($sql);
			
			$last = $row->id;
		}
	}
	return array($decode->total_found,count($decode->matches),$last);
}

