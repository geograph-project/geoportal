<?

include "config.php";

include "includes/functions.inc.php";

include "includes/database.class.php";

$db = new Database;
$db->connect(false);

print "<ol>";

#########################

if (!$db->db) {
	?>
	</ol>
	<h2>Unable to connect to database</h2>
	
	<p>Did you copy config.sample.php to <b>config.inc.php</b>, and add your database connection details?</p>
	<?
	exit;
}

print "<li>Database connection successful</li>";

#########################

if (!empty($_POST['create'])) {
	$tables = $db->getAll("SHOW TABLES LIKE '{$db->prefix}%'");
	if (!empty($tables)) {
		//die("Tables already exist - cowardly refusing to continue!");
	}
	
	$db->query("CREATE TABLE {$db->table_configuration} (
			`configuration_id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`name` VARCHAR( 32 ) NOT NULL ,
			`value` TEXT NOT NULL ,
			`created` DATETIME NOT NULL ,
			`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
			UNIQUE (`name`)
		) ENGINE = MYISAM");
	
	$db->query("CREATE TABLE {$db->table_image} (
			`image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`title` varchar(128) DEFAULT NULL,
			`submitted` datetime NOT NULL,
			`category` varchar(32) NOT NULL DEFAULT '',
			`taken` date NOT NULL DEFAULT '0000-00-00',
			`grid_reference` varchar(6) NOT NULL DEFAULT '',
			`user_id` int(10) unsigned NOT NULL,
			`profile_link` varchar(255) NOT NULL,
			`realname` varchar(128) NOT NULL DEFAULT '',
			`comment` text NOT NULL,
			`wgs84_lat` decimal(10,6) NOT NULL DEFAULT '0.000000',
			`wgs84_long` decimal(10,6) NOT NULL DEFAULT '0.000000',
			`hash` varchar(8) NOT NULL DEFAULT '',
			`width` smallint(5) unsigned NOT NULL DEFAULT '0',
			`height` smallint(5) unsigned NOT NULL DEFAULT '0',
			`width_original` smallint(5) unsigned NOT NULL DEFAULT '0',
			`height_original` smallint(5) unsigned NOT NULL DEFAULT '0',
			`created` datetime NOT NULL,
			`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`fetched` datetime NOT NULL,
			`showday` date DEFAULT NULL,
			PRIMARY KEY (`image_id`)
		) ENGINE=MyISAM");
	
}

#############

if (!($result = mysql_query("SELECT * FROM {$db->table_configuration}", $db->db))) {
	?>
	</ol>
	<h2>Empty Database Detected</h2>
	
	<form method=post>
		Click this button: 
		<input type="submit" name="create" value="Create Database Tables"/>
	</form>
	
	<?
	exit;
}
while($result && ($row = mysql_fetch_assoc($result))) {
	$CONF[$row['name']] = $row['value'];
}
print "<li>Loaded Configuration from database</li>";

#########################

//todo login!

#########################

if (!empty($_POST['update'])) {
	foreach ($_POST['conf'] as $name => $value) {
		$name = $db->quote($name);
		$value = $db->quote($value);
		$db->query("INSERT INTO {$db->table_configuration} SET name=$name, value=$value, created=NOW() ON DUPLICATE KEY UPDATE name=$name, value=$value");
	}
	
	foreach ($db->getAll("SELECT * FROM {$db->table_configuration}") as $row) {
		$CONF[$row['name']] = $row['value'];
	}
	
	print "Configuration Saved";
}

#############

if (empty($CONF['url'])) {
	$CONF['url'] = "http://{$_SERVER['HTTP_HOST']}".dirname($_SERVER['SCRIPT_NAME'])."/";
}


	?>
	</ol>
	<h2>Configure your portal</h2>
	<form method=post>
		<table cellspacing=0 cellpadding=10 border=1 bgcolor=#eee>
			<tr>
				<th>Portal Title</th>
				<td><input type="text" name="conf[title]" value="<? echo @he($CONF['title']); ?>" size="60" maxlength="64"/><br/>
					<small>General title for your Portal</small></td>
			</tr>
			<tr>
				<th>Portal Subject</th>
				<td><input type="text" name="conf[subject]" value="<? echo @he($CONF['subject']); ?>" size="60" maxlength="64"/><br/>
					<small>Ideally 3 words or less describing the subject of your Portal. Can be the same as the title.</small></td>
			</tr>
			<tr>
				<th>Introduction</th>
				<td><textarea name="conf[intro]" rows="6" cols="80"><? echo @he($CONF['intro']); ?></textarea><br/>
					<small>Short paragraph or two introducing your portal, used on the homepage.</small></td>
			</tr>
			<tr>
				<th>URL</th>
				<td><input type="text" name="conf[url]" value="<? echo @he($CONF['url']); ?>" size="60" maxlength="64"/><br/>
					<small>The Homepage for your portal - including the tailing slash. Should be autodetected, but only tested on Apache.</small></td>
			</tr>
			<tr>
				<th>Query</th>
				<td><input type="text" name="conf[query]" value="<? echo @he($CONF['query']); ?>" size="60" maxlength="64" id="q"/> <a href="#" onclick="return test_q()">Test</a><br/>
					<small>Ideally provide a VERY GENERAL keyword search that shows images for your portal, eg for a Train Station portal, could be [ "Train Station" | "Railway Station" ]. The exact images used in the portal will be refined later. Dont worry about having false positives on this search. </small></td>
			</tr>
			<tr>
				<th>Tag</th>
				<td><input type="text" name="conf[tag]" value="<? echo @he($CONF['tag']); ?>" size="60" maxlength="64" id="tag"/> <a href="#" onclick="return test_tag()">Test</a><br/>
					<small>OPTIONAL If there is a single tag that well represents this portal enter it here.</small></td>
			</tr>
			<tr>
				<th>Geograph Domain</th>
				<td><input type="text" name="conf[geograph_domain]" value="<? echo @he($CONF['geograph_domain']); ?>" size="60" maxlength="64"/><br/>
					<small>only tested with www.geograph.org.uk but might work for others</small></td>
			</tr>
			<tr>
				<th>Submission Message</th>
				<td><textarea name="conf[submission_prompt]" rows="6" cols="80"><? echo @he($CONF['submission_prompt']); ?></textarea><br/>
					<small>If included will add a prompt to homepaeg, telling users how to submit. Include something along the lines of 'Submit your photograph to Geograph, and be sure to include the [bridge] tag on the image'.</small></td>
			</tr>
		</table>
		<br/>
		<input type="submit" name="update" value="Save Configuration"/>
	</form>
	<a href="./">back to homepage</a>
	
<script>
function test_q() {
	var q = document.getElementById('q').value;
	window.open("http://<? echo he($CONF['geograph_domain']); ?>/browser/redirect.php?q="+encodeURIComponent(q));
}
function test_tag() {
	var q = document.getElementById('tag').value;
	window.open("http://<? echo he($CONF['geograph_domain']); ?>/stuff/tagmap.php?tag="+encodeURIComponent(q));
}
</script>
