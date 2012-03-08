/*
small map initialization

Copyright 2012 by Rosscoe info at 4x4falcon dot com

Based on openstreetmap map.js
*/

var epsg4326 = new OpenLayers.Projection("EPSG:4326");
var map;
var markers;
var vectors;
var popup;


function init()
 {
  var options = {
                 projection: new OpenLayers.Projection("EPSG:900913"),
                 displayProjection: new OpenLayers.Projection("EPSG:4326"),
                 units: "m",
                 maxResolution: 156543.0339,
                 maxExtent: new OpenLayers.Bounds(-20037508.34, -20037508.34, 20037508.34, 20037508.34),
                 numZoomLevels: 19,

                 controls: [
                            new OpenLayers.Control.Navigation()
                           ]
                 };

  map = new OpenLayers.Map("small_map", options);
  var mapnik = new OpenLayers.Layer.OSM("fosm.org", "/default/${z}/${x}/${y}.png", {numZoomLevels: 19});
  map.addLayer(mapnik);
  map.setCenter(new OpenLayers.LonLat($lon,$lat) // Center of the map
     .transform(
       new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
       new OpenLayers.Projection("EPSG:900913") // to Spherical Mercator Projection
      ), $zoom // Zoom level
    );

  var $url = $api_url + $type + '/' + $id;

  if ($type != 'node')
   {
    $url += '/full';
   }


  if ($type != 'changeset')
   {
    addObjectToMap($url, true, function(extent) {
//            $("#loading").hide();
            $("#browse_map .geolink").show();

            if (extent) {
              extent.transform(map.getProjectionObject(), map.displayProjection);

              var centre = extent.getCenterLonLat();

//            $("#remote_area_edit").click(function (event) {
//              return remoteEditHandler(event, extent);
//            });


//            $("#remote_object_edit").click(function (event) {
//              return remoteEditHandler(event, extent, "node1299131858");
//            });


//              var $tmp = "View " + $type + " on larger map";
//              $("#object_larger_map").html("View node on larger map");
//              $tmp = "Edit " + type;
//              $("#object_edit").html("Edit node");

//            updatelinks(centre.lon, centre.lat, 16, null, extent.left, extent.bottom, extent.right, extent.top, $type, $id);
            } else {
            $("#small_map").hide();
            }
          });
//    createMenu("area_edit", "area_edit_menu", 1000, "right");
//    createMenu("object_edit", "object_edit_menu", 1000, "right");
   }
  else
   {
    var bbox = new OpenLayers.Bounds(minlon, minlat, maxlon, maxlat);
    var centre = bbox.getCenterLonLat();

    setMapExtent(bbox);
    addBoxToMap(bbox);
   }
 }

function addObjectToMap(url, zoom, callback)
 {
  OpenLayers.ProxyHost = "/cgi-bin/proxy.cgi?url=";

   var layer = new OpenLayers.Layer.GML("Objects", url, {
      format: OpenLayers.Format.OSM,
      style: {
          strokeColor: "blue",
          strokeWidth: 3,
          strokeOpacity: 0.5,
          fillOpacity: 0.2,
          fillColor: "lightblue",
          pointRadius: 6
      },
      projection: new OpenLayers.Projection("EPSG:4326"),
      displayInLayerSwitcher: false
   });

   layer.events.register("loadend", layer, function() {
      var extent;

      if (this.features.length) {
         extent = this.features[0].geometry.getBounds();

         for (var i = 1; i < this.features.length; i++) {
            extent.extend(this.features[i].geometry.getBounds());
         }

         if (zoom) {
            if (extent) {
               this.map.zoomToExtent(extent);
            } else {
               this.map.zoomToMaxExtent();
            }
         }
      }

      if (callback) {
         callback(extent);
      }
   });

   map.addLayer(layer);

   layer.loadGML();
}

function setMapExtent(extent)
 {
  map.zoomToExtent(extent.clone().transform(epsg4326, map.getProjectionObject()));
 }

function addBoxToMap(boxbounds) {
   if (!vectors) {
     // Be aware that IE requires Vector layers be initialised on page load, and not under deferred script conditions
     vectors = new OpenLayers.Layer.Vector("Boxes", {
        displayInLayerSwitcher: false
     });
     map.addLayer(vectors);
   }
   var geometry = boxbounds.toGeometry().transform(epsg4326, map.getProjectionObject());
   var box = new OpenLayers.Feature.Vector(geometry, {}, {
      strokeWidth: 2,
      strokeColor: '#ee9900',
      fillOpacity: 0
   });

   vectors.addFeatures(box);

   return box;
 }


