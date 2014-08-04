<?

include "includes/start.inc.php";

customExpiresHeader(3600);
customGZipHandlerStart();

include "templates/header.inc.php";

print '<h3><b>HeatMap</b></h3>';

include "includes/filter.inc.php";

?>

    <div id="map-canvas" style="width:700px;height:800px;">Please wait... </div>


 <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=visualization"></script>

<? if (!empty($CONV['map_show_gb_grid'])) { ?>
<script type="text/javascript" src="includes/grid-projection.js"></script>
<script type="text/javascript" src="includes/v3_uk_grat.js"></script>
<? } ?>

  <script>
var map, pointArray, heatmap;

var grid;
var proj;


var pointData = [<?
foreach ($db->getAll("SELECT wgs84_lat,wgs84_long FROM {$db->table_image} i $tables WHERE $where") as $row) {
	print $sep."new google.maps.LatLng({$row['wgs84_lat']}, {$row['wgs84_long']})\n";
	$sep = ',';
} ?>];

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

<? if (!empty($CONV['map_show_gb_grid'])) { ?>
    proj = new GridProjection();
    proj.initialize();

    grid = new OgbGrat3(map, proj);
<? } ?>

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

