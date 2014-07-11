<?

include "includes/start.inc.php";

include "templates/header.inc.php";

print '<h3><b>HeatMap</b> / <a href="breakdown.php?'.http_build_query($_GET).'">Breakdown</a></h3>';

include "includes/filter.inc.php";

?>

    <div id="map-canvas" style="width:700px;height:800px;"></div>


 <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=visualization"></script>
  <script>
var map, pointArray, heatmap;

var pointData = [<?
foreach ($db->getAll("SELECT wgs84_lat,wgs84_long FROM {$db->table_image} i $tables WHERE $where") as $row) {
	print $sep."new google.maps.LatLng({$row['wgs84_lat']}, {$row['wgs84_long']})\n";
	$sep = ',';
} ?>];

function initialize() {
  var mapOptions = {
    zoom: 6,
    center: new google.maps.LatLng(53.59967,-4.54605),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

  map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);

  var pointArray = new google.maps.MVCArray(pointData);

  heatmap = new google.maps.visualization.HeatmapLayer({
    data: pointArray
  });

  heatmap.setMap(map);
}




google.maps.event.addDomListener(window, 'load', initialize);

    </script>


<?

include "templates/footer.inc.php";

