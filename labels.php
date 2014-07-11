<?

include "includes/start.inc.php";

include "templates/header.inc.php";

if (!empty($_POST['content'])) {
	$lines = explode("\n",str_replace("\r",'',$_POST['content']));

	$stack = array();
	foreach ($lines as $idx => $line) {
		if (preg_match('/^(\s*)(.+?)( *\[\d+\])?\s*$/',$line,$m)) {
			$indent = strlen($m[1]);
			$name = $m[2];
			$id = trim($m[3],'[] ');

			$updates = array();
			if ($id)
				$updates['label_id'] = $id;
			$updates['name'] = $name;
			$updates['orderby'] = $idx;
			$updates['parent_id'] = 0;
			if (!empty($stack))
				foreach ($stack as $row)
					if ($indent > $row[0])
						$updates['parent_id'] = $row[2];

			$sql = $db->updates_to_insertupdate($db->table_label,$updates);
			$db->query($sql);
			if (!$id)
				$id = $db->insert_id();
			
			while(!empty($stack) && $stack[count($stack)-1][0] >= $indent)
				array_pop($stack);

			$stack[] = array($indent,$name,$id);
		}
	}


}


function outputFunction($parent_id,$indent=0) {
	global $db;
	$rows = $db->getAll("SELECT * 
		FROM {$db->table_label} l
		WHERE parent_id = {$parent_id}
		ORDER BY orderby");
	if (!empty($rows))
		foreach ($rows as $row) {
			if ($indent>0)
				print str_repeat('  ',$indent);
			print he($row['name']).' ['.$row['label_id'].']'."\n";
			outputFunction($row['label_id'],$indent+1);
		}
}

?>
<form method=post>
<ul>
	<li>Add new Labels as needed (omit the id at the end)</li>
	<li>Change parents by changing indentation</li>
	<li>Rename Labels by simply editing (but leave the number at the end)</li>
	<li>Can also reorder Labels in the list</li>
	<li>deleting is not yet supported</li>
</ul>
<textarea name="content" rows="50" cols="80"><?

outputFunction(0);

?></textarea>
<input type=submit value="Save Changes">
</form>
<?
include "templates/footer.inc.php";

