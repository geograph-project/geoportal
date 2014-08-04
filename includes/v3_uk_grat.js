/* OGB Graticule for GMaps V3 API, port of Bill Chadwick's V2 graticule by Bill Chadwick */

function OgbGrat3(map, gridProj) {

    // Now initialize all properties.
    this.map_ = map;
    //array for lines 
    this.lines_ = new Array();

    this.drawFirst_ = true;
    this.lstnIdle_ = null;
    this.lstnType_ = null;
    this.lstnZoom_ = null;

    this.gridProj_ = gridProj; // an initiailised instance of GridProjection

    // call setMap on this overlay
    this.setMap(map);
}

OgbGrat3.prototype = new google.maps.OverlayView();

OgbGrat3.prototype.onAdd = function() {


    // Create the DIV 
    var div = document.createElement('DIV');
    div.style.border = "none";
    div.style.borderWidth = "0px";
    div.style.position = "absolute";
    this.set('container', div);

    // We add our overlay to the same pane as the map (low z).
    var panes = this.getPanes();
    panes.mapPane.appendChild(div);
}

//This normally does nothing due to reentrancy problems and problems removing overlays from within an overlay
//Instead we use the idle event to trigger a redraw, this event occurs after zoom and map type changes
OgbGrat3.prototype.draw = function() {

    var self = this;

    //but draw it the very first time
    if (this.drawFirst_) {
        this.safeRedraw();

        function redraw() {
            self.safeRedraw();
        }

        function undraw() {
            self.unDraw();
        }

        // We use the idle event to trigger redraw
        this.lstnIdle_ = google.maps.event.addListener(this.getMap(), 'idle', redraw);

        // We use the type changed event to trigger a redraw too
        this.lstnType_ = google.maps.event.addListener(this.getMap(), "maptypeid_changed", redraw);

        // Hide labels when zooming
        this.lstnZoom_ = google.maps.event.addListener(this.getMap(), "zoom_changed", undraw);

        this.drawFirst_ = false;
    }
}

OgbGrat3.prototype.onRemove = function() {

    this.get('container').parentNode.removeChild(this.get('container'));
    this.set('container', null);

    for (var i = 0; i < this.lines_.length; i++) {
        this.lines_[i].setMap(null);
        this.lines_[i] = null;
    }

    //remove handlers we use to trigger redraw / undraw / hide
    if (this.lstnIdle_ != null)
        google.maps.event.removeListener(this.lstnIdle_);
    if (this.lstnType_ != null)
        google.maps.event.removeListener(this.lstnType_);
    if (this.lstnZoom_ != null)
        google.maps.event.removeListener(this.lstnZoom_);

}

OgbGrat3.prototype.hide = function() {
    if (this.get('container')) {
        this.get('container').style.visibility = "hidden";
    }
}

OgbGrat3.prototype.show = function() {
    if (this.get('container')) {
        this.get('container').style.visibility = "visible";
    }
}

OgbGrat3.prototype.unDraw = function() {

    try {

        for (i = 0; i < this.lines_.length; i++) {
            this.lines_[i].setMap(null);
            this.lines_[i] = null;
        }

        var container = this.get('container');
        while (container.hasChildNodes())
            container.removeChild(container.firstChild);

    }
    catch (e) {
    }
}

