<?

include "includes/start.inc.php";

customExpiresHeader(3600);
customGZipHandlerStart();

include "templates/header.inc.php";

print '<h3>Coverage Map</h3>';

print '<p>View <a href="squares.php">green squares as a list</a></p>';

#################

        $row = $db->getRow("SELECT COUNT(*) AS squares, SUM(images>0) AS photographed FROM {$db->table_square}");

        if (!empty($row['squares'])) {
		print "<div class=statbar>";
                $row['percentage'] = sprintf('%.1f',$row['photographed']/$row['squares']*100);
                print "Total Squares: ".dis($row['squares']).", of which ".dis($row['photographed'])." have photographs; which is a coverage of <b>{$row['percentage']}%</b>";
                print " <a href=statistics.php>MORE...</a>";
	        if (!empty($CONF['square_source'])) {
			print "<div class=source>".nl2br(MakeLinks(he($CONF['square_source'])))."</div>";
		}
                print "</div>";
        }

##################

if (empty($_GET['dynamic'])) { ?>

	<div class="warning">
		Note: This is only a quick plot. Its not zoomable nor clickable. Can switch to <a href="?dynamic=true">dynamic version</a> which, while interactive, is slow.
	</div>

	<p>Red are squares with images, Green are squares without. Switch to dynamic version to be able to click a point, or can view <a href="squares.php">green squares as in list form</a>.</p>
	<img src="coveragemap.png.php">
	
<? } else { 

##################

?>

<div class="warning">
	This is only a prototype map. Because there are so many points the map is very slow and cumbersome. A real portal should have a better optimised map!
</div>

<p><img src="templates/dot_red.gif"> A square with photo(s), and <img src="templates/dot_green.gif"> no photos in the square. Click a red dot to view images</p>

    <div id="map-canvas" style="width:700px;height:800px;float:left">Please wait... (this map may take a LONG time to show!</div>
    <div id="thumbbar" style="width:150px;margin-left:20px;float:left"></div>
	<br style=clear:both>

 <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>

<? if (!empty($CONV['map_show_gb_grid'])) { ?>
<script type="text/javascript" src="includes/grid-projection.js"></script>
<script type="text/javascript" src="includes/v3_uk_grat.js"></script>
<? } ?>

  <script>
var map;
	var grid;
	var proj;

var pointData = [<?

$where = "wgs84_lat != 0";
if (isset($_GET['images']))
	$where .= " AND images = ".intval($_GET['images']);

$rows = $db->getAll("SELECT grid_reference,wgs84_lat,wgs84_long,images FROM {$db->table_square} WHERE $where") ;

$sep = '';
foreach ($rows as $row) {
	print $sep."new google.maps.LatLng({$row['wgs84_lat']}, {$row['wgs84_long']})\n";
	$sep = ',';
} ?>];

var redicon= new google.maps.MarkerImage('templates/dot_red.gif',null,null,null,new google.maps.Size(10,10));
var greenicon= new google.maps.MarkerImage('templates/dot_green.gif',null,null,null,new google.maps.Size(10,10));

function createMarker(grid_reference,wgs84_lat,wgs84_long,images) {
  var marker = new google.maps.Marker({
    position: new google.maps.LatLng(wgs84_lat, wgs84_long),
    icon: images?redicon:greenicon,
    map: map,
    title: grid_reference+' ('+images+' images)'
  });

  google.maps.event.addListener(marker, 'click', function() {
    //window.open('./images.php?gridref='+grid_reference,'_blank');
    loadImages(grid_reference);
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

<? if (!empty($CONV['map_show_gb_grid'])) { ?>
    proj = new GridProjection();
    proj.initialize();

    grid = new OgbGrat3(map, proj);
<? }

  foreach ($rows as $row) {
        print "createMarker('{$row['grid_reference']}', {$row['wgs84_lat']}, {$row['wgs84_long']}, {$row['images']})\n";
  } ?>

}

google.maps.event.addDomListener(window, 'load', initialize);

    </script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
  <script>
	function loadImages(gridref) {
		$.getJSON( "square.ajax.php", {gridref:gridref}, function( data ) {
			if (data) {
				var ele = $('#thumbbar').empty()
					.append('<big><b>'+gridref+'</b></big> <br>')
					.append(' ('+data.images+' images) <br>');
				if (data.name)
					ele.append('<b>'+data.name+'</b> <br>');
				if (data.link) {
					var tmp = document.createElement('a');
					tmp.href = data.link;
					var link = tmp.hostname.replace(/^www./,'');
					ele.append('<a href="'+data.link+'">'+link+'</a> <br>');
				}
				if (data.domain)
					ele.append('<a href="http://'+data.domain+'/gridref/'+gridref+'">'+data.domain.replace(/^www./,'')+'</a> <br>');
				if (data.rows) {
					ele.append('<br>');
					jQuery.each( data.rows, function( i, v ) {
						ele.append('<div class="thumb"><a href="http://'+data.domain+'/photo/'+v.image_id+'" title="'+v.title+' by '+v.realname+'">'+
							'<img src="'+v.thumb+'"></a></div>');
					});
					if (data.rows.length < data.images) {
						ele.append('<a href="images.php?gridref='+gridref+'">view more...</a>');
					}
				}
			} else {
				$('#thumbbar').html("no results");
			}
		});
	}
  </script>


<?
}
################


include "templates/footer.inc.php";