function updatelinks(lon,lat,zoom,layers,minlon,minlat,maxlon,maxlat, obj_type, obj_id) {
  var decimals = Math.pow(10, Math.floor(zoom/3));
  var node;

  lat = Math.round(lat * decimals) / decimals;
  lon = Math.round(lon * decimals) / decimals;

  node = document.getElementById("permalinkanchor");
  if (node) {
    var args = getArgs(node.href);
    args["lat"] = lat;
    args["lon"] = lon;
    args["zoom"] = zoom;
    if (layers) {
      args["layers"] = layers;
    }
    if (obj_type && obj_id) {
      args[obj_type] = obj_id;
    }
    node.href = setArgs(node.href, args);
  }

  node = document.getElementById("viewanchor");
  if (node) {
    var args = getArgs(node.href);
    args["lat"] = lat;
    args["lon"] = lon;
    args["zoom"] = zoom;
    if (layers) {
      args["layers"] = layers;
    }
    node.href = setArgs(node.href, args);
  }

  node = document.getElementById("exportanchor");
  if (node) {
    var args = getArgs(node.href);
    args["lat"] = lat;
    args["lon"] = lon;
    args["zoom"] = zoom;
    if (layers) {
      args["layers"] = layers;
    }
    node.href = setArgs(node.href, args);
  }

  node = document.getElementById("editanchor");
  if (node) {
    if (zoom >= 13) {
      var args = new Object();
      args.lat = lat;
      args.lon = lon;
      args.zoom = zoom;
      node.href = setArgs("/edit", args);
      node.style.fontStyle = 'normal';
    } else {
      node.href = 'javascript:alert("zoom in to edit map");';
      node.style.fontStyle = 'italic';
    }
  }
  
  node = document.getElementById("historyanchor");
  if (node) {
    if (zoom >= 11) {
      var args = new Object();
      //set bbox param from 'extents' object
      if (typeof minlon == "number" &&
	  typeof minlat == "number" &&
	  typeof maxlon == "number" &&
	  typeof maxlat == "number") {
      
        minlon = Math.round(minlon * decimals) / decimals;
        minlat = Math.round(minlat * decimals) / decimals;
        maxlon = Math.round(maxlon * decimals) / decimals;
        maxlat = Math.round(maxlat * decimals) / decimals;
        args.bbox = minlon + "," + minlat + "," + maxlon + "," + maxlat;
      }
      
      node.href = setArgs("/history", args);
      node.style.fontStyle = 'normal';
    } else {
      node.href = 'javascript:alert("zoom in to see editing history");';
      node.style.fontStyle = 'italic';
    }
  }

  node = document.getElementById("shortlinkanchor");
  if (node) {
    var args = getArgs(node.href);
    var code = makeShortCode(lat, lon, zoom);
    var prefix = shortlinkPrefix();

    // little hack. may the gods of hardcoding please forgive me, or 
    // show me the Right way to do it.
    if (layers && (layers != "B000FTF")) {
      args["layers"] = layers;
      node.href = setArgs(prefix + "/go/" + code, args);
    } else {
      node.href = prefix + "/go/" + code;
    }
  }
}


function remoteEditHandler(event, bbox, select)
 {
  var left = bbox.left - 0.0001;
  var top = bbox.top + 0.0001;
  var right = bbox.right + 0.0001;
  var bottom = bbox.bottom - 0.0001;
  var loaded = false;

  $("#linkloader").load(function () { loaded = true; });

  if (select)
   {
    $("#linkloader").attr("src", "http://127.0.0.1:8111/load_and_zoom?left=" + left + "&top=" + top + "&right=" + right + "&bottom=" + bottom + "&select=" + select);
   }
  else
   {
    $("#linkloader").attr("src", "http://127.0.0.1:8111/load_and_zoom?left=" + left + "&top=" + top + "&right=" + right + "&bottom=" + bottom);
   }

  setTimeout(function ()
   {
    if (!loaded) alert("Editing failed - make sure JOSM or Merkaartor is loaded and the remote control option is enabled");
   }, 1000);

  return false;
 }
