/**
 * Crown Copyright (c) 2009, Secretary of State for Communities and Local Government,
 * acting through Ordnance Survey.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * 
 *   Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * 
 *   Neither the name of the Secretary of State for Communities and Local Government,
 *   acting through Ordnance Survey nor the names of its contributors
 *   may be used to endorse or promote products derived from this software
 *   without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 */


function OgbPoint(e,n) {
    this.east = e;
    this.north = n;
}

/**
 *  
 * Class: OpenSpace.GridProjection
 * Enables transformation from WGS84 to British National Grid and
 * from British National Grid to WGS84.
 * 
 *
 */
 function GridProjection(){
 }

    /**
     * Constructor: OpenSpace.GridProjection
     * Create a new OpenSpace.GridProjection instance
     *
     * Parameters:
     *
     * Returns:
     * An instance of OpenSpace.GridProjection
     */
    GridProjection.prototype.initialize = function()
    {
        this.DEG_TO_RAD = Math.PI / 180.0;
        this.a = 6378137.0; /* semi-major axis */
        this.b = 6356752.3141; /* semi-minor axis */
        this.f = 1.0 - this.b / this.a; /* flattening */
        this.e2 = 1.0 - (this.b * this.b) / (this.a * this.a); /* eccentricity squared */
        this.lat0 = 49.0 * this.DEG_TO_RAD; /* latitude  of origin, in radians */
        this.lon0 = -2.0 * this.DEG_TO_RAD; /* longitude of origin, in radians */
        this.falseE = 400000.0; /* false origin eastings */
        this.falseN = -100000.0; /* false origin northings */
        this.scl = 0.9996012717; /* scale factor */
        this.ety = (this.a - this.b) / (this.a + this.b); /* ellipticity */
        this.aS = this.a * this.scl; /* scaled major_axis */
        this.bS = this.b * this.scl; /* scaled minor axis */
        this.low_res_east_shift = new Array(3);
        var i;
        for (i = 0; i < 3; i++) 
        {
            this.low_res_east_shift[i] = new Array(5);
        }
        this.low_res_north_shift = new Array(3);
        for (i = 0; i < 3; i++) 
        {
            this.low_res_north_shift[i] = new Array(5);
        }
        
        this.init_low_res_data();
    };
    
    /* Sets up the low resolution data                         */
    /* Initialises array with:                                 */
    /*    low_res_east_shift                                   */
    /*    low_res_north_shift                                  */
    /* These are of type int and may be accessed as 2D arrays, */
    /* of dimension [3][5] using (eg):                         */
    /*    low_res_east_shift[east_index][north_index]          */
    GridProjection.prototype.init_low_res_data = function() {
        this.low_res_east_shift[0][0] = 92;
        this.low_res_east_shift[0][1] = 89;
        this.low_res_east_shift[0][2] = 85;
        this.low_res_east_shift[0][3] = 93;
        this.low_res_east_shift[0][4] = 99;
        this.low_res_east_shift[1][0] = 96;
        this.low_res_east_shift[1][1] = 96;
        this.low_res_east_shift[1][2] = 97;
        this.low_res_east_shift[1][3] = 99;
        this.low_res_east_shift[1][4] = 104;
        this.low_res_east_shift[2][0] = 102;
        this.low_res_east_shift[2][1] = 105;
        this.low_res_east_shift[2][2] = 108;
        this.low_res_east_shift[2][3] = 106;
        this.low_res_east_shift[2][4] = 107;

        this.low_res_north_shift[0][0] = -82;
        this.low_res_north_shift[0][1] = -75;
        this.low_res_north_shift[0][2] = -58;
        this.low_res_north_shift[0][3] = -47;
        this.low_res_north_shift[0][4] = -44;
        this.low_res_north_shift[1][0] = -80;
        this.low_res_north_shift[1][1] = -75;
        this.low_res_north_shift[1][2] = -62;
        this.low_res_north_shift[1][3] = -52;
        this.low_res_north_shift[1][4] = -49;
        this.low_res_north_shift[2][0] = -82;
        this.low_res_north_shift[2][1] = -78;
        this.low_res_north_shift[2][2] = -62;
        this.low_res_north_shift[2][3] = -54;
        this.low_res_north_shift[2][4] = -52;
    };
 
    /**
     * APIMethod: getOgbPointFromLonLat
     * Convert an WGS84 point into a OSGB36 grid point
     * 
     * Parameters:
     * pt_LonLat - {<google.maps.LatLng>} The point to convert
     * 
     * Returns:
     * {<OgbPoint>} The point converted into OSGB36 British National Grid
     */
    GridProjection.prototype.getOgbPointFromLonLat = function(pt_LonLat) {
        var pt_ETRS89 = this.geog2Grid(pt_LonLat);
        var pt_OSGB36 = this.convert_89_36(pt_ETRS89);
        return pt_OSGB36;
    };
     
    /**
     * APIMethod: getLonLatFromOgbPoint
     * Convert a OSGB36 grid point into a WGS84 lon lat point
     * 
     * Parameters:
     * pt_OSGB36 - {<OgbPoint>} The point to convert
     * 
     * Returns:
     * {<google.maps.LatLng>} The point converted into WGS84 Longitude Latitude
     */    
    GridProjection.prototype.getLonLatFromOgbPoint = function(pt_OSGB36)
    {        
        // Transform to ETRS89 first
        var pt_ETRS89 = this.convert_36_89(pt_OSGB36);
        
        // Then project back onto WGS84 ellipsoid
        var pt_LonLat = this.E_N_to_LonLat(pt_ETRS89);

        return pt_LonLat;
    };

    /**
    * APIMethod: gridRefToEastNorth
    * Convert a grid reference string to Eastings and Northings
    * 
    * Parameters:
    * ngr - {<string>} The grid reference to convert
    * 
    * Returns:
    * {<OgbPoint>} The grid ref converted into easting and northings
    */
    GridProjection.prototype.gridRefToEastNorth = function (ngr) {
        var e;
        var n;

        ngr = ngr.toUpperCase(ngr);

        var bits = ngr.split(' ');
        ngr = "";
        for (var i = 0; i < bits.length; i++)
            ngr += bits[i];

        var c = ngr.charAt(0);
        if (c == 'S') {
            e = 0;
            n = 0;
        }
        else if (c == 'T') {
            e = 500000;
            n = 0;
        }
        else if (c == 'N') {
            n = 500000;
            e = 0;
        }
        else if (c == 'O') {
            n = 500000;
            e = 500000;
        }
        else if (c == 'H') {
            n = 1000000;
            e = 0;
        }
        else
            return null;

        c = ngr.charAt(1);
        if (c == 'I')
            return null;

        c = ngr.charCodeAt(1) - 65;
        if (c > 8)
            c -= 1;
        e += (c % 5) * 100000;
        n += (4 - Math.floor(c / 5)) * 100000;

        c = ngr.substr(2);
        if ((c.length % 2) == 1)
            return null;
        if (c.length > 10)
            return null;

        try {
            var s = c.substr(0, c.length / 2);
            while (s.length < 5)
                s += '0';
            e += parseInt(s, 10);
            if (isNaN(e))
                return null;

            s = c.substr(c.length / 2);
            while (s.length < 5)
                s += '0';
            n += parseInt(s, 10);
            if (isNaN(n))
                return null;

            return new OgbPoint(e, n);
        }
        catch (ex) {
            return null;
        }

    }
    
    
    
    /**
     * Method: os_arc
     * Internal conversion method
     * 
     * Parameters:
     * k3 - {Float}
     * k4 - {Float}
     * 
     * Returns:
     * {Float}
     */  
    GridProjection.prototype.os_arc = function(k3, k4)
    {
        var j3 = (((this.ety + 1.0) * this.ety * 5.0 /
                  4.0 + 1.0) *
                    this.ety + 1.0) * k3;
        var j4 = ((21.0 * this.ety / 8.0 + 3.0) *
                  this.ety + 3.0) *
                  this.ety * Math.sin(k3) * Math.cos(k4);
        var j5 = this.ety * (this.ety + this.ety * this.ety) *
                 Math.sin(2.0 * k3) * Math.cos(2.0 * k4) * 15.0 / 8.0;
        var j6 = this.ety * this.ety * this.ety * Math.sin(3.0 * k3) *
                 Math.cos(3.0 * k4) * 35.0 / 24.0;
        return (this.bS * (j3 - j4 + j5 - j6));
    };
    
    /**
     * Method: geog2Grid
     * Internal conversion method
     * 
     * Parameters:
     * pt_LonLat - {<google.maps.LatLng>} The point to convert
     * 
     * Returns:
     * {<OgbPoint>} The point converted
     */   
    GridProjection.prototype.geog2Grid = function(pt_LonLat)
    {
        
        // Convert degrees to radians
        var lat_rad = pt_LonLat.lat() * this.DEG_TO_RAD;
        var lon_rad = pt_LonLat.lng() * this.DEG_TO_RAD;
           
        // Set up parameters for projection algorithm
        // see OS leaflet "Mercator Projection, constants formulae and
        // methods" for details
        
        var k3 = lat_rad - this.lat0;
        var k4 = lat_rad + this.lat0;
        var tan_k = Math.tan(lat_rad);
        var tan_k_2 = tan_k * tan_k;
        var sin_k = Math.sin(lat_rad);
        var cos_k = Math.cos(lat_rad);
        var cos_k_3 = cos_k * cos_k * cos_k;
        var cos_k_5 = cos_k * cos_k * cos_k_3;
        
        var m = this.os_arc(k3, k4);
        
        var v = this.aS / Math.sqrt(1.0 - this.e2 * sin_k * sin_k);
        var v_3 = v * v * v;
        var v_5 = v_3 * v * v;
        var v_7 = v_5 * v * v;
        var r = v * (1.0 - this.e2) / (1.0 - this.e2 * sin_k * sin_k);
        var h2 = v / r - 1.0;
        
        var p = lon_rad - this.lon0;
        var j3 = m + this.falseN;
        var j4 = v * sin_k * cos_k / 2.0;
        var j5 = v * sin_k * cos_k_3 * (5.0 - tan_k_2 + 9.0 * h2) / 24.0;
        var j6 = v * sin_k * cos_k_5 * ((tan_k_2 - 58.0) * tan_k_2 + 61.0) / 720.0;
        var gridPointLat = ((j6 * p * p + j5) * p * p + j4) * p * p + j3;
        var j7 = v * cos_k;
        var j8 = v * cos_k_3 * (v / r - tan_k_2) / 6.0;
        var j9 = v * cos_k_5 / 120.0;
        j9 = j9 * ((tan_k_2 - 58.0 * h2 - 18.0) * tan_k_2 + 5.0 + 14.0 * h2);

        var gridPointLon = ((j9 * p * p + j8) * p * p + j7) * p + this.falseE;

        var gridPoint = new OgbPoint(gridPointLon, gridPointLat);
               
        return gridPoint;
    };
    
    /**
     * Method: convert_89_36
     * Convert an ETRS89 point into a OSGB36 point
     * 
     * Parameters:
     * pt_ETRS89 - {<OgbPoint>} The point to convert
     * 
     * Returns:
     * {<OgbPoint>} The point converted into British National Grid
     */
    GridProjection.prototype.convert_89_36 = function(pt_ETRS89) {
        var e = new Array(4);
        var n = new Array(4);
        var dxs = new Array(4);
        var dys = new Array(4);

        var spacing = 350000.0;

        // calculate the appropriate position in the grid
        var east_index = Math.floor(pt_ETRS89.east / spacing);
        var north_index = Math.floor(pt_ETRS89.north / spacing);

        e[0] = e[3] = east_index;
        e[1] = e[2] = east_index + 1;
        n[0] = n[1] = north_index;
        n[2] = n[3] = north_index + 1;

        var i;
        for (i = 0; i < 4; i++) {
            if (e[i] < 0)
                e[i] = 0;
            if (e[i] > 2)
                e[i] = 2;
            if (n[i] < 0)
                n[i] = 0;
            if (n[i] > 4)
                n[i] = 4;
            //if (e[i] >= 0 && e[i] <= 2 && n[i] >= 0 && n[i] <= 4) 
            //{
            dxs[i] = this.low_res_east_shift[e[i]][n[i]];
            dys[i] = this.low_res_north_shift[e[i]][n[i]];
            //}
            //else 
            //{
            //    dxs[i] = 0;
            //    dys[i] = 0;
            //}
        }

        // calculate shifts using bilinear interpolation                
        var shiftX = this.bilinear(dxs, east_index * spacing, east_index * spacing + spacing, north_index * spacing, north_index * spacing + spacing, pt_ETRS89.east, pt_ETRS89.north);

        var shiftY = this.bilinear(dys, east_index * spacing, east_index * spacing + spacing, north_index * spacing, north_index * spacing + spacing, pt_ETRS89.east, pt_ETRS89.north);

        // return OSGB36 position
        var output = new OgbPoint(pt_ETRS89.east + shiftX, pt_ETRS89.north + shiftY);


        return output;
    };
    
    /**
     * Method: E_N_to_LonLat
     * Convert an ETRS89 point into a WGS84 point
     * 
     * Parameters:
     * pt_ETRS89 - {<OgbPoint>} The point to convert
     * 
     * Returns:
     * {<google.maps.LatLng>} The point converted into WGS84
     */
    GridProjection.prototype.E_N_to_LonLat = function(pt_ETRS89)
    {
        // Un-project Transverse Mercator eastings and northings back to latitude.
        // Input:
        //   eastings (East) and northings (North) in meters;
        //   ellipsoid axis dimensions (a & b) in meters;
        //   eastings (e0) and northings (n0) of false origin in meters;
        //   central meridian scale factor (f0) and
        //   latitude (PHI0) and longitude (LAM0) of false origin in decimal degrees.
        
        // REQUIRES THE "Marc" AND "InitialLat" FUNCTIONS
        
        // Convert angle measures to radians
        var RadPHI0 = this.lat0;
        var RadLAM0 = this.lon0;
        
        // Compute af0, bf0, e squared (e2), n and Et
        var af0 = this.a * this.scl;
        var bf0 = this.b * this.scl;
        var e2 = ((af0 * af0) - (bf0 * bf0)) / (af0 * af0);
        var n = (af0 - bf0) / (af0 + bf0);
        var Et = pt_ETRS89.east - this.falseE;
        

        // Compute initial value for latitude (PHI) in radians
        var PHId = this.initialLat(pt_ETRS89.north, this.falseN, af0, RadPHI0, n, bf0);
        
        // Compute nu, rho and eta2 using value for PHId
        var sinPHId = Math.sin(PHId);
        var sinPHId2 = sinPHId * sinPHId;
        var nu = af0 / (Math.sqrt(1.0 - (e2 * sinPHId2)));
        var rho = (nu * (1.0 - e2)) / (1.0 - (e2 * sinPHId2));
        var eta2 = (nu / rho) - 1.0;
        

        // Compute Latitude
        var tanPHId = Math.tan(PHId);
        var tanPHId2 = tanPHId * tanPHId;
        var tanPHId4 = tanPHId2 * tanPHId2;
        var tanPHId6 = tanPHId4 * tanPHId2;
        
        var VII = (tanPHId) / (2 * rho * nu);
        var VIII = (tanPHId / (24 * rho * (nu * nu * nu))) * (5 + (3 * tanPHId2) + eta2 - (9 * eta2 * tanPHId2));
        var IX = (tanPHId / (720 * rho * (nu * nu * nu * nu * nu))) * (61 + (90 * tanPHId2) + (45 * tanPHId4));
        var E_N_to_Lat = (180 / Math.PI) *
        (PHId - ((Et * Et) * VII) +
        ((Et * Et * Et * Et) * VIII) -
        ((Et * Et * Et * Et * Et * Et) * IX));
        
        // Compute Longitude
        var cosPHId = Math.cos(PHId);
        var cosPHId_1 = 1.0 / cosPHId;
        
        var X = cosPHId_1 / nu;
        var XI = (cosPHId_1 / (6 * (nu * nu * nu))) * ((nu / rho) + (2 * tanPHId2));
        var XII = (cosPHId_1 / (120 * (nu * nu * nu * nu * nu))) * (5 + (28 * tanPHId2 + (24 * tanPHId4)));
        var XIIA = (cosPHId_1 / (5040 * (nu * nu * nu * nu * nu * nu * nu))) * (61 + (662 * tanPHId2 + (1320 * tanPHId4 + (720 * tanPHId6))));
        

        var E_N_to_Lng = (180 / Math.PI) * (RadLAM0 + (Et * X) - ((Et * Et * Et) * XI) + ((Et * Et * Et * Et * Et) * XII) - ((Et * Et * Et * Et * Et * Et * Et) * XIIA));
        var pt_LonLat = new google.maps.LatLng(E_N_to_Lat, E_N_to_Lng);
        
        return pt_LonLat;
    };
    
    /**
     * Method: initialLat
     * Internal conversion method
     * 
     * Parameters:
     * 
     * Returns:
     * {Float}
     */
    GridProjection.prototype.initialLat = function(North, n0, afo, PHI0, n, bfo)
    {
        // Compute initial value for Latitude (PHI) IN RADIANS.
        // Input:
        //   northing of point (North) and northing of false origin (n0) in meters;
        //   semi major axis multiplied by central meridian scale factor (af0) in meters;
        //   latitude of false origin (PHI0) IN RADIANS;
        //   n (computed from a, b and f0) and
        //   ellipsoid semi major axis multiplied by central meridian scale factor (bf0) in meters.
        
        // REQUIRES THE "Marc" FUNCTION
        
        // First PHI value (PHI1)
        var PHI1 = ((North - n0) / afo) + PHI0;
        
        // Calculate M
        var M = this.marc(bfo, n, PHI0, PHI1);
        
        // Calculate new PHI value (PHI2)
        var PHI2 = ((North - n0 - M) / afo) + PHI1;
        

        // Iterate to get final value for InitialLat
        while (Math.abs(North - n0 - M) > 0.000001) 
        {
            PHI2 = ((North - n0 - M) / afo) + PHI1;
            M = this.marc(bfo, n, PHI0, PHI2);
            

            PHI1 = PHI2;
        }
        
        return PHI2;
    };
    
    /**
     * Method: marc
     * Internal conversion method to Compute meridional arc.
     * 
     * Parameters:
     * 
     * Returns:
     * {Float}
     */
    GridProjection.prototype.marc = function(bf0, n, PHI0, PHI)
    {
        // Compute meridional arc.
        // Input: -
        //   ellipsoid semi major axis multiplied by central meridian scale factor (bf0) in meters;
        //   n (computed from a, b and f0);
        //   lat of false origin (PHI0) and initial or final latitude of point (PHI) IN RADIANS.
        
        // THIS FUNCTION IS CALLED BY THE - _
        // "Lat_Long_to_North" and "InitialLat" FUNCTIONS
        // THIS FUNCTION IS ALSO USED ON IT'S OWN IN THE "Projection and Transformation Calculations.xls" SPREADSHEET
        
        var marc = bf0 *
        (((1.0 + n + ((5.0 / 4.0) * (n * n)) + ((5.0 / 4.0) * (n * n * n))) * (PHI - PHI0)) -
        (((3.0 * n) + (3.0 * (n * n)) + ((21.0 / 8.0) * (n * n * n))) * Math.sin(PHI - PHI0) * Math.cos(PHI + PHI0)) +
        ((((15.0 / 8.0) * (n * n)) + ((15.0 / 8.0) * (n * n * n))) * Math.sin(2.0 * (PHI - PHI0)) * Math.cos(2.0 * (PHI + PHI0))) -
        (((35.0 / 24.0) * (n * n * n)) * Math.sin(3.0 * (PHI - PHI0)) * Math.cos(3.0 * (PHI + PHI0))));
        return marc;
    };
    
    /**
     * Method: convert_36_89
     * Convert an OSGB36 point into an ETRS89 point
     * 
     * Parameters:
     * pt_OSGB36 - {<OgbPoint>} The point to convert
     * 
     * Returns:
     * {<OgbPoint>} The point converted into ETRS89
     */  
    GridProjection.prototype.convert_36_89 = function(pt_OSGB36)
    {
        // Takes OSGB36 plane co-ordinates and calculates ETRF89 plane co-ordinates
        // resolution should be HIGH_RES or LOW_RES
        // Solves a quadratic to reverse the bilnear interpolation process        
        
        var dxs = new Array(4);
        var dys = new Array(4);
        var v1x;
        var v1y;
        var v2x;
        var v2y;
        var local_pt = new OgbPoint(0,0);
        var i;
        var out_of_range = false;
        var a;
        var b;
        var AA;
        var BB;
        var CC;
        var old_a;
        var f_x;
        var f_dx;
        var s;
        var dn;
        var de;
        var t1;
        var t2;
        var t3;
        var t5;
        var l = new Array(7);
        var spacing = 350000.0;
        
        var gride = Math.floor(pt_OSGB36.east / spacing);
        var gridn = Math.floor(pt_OSGB36.north / spacing);
        // Loop through different gridsquares to find which parameters are applicable
        
        var indexX;
        var indexY;
        
        i = 0;
        while (i != 4) 
        {
            for (i = 0; i < 4; i++) 
            {
                indexX = gride + Math.floor(i / 2);
                indexY = gridn + Math.floor(((i + 1) % 4) / 2);
                if (indexX < 0)
                    indexX = 0;
                if (indexX > 2)
                    indexX = 2;
                if (indexY < 0)
                    indexY = 0;
                if (indexY > 4)
                    indexY = 4;    
                        
                if (indexX >= 0 && indexX <= 2 && indexY >= 0 && indexY <= 4) 
                {
                    dxs[i] = this.low_res_east_shift[indexX][indexY];
                    
                    //if (dxs[i] == 0) 
                    //    out_of_range = true;
                    //else 
                    //    out_of_range = false;
                    //if (out_of_range) 
                    //{
                    //}
                    
                    dys[i] = this.low_res_north_shift[indexX][indexY];
                    
                }
                else
                {
                    //OpenLayers.Console.log("index out of range: indexX: " + indexX + " indexY: " + indexY);
                }
            }
            
            // Check the cross products around the point to successive grid square corners
            //if (!out_of_range) 
            //{
                for (i = 0; i < 4; i++) 
                {
                    indexX = gride + Math.floor(i / 2);
                    indexY = gridn + Math.floor(((i + 1) % 4) / 2);
                    v1x = dxs[i] + spacing * indexX - pt_OSGB36.east;
                    v1y = dys[i] + spacing * indexY - pt_OSGB36.north;
                    indexX = gride + Math.floor(((i + 1) % 4) / 2);
                    indexY = gridn + 1 - Math.floor(i / 2);
                    v2x = dxs[(i + 1) % 4] + spacing * indexX - pt_OSGB36.east;
                    v2y = dys[(i + 1) % 4] + spacing * indexY - pt_OSGB36.north;
                    
                    // if greater than 0, the point lies on the outside of the square

                    if ((v1x * v2y - v2x * v1y) > 0.0) 
                        break;
                }

                switch (i)
                {
                    case 0:
                        gride--;
                        break;
                    case 1:
                        gridn++;
                        break;
                    case 2:
                        gride++;
                        break;
                    case 3:
                        gridn--;
                        break;
                    case 4:
                        break;
                }

            //}
        }
        
        // drops out of the loop when the appropriate grid square has been found
        
        // Take co-ordinates from an origin of the bottom-left corner of the gridsquare
        //if (!out_of_range) 
        //{
            local_pt.east = pt_OSGB36.east - gride * spacing;
            local_pt.north = pt_OSGB36.north - gridn * spacing;
            
            // The east shift (a) and north shift (b) are calculated by solving a set of 2
            // simultaneous equations.  This involves solving a quadratic for 'a', then
            // substituting this into the equation for 'b'.
            
            // First solve the quadratic for 'a'.
            // Quadratic is:  AAa^2 + BBa + CC  
            // The grid square in which the point was originally situated has shifts (d):
            //                              
            // gridn+1 _______________      
            //         |\           /|      
            //         | d[1]   d[2] |      
            //         |             |      
            //         |             |      
            //         |             |      
            //         | d[0]   d[3] |      
            //   gridn |/___________\|      
            //     gride         gride+1   
            //                              
            
            
            // The array l contains temporary variables used to simplify the calculations
            
            l[1] = dxs[0] - dxs[3];
            l[2] = dxs[1] - dxs[2];
            l[3] = dys[0] - dys[3];
            l[4] = dys[1] - dys[2];
            l[5] = dys[0] - dys[1];
            l[6] = dxs[0] - dxs[1];
            

            s = spacing;
            dn = local_pt.north - dys[0];
            de = local_pt.east - dxs[0];
            t1 = s - l[1];
            t2 = l[3] - l[4];
            t3 = l[1] - l[2];
            t5 = s - l[5];
            

            AA = (t1 * t2 + l[3] * t3) / s;
            BB = t3 * dn - l[3] * l[6] + t1 * t5 - t2 * de;
            CC = -s * dn * l[6] - s * de * (s - l[5]);
            
       
            
            if (AA < BB * 0.0000000001) 
            {
                if (BB == 0) 
                {
                    alert("Indeterminable equations");
                    return null;
                }
                a = -CC / BB;
            }
            else 
            {
                a = (-BB + Math.sqrt(BB * BB - 4.0 * AA * CC)) / (2.0 * AA);
            }
            
            // Quadratic is ill-conditioned, so use a Newton-Raphson iteration
            // to produce a better estimate for a
            
            for (i = 0; i < 10; i++) 
            {
                old_a = a;
                f_x = AA * old_a * old_a + BB * old_a + CC;
                f_dx = 2.0 * AA * old_a + BB;
                if (f_dx == 0.0) 
                    break;
                a = old_a - (f_x / f_dx);
                if (Math.abs(old_a - a) < 0.00001) 
                    break;
            }
            
            // Having found the east shift (a), substitute into the second equation to give
            // the north shift (b)
            
            b = ((s * s) * local_pt.north - (s * (s * dys[0] - a * l[3]))) /
            ((s * s) - (s * l[5] - a * t2));

            var pt_ETRS89 = new OgbPoint(a + (gride * spacing), b + (gridn * spacing));
                        
           
        //}
        return pt_ETRS89;
    };
    
    /**
     * Method: bilinear
     * Internal interpolation method.
     * 
     * Parameters:
     * 
     * Returns:
     * {Float}
     */
    GridProjection.prototype.bilinear= function(y,  x1l, x1u,  x2l,  x2u, x1,  x2)
    {
        var d1 = x1u - x1l;
        var d2 = x2u - x2l;
        var t = (x1 - x1l) / d1;
        var u = (x2 - x2l) / d2;
        return (1.0 - t) * (1.0 - u) * y[0] + t * (1.0 - u) * y[1] + t * u * y[2] + (1.0 - t) * u * y[3];
    };

    /**
    * APIMethod: gridRefFromEastNorth
    * Convert Eastings and Northings to a Grid Ref
    * 
    * Parameters:
    * e,n eastings and northings in metres
    * digits for the GR, range 1-5
    * 
    * Returns:
    * grid ref as string
    */

    GridProjection.prototype.gridRefFromEastNorth = function(e, n, digits) {

        //convert northing and easting to letter and number grid system
        //digits 1-5, e & n in metres


        var res = 1;
        var fres = 1.0;
        switch (digits) {
            case 4:
                res = 10;
                fres = 10.0;
                break;
            case 3:
                res = 100;
                fres = 100.0;
                break;
            case 2:
                res = 1000;
                fres = 1000.0
                break;
            case 1:
                res = 10000;
                fres = 10000.0;
                break;
            default:
                digits = 5;
        }

        var east = parseInt(Math.round(e / fres), 10) * res;
        var north = parseInt(Math.round(n / fres), 10) * res;

        var eX = east / 500000;
        var nX = north / 500000;
        var tmp = Math.floor(eX) - 5.0 * Math.floor(nX) + 17.0;
        nX = 5 * (nX - Math.floor(nX));
        eX = 20 - 5.0 * Math.floor(nX) + Math.floor(5.0 * (eX - Math.floor(eX)));
        if (eX > 7.5) eX = eX + 1; // I is not used
        if (tmp > 7.5) tmp = tmp + 1; // I is not used

        var eing = east - (Math.floor(east / 100000) * 100000);
        var ning = north - (Math.floor(north / 100000) * 100000);


        var estr = (eing / fres).toFixed(0);
        var nstr = (ning / fres).toFixed(0);
        while (estr.length < digits)
            estr = "0" + estr;
        while (nstr.length < digits)
            nstr = "0" + nstr;

        var ngr = String.fromCharCode(tmp + 65) +
          String.fromCharCode(eX + 65) +
          " " + estr + " " + nstr;
        return ngr;

    };