// Redraw the graticule based on the current projection and zoom level
OgbGrat3.prototype.safeRedraw = function() {

    //clear old
    this.unDraw();

    //best color for writing on the map
    function _bestTextColor(overlay) {
        var type = overlay.getMap().getMapTypeId();
        var GMM = google.maps.MapTypeId;
        if (type === GMM.HYBRID) return '#fff';
        if (type === GMM.ROADMAP) return '#6476fc';
        if (type === GMM.SATELLITE) return '#fff';
        if (type === GMM.TERRAIN) return '#6476fc';
        var mt = overlay.getMap().mapTypes[type];
        return (mt.textColor) ? mt.textColor : '#fff';
    };
    this.color_ = _bestTextColor(this);

    //determine graticule interval
    var bnds = this.map_.getBounds();

    if (!bnds) {
        // The map is not ready yet.
        return;
    }

    var sw = bnds.getSouthWest(),
        ne = bnds.getNorthEast();
    var l = sw.lng(),
        b = sw.lat(),
        r = ne.lng(),
        t = ne.lat();

    //sanity - limit to os grid area
    if (t < 49.0)
        return;
    if (b > 61.0)
        return;
    if (r < -8.0)
        return;
    if (l > 2.0)
        return;

    //grid interval in km   

    var d = 100.0;
    switch (this.map_.getZoom()) // use same interval as Google's scale bar
    {
        case 5:
            d = 100.0;
            break;
        case 6:
            d = 100.0;
            break;
        case 7:
            d = 50.0;
            break;
        case 8:
            d = 20.0;
            break;
        case 9:
            d = 20.0;
            break;
        case 10:
            d = 10.0;
            break;
        case 11:
            d = 5.0;
            break;
        case 12:
            d = 2.0;
            break;
        case 13:
            d = 1.0;
            break;
        case 14:
            d = 0.5;
            break;
        case 15:
            d = 0.2;
            break;
        case 16:
            d = 0.1;
            break;
        case 17:
            d = 0.05;
            break;
        case 18:
            d = 0.02;
            break;
        case 19:
            d = 0.01;
            break;
        case 20:
            d = 0.01;
            break;
        case 21:
            d = 0.01;
            break;
        default:
            return;
    }

    function latLngToPixel(overlay, ll) {
        return overlay.getProjection().fromLatLngToDivPixel(ll);
    };

    function llFromKm(proj, ekm, nkm) {
        return proj.getLonLatFromOgbPoint(new OgbPoint(ekm * 1000.0, nkm * 1000.0));
    }

    function lable(x, y, v, c) {
        var dv = document.createElement("DIV");
        dv.style.position = "absolute";
        dv.style.left = x.toString() + "px";
        dv.style.top = y.toString() + "px";
        dv.style.color = c;
        dv.style.fontFamily = 'Arial';
        dv.style.fontSize = '1.0em';
        var km = (Math.round(v) % 100).toString();
        if (km.length < 2)
            km = "0" + km;

        if (d < 0.1) {
            km = (Math.round(v * 100) % 10000).toString();
            if (km.length < 4)
                km = "0" + km;
            if (km.length < 4)
                km = "0" + km;
            if (km.length < 4)
                km = "0" + km;
            km = km.substr(0, 2) + "." + km.substr(2, 2);
        }
        else if (d < 1.0) {
            km = (Math.round(v * 10) % 1000).toString();
            if (km.length < 3)
                km = "0" + km;
            if (km.length < 3)
                km = "0" + km;
            km = km.substr(0, 2) + "." + km.substr(2, 1);
        }
        else if (d >= 100.0) {
            km = "";
        }

        dv.innerHTML = km;

        return dv;

    };

    //find enclosing OGB rectangle (in km) of WGS 84 rectangle
    var blEN = this.gridProj_.getOgbPointFromLonLat(new google.maps.LatLng(b, l));
    var trEN = this.gridProj_.getOgbPointFromLonLat(new google.maps.LatLng(t, r));
    var brEN = this.gridProj_.getOgbPointFromLonLat(new google.maps.LatLng(b, r));
    var tlEN = this.gridProj_.getOgbPointFromLonLat(new google.maps.LatLng(t, l));
    var west = Math.min(blEN.east, tlEN.east) / 1000.0;
    var south = Math.min(blEN.north, brEN.north) / 1000.0;
    var east = Math.max(brEN.east, trEN.east) / 1000.0;
    var north = Math.max(trEN.north, tlEN.north) / 1000.0;

    //round iteration limits to the computed grid interval
    east = Math.ceil(east / d) * d;
    west = Math.floor(west / d) * d;
    north = Math.ceil(north / d) * d;
    south = Math.floor(south / d) * d;

    //Sanity / limit
    if (west <= 0.0)
        west = 0.0;
    if (east >= 700.0)
        east = 700.0;
    if (south < 0.0)
        south = 0.0;
    if (north > 1300.0)
        north = 1300.0;

    this.lines_ = new Array();
    var i = 0;

    //pane/layer to write on
    var mapDiv = this.get('container');

    //horizontal lines
    var s = south;
    while (s <= north) {

        var pts = new Array();
        //under 10km grid squares draw as straight line  
        if (d < 10.0) {
            pts[0] = llFromKm(this.gridProj_, east, s);
            pts[1] = llFromKm(this.gridProj_, west, s);
        }
        //over 10km grid squares draw as set of segments
        else {
            var e = west;
            var q = 0;
            while (e <= east) {
                pts[q] = llFromKm(this.gridProj_, e, s);
                q++;
                e += d;
            }
        }

        //line
        if (pts.length > 0) {
            this.lines_[i] = new google.maps.Polyline({ path: pts, strokeColor: this.color_, strokeWeight: 1, clickable: false });
            this.lines_[i].setMap(this.map_);
            i++;
        }

        //label at height of second horz line
        try {
            var p = latLngToPixel(this, this.gridProj_.getLonLatFromOgbPoint(new OgbPoint((west + d + d) * 1000.0, s * 1000.0)));
            var dv = lable(p.x + 3, p.y, s, this.color_);
            mapDiv.appendChild(dv);
        }
        catch (ex) {
        }

        s += d;
    }


    //vertical lines
    var e = west;
    while (e <= east) {

        var pts2 = new Array();

        //under 10km grid squares draw as straight line 
        if (d < 10.0) {
            pts2[0] = llFromKm(this.gridProj_, e, north);
            pts2[1] = llFromKm(this.gridProj_, e, south);
        }
        //over 10km grid squares draw as set of segments
        else {
            var s = south;
            var q = 0;
            while (s <= north) {
                pts2[q] = llFromKm(this.gridProj_, e, s);
                q++;
                s += d;
            }
        }

        //line
        if (pts.length > 0) {
            this.lines_[i] = new google.maps.Polyline({ path: pts2, strokeColor: this.color_, strokeWeight: 1, clickable: false });
            this.lines_[i].setMap(this.map_);
            i++;
        }

        //label on second vert line 
        try {
            var p = latLngToPixel(this, this.gridProj_.getLonLatFromOgbPoint(new OgbPoint(e * 1000.0, (south + d + d) * 1000.0)));
            if (e != (west + d + d)) {
                var dv = lable(p.x, p.y + 3, e, this.color_);
                mapDiv.appendChild(dv);
            }
        }
        catch (ex) {
        }

        e += d;

    }

    this.show();

}

