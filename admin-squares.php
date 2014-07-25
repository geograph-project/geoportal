<?

include "includes/start.inc.php";

include "templates/header.inc.php";

if (!empty($_POST['content'])) {
	$lines = explode("\n",str_replace("\r",'',$_POST['content']));

	foreach ($lines as $idx => $line) {
		if (preg_match('/^(\s*)(.+?)( *\[\d+\])?\s*$/',$line,$m)) {
			$gridref = $m[2];
			$id = trim($m[3],'[] ');

			$updates = array();
			if ($id)
				$updates['square_id'] = $id;
			$updates['grid_reference'] = $gridref;

			$sql = $db->updates_to_insertupdate($db->table_square,$updates);
			$db->query($sql);
			//if (!$id)
			//	$id = $db->insert_id();
			
		}
	}


}


function outputFunction($parent_id,$indent=0) {
	global $db;
	$rows = $db->getAll("SELECT * FROM {$db->table_square}");
	if (!empty($rows))
		foreach ($rows as $row) {
			print he($row['grid_reference']).' ['.$row['square_id'].']'."\n";
		}
}

?>
<form method=post>
<ul>
	<li>Add new Square as needed (omit the id at the end)</li>
	<li>deleting is not yet supported</li>
</ul>
<textarea name="content" rows="50" cols="80"><?

outputFunction(0);

?></textarea>
<input type=submit value="Save Changes">
</form>
<?
include "templates/footer.inc.php";
