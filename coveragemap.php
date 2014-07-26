<?

include "includes/start.inc.php";

include "templates/header.inc.php";

print '<h3>Coverage Map</h3>';

#################

        $row = $db->getRow("SELECT COUNT(*) AS squares, SUM(images>0) AS photographed FROM {$db->table_square}");

        if (!empty($row['squares'])) {
		print "<div class=statbar>";
                $row['percentage'] = sprintf('%.1f',$row['photographed']/$row['squares']*100);
                print "Total Squares: ".dis($row['squares']).", of which ".dis($row['photographed'])." have photographs; which is a coverage of <b>{$row['percentage']}%</b>";
                print " <a href=statistics.php>MORE...</a>";
	        if (!empty($CONF['square_source'])) {
			print "<div class=source>".nl2br(he($CONF['square_source']))."</div>";
		}
                print "</div>";
        }

##################

if (empty($_GET['dynamic'])) { ?>

	<div class="warning">
		Note: This is only a quick plot. Its not zoomable nor clickable. Can switch to <a href="?dynamic=true">dynamic version</a> which, while interactive, is slow.
	</div>

	<p>Red ares with images, Green are squares without</p>
	<img src="coveragemap.png.php">
	
<? } else { 

##################

?>

<div class="warning">
	This is only a prototype map. Because there are so many points the map is very slow and cumbersome. A real portal should have a better optimised map!
</div>

<p><img src="dot_red.gif"> A square with photo(s), and <img src="dot_green.gif"> no photos in the square. Click a red dot to view images</p>

    <div id="map-canvas" style="width:700px;height:800px;">Please wait... (this map may take a LONG time to show!</div>


 <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
  <script>
var map;

var pointData = [<?

$rows = $db->getAll("SELECT grid_reference,wgs84_lat,wgs84_long,images FROM {$db->table_square}") ;

$sep = '';
foreach ($rows as $row) {
	print $sep."new google.maps.LatLng({$row['wgs84_lat']}, {$row['wgs84_long']})\n";
	$sep = ',';
} ?>];

var redicon= new google.maps.MarkerImage('dot_red.gif',null,null,null,new google.maps.Size(10,10));
var greenicon= new google.maps.MarkerImage('dot_green.gif',null,null,null,new google.maps.Size(10,10));

function createMarker(grid_reference,wgs84_lat,wgs84_long,images) {
  var marker = new google.maps.Marker({
    position: new google.maps.LatLng(wgs84_lat, wgs84_long),
    icon: images?redicon:greenicon,
    map: map,
    title: grid_reference+' ('+images+' images)'
  });

  google.maps.event.addListener(marker, 'click', function() {
    window.open('./images.php?gridref='+grid_reference,'_blank');
  });

}

function initialize() {
  var bounds = new google.maps.LatLngBounds();
  for(q=0;q<pointData.length;q++)
    bounds.extend(pointData[q]);

  var mapOptions = {
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

  map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);
  map.fitBounds(bounds);

  <?
  foreach ($rows as $row) {
        print "createMarker('{$row['grid_reference']}', {$row['wgs84_lat']}, {$row['wgs84_long']}, {$row['images']})\n";
  } ?>

}

google.maps.event.addDomListener(window, 'load', initialize);

    </script>

<?
}
################


include "templates/footer.inc.php";

