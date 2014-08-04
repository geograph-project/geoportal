<?

include "includes/start.inc.php";

$plot = empty($CONF['imagemap_grid_display'])?'wgs84':$CONF['imagemap_grid_display']; //todo - this is not honoured!

$scalex = empty($CONF['imagemap_scalex'])?50:$CONF['imagemap_scalex']; //$pixels = $degress * $scale
$scaley = empty($CONF['imagemap_scaley'])?80:$CONF['imagemap_scaley']; //not equal because we using lat/long here, which isnt square!
$pix = empty($CONF['imagemap_pixels'])?2:$CONF['imagemap_pixels']; //number of pixels padding

$decimals = empty($CONF['imagemap_decimals'])?2:$CONF['imagemap_decimals']; //decimal places to round decrees (eg 0 = whole degree, 1 is to nearest 0.1degree etc)

#################

$rows = $db->getAll("SELECT wgs84_lat,wgs84_long,images FROM {$db->table_square} where wgs84_lat != 0") ;
if (empty($rows) || $rows[0]['wgs84_lat'] < 1) {
	die("unable to plot points");
}

$x = $y = array();
foreach ($rows as $row) {
	$x[] = $row['wgs84_long'];
	$y[] = $row['wgs84_lat'];
}

$north = max($y);
$south = min($y);
$east = max($x);
$west = min($x);

function floordec($zahl){    
	global $decimals;
     return floor($zahl*pow(10,$decimals))/pow(10,$decimals);
}
function ceildec($zahl){
	global $decimals;
     return ceil($zahl*pow(10,$decimals))/pow(10,$decimals);
}

$width = (ceildec($east)-floordec($west)) * $scalex;
$height = (ceildec($north)-floordec($south)) * $scaley;

###################

$south = floordec($south);
$west = floordec($west);

$im = @imagecreate($width,$height) or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocate($im, 255, 255, 255);
$red = imagecolorallocate($im, 233, 14, 91);
$green = imagecolorallocate($im, 0, 255, 0);


foreach ($rows as $row) {
	$x = ($row['wgs84_long']-$west) * $scalex;
	$y = $height - (($row['wgs84_lat']-$south) * $scaley);

	imagefilledrectangle($im,$x-$pix,$y-$pix,$x+$pix,$y+$pix,$row['images']?$red:$green);
}

###################

header("Content-Type: image/png");

imagepng($im);
imagedestroy($im);


