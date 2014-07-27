<?

include "config.php";

session_start();

//NOTE: We DONT check login status right up here, as this page is special, in that it can work befor users even created. 


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
		die("Tables already exist - cowardly refusing to continue!");
	}
	
	$file = file_get_contents('admin-setup.schema.mysql');

	$commands = explode(";\n",$file);

	foreach ($commands as $command) {
		$command = str_replace('geoportal_',$db->prefix,$command);
		$db->query($command);
	}
}

#############

if (!($result = mysql_query("SELECT * FROM {$db->table_configuration}", $db->db))) {
	?>
	</ol>
	<h2>Empty Database Detected</h2>
	
	<form method=post>
		Click this button: 
		<input type="submit" name="create" value="Create Database Tables"/> (or you can import the schema.mysql file manually first, then refresh this page)
	</form>
	
	<?
	exit;
}
while($result && ($row = mysql_fetch_assoc($result))) {
	$CONF[$row['name']] = $row['value'];
}
print "<li>Loaded Configuration from database</li>";

#########################

$row= $db->getRow("SELECT COUNT(*) as users, rights, user_id FROM {$db->table_user}");

if (empty($row['users'])) {
	if (empty($CONF['geograph_apikey'])) {
		//we need to let this continue, so they can use the form to enter their details!
	} else {
		//else get them to login!
		$_SESSION['continue'] = "admin-setup.php";
		header("Location: ./login.php");
		exit;
	}
} elseif($row['users'] == 1 && $row['rights'] == 'basic') {
	if ($row['user_id'] == $_SESSION['user_id']) {
		//they the first user, make them the admin!
		$db->query("UPDATE {$db->table_user} SET rights = 'basic,admin' WHERE user_id = {$row['user_id']}");
		print "Congratulations. As the first user, you are now the admin of this portal.";
	} else {
		//else get them to login!
		$_SESSION['continue'] = "admin-setup.php";
                header("Location: ./login.php");
                exit;
	}
} elseif (!empty($_SESSION['user_id'])) {
	//they are logged in, chek if admin!


	$rights = $db->getOne("SELECT rights FROM {$db->table_user} WHERE user_id = ".intval($_SESSION['user_id']));

        $_SESSION['basic'] = (strpos($rights,'basic') !== FALSE);

        if (!$_SESSION['basic'])
                die("unable to continue at this time");

        $_SESSION['admin'] = (strpos($rights,'admin') !== FALSE);

        if (!$_SESSION['admin'])
                die("You need to be an admin to access this page!");

} else {
	$_SESSION['continue'] = "admin-setup.php";
	header("Location: ./login.php");
        exit;	
}

#########################

if (!empty($_POST['update'])) {
	$BEFORE = $CONF;

	foreach ($_POST['conf'] as $name => $value) {
		$name = $db->quote($name);
		$value = $db->quote($value);
		$db->query("INSERT INTO {$db->table_configuration} SET name=$name, value=$value, created=NOW() ON DUPLICATE KEY UPDATE name=$name, value=$value");
	}

	foreach ($db->getAll("SELECT * FROM {$db->table_configuration}") as $row) {
		$CONF[$row['name']] = $row['value'];
	}
	
	print "<p>Configuration Saved</p>";

	if (!empty($CONF['geograph_apikey']) && !empty($BEFORE['geograph_apikey'])) {
		print '<meta http-equiv="refresh" content="3; url=?">';
		print "redirecting in 3 seconds...";
		exit;	
	}
}

#############

if (empty($CONF['url'])) {
	$CONF['url'] = "http://{$_SERVER['HTTP_HOST']}".dirname($_SERVER['SCRIPT_NAME'])."/";
}

if (empty($CONF['geograph_apikey'])) {
	print "<p>WARNING: Until you get an Geograph API key, from <a href='http://{$CONF['geograph_domain']}/admin/mykey.php'>http://{$CONF['geograph_domain']}/admin/mykey.php</a>, ANYBODY can access this page. Once you have a key, can enforce login.</p>";
}


	?>
	</ol>
	<h2>Configure your portal</h2>
	<form method=post>
		<h3>Geograph Intergration</h3>
		<table cellspacing=0 cellpadding=10 border=1 bgcolor=#eee width="100%">
			<tr>
				<th>URL</th>
				<td><input type="text" name="conf[url]" value="<? echo @he($CONF['url']); ?>" size="60" maxlength="64"/><br/>
					<small>The Homepage for your portal - including the tailing slash. Should be autodetected, but only tested on Apache.</small></td>
			</tr>
			<tr>
				<th>Geograph Domain</th>
				<td><input type="text" name="conf[geograph_domain]" value="<? echo @he($CONF['geograph_domain']); ?>" size="60" maxlength="64"/><br/>
					<small>only tested with www.geograph.org.uk but might work for others</small></td>
			</tr>
                        <tr>
                                <th>API Key</th>
                                <td><input type="text" name="conf['geograph_apikey]" value="<? echo @he($CONF['geograph_apikey']); ?>" size="60" maxlength="64"/><br/>
                                        <small>Your Geograph API Key, get from <a href="http://<? echo $CONF['geograph_domain'];?>/admin/mykey.php">here</a> - remember keep this secret!</small></td>
                        </tr>
                        <tr>
                                <th>API Access Key</th>
                                <td><input type="text" name="conf['geograph_accesskey]" value="<? echo @he($CONF['geograph_accesskey']); ?>" size="60" maxlength="64"/><br/>
                                        <small>Your Geograph Access Key, get from <a href="http://<? echo $CONF['geograph_domain'];?>/admin/mykey.php">here</a></small></td>
                        </tr>
                        <tr>
                                <th>API Shared Magic</th>
                                <td><input type="text" name="conf['geograph_magic]" value="<? echo @he($CONF['geograph_magic']); ?>" size="60" maxlength="64"/><br/>
                                        <small>Your Geograph Shared Magic, get from <a href="http://<? echo $CONF['geograph_domain'];?>/admin/mykey.php">here</a> - remember keep this secret!</small></td>
                        </tr>
		</table>
		<? if (!empty($CONF['geograph_apikey'])) { ?>
			If you've just filled out the above details, its recommended to <input type="submit" name="update" value="Save Configuration"/> now, before continuing with the rest of the configuration.
		<? } ?>

		<h3>General Settings</h3>
		<table cellspacing=0 cellpadding=10 border=1 bgcolor=#eee width="100%">
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
		</table>
		<h3>Portal Contents</h3>
		<table cellspacing=0 cellpadding=10 border=1 bgcolor=#eee width="100%">

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
				<th>Submission Message</th>
				<td><textarea name="conf[submission_prompt]" rows="6" cols="80"><? echo @he($CONF['submission_prompt']); ?></textarea><br/>
					<small>If included will add a prompt to homepaeg, telling users how to submit. Include something along the lines of 'Submit your photograph to Geograph, and be sure to include the [bridge] tag on the image'.</small></td>
			</tr>
			<tr>
				<th>Coverage Map Source</th>
				<td><textarea name="conf[square_source]" rows="6" cols="80"><? echo @he($CONF['square_source']); ?></textarea><br/>
					<small>OPTIONAL Lists a credit and explanation for the source of the squares basemap - used for calculating coverage statistics.</small></td>
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
