<?

include "includes/start.inc.php";

$scalex = 50; //$pixels = $degress * $scale
$scaley = 80; //not equal because we using lat/long here, which isnt square!
$pix = 2;


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


$width = (ceil($east)-floor($west)) * $scalex;
$height = (ceil($north)-floor($south)) * $scaley;

###################

$south = floor($south);
$west = floor($west);

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


