<?

include "includes/start.inc.php";
include "includes/fetching.inc.php";

#######################

if (!empty($CONF['query'])) {

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
	} while ($c<30);
}

######################

if (!empty($images)) {
	update_square_images();
	update_image_point();
}

######################

print "<hr/>.";

#######################

