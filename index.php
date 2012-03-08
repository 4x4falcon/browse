<?php
include_once ("../php/config.inc.php");
include_once ("functions.php");

//print "<p>";
//print_r ($_REQUEST);
//print "</p>";

if (isset($_REQUEST['object']))
 {
  $object_type = $_REQUEST['object'];
 }
else
 {
  exit();
 }
if (isset($_REQUEST['fosm_id']))
 {
  $fosm_id = $_REQUEST['fosm_id'];
 }
else
 {
  exit();
 }

//print "object = $object<br/>id = $fosm_id<br />";

if (($object_type != '') && ($fosm_id != ''))
 {
  $object_url = $api_url . $object_type . "/" . $fosm_id;

  ini_set("user_agent","Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
  ini_set("max_execution_time", 0);
  ini_set("memory_limit", "10000M");
  $object = simplexml_load_file($object_url);

  if ($object === false)
   {
    print '
 <html>
  <head>
   <title>
    fosm.org - object does not exist
   </title>
  </head>
  <body>
   <h1>No ';
  print $object_type;
  print ' with id of ';
  print $fosm_id;
  print ' found at fosm.org</h1>
  Maybe it can be found ';

  print '<a href="http://www.openstreetmap.com/browse/' . $object_type . '/' . $fosm_id . '" target="_blank">here</a>.';

  print '
  </body>
</html>';

   exit(0);
  }
 }
else
 {
  exit(0);
 }

$points = get_lat_lon_zoom($object, $object_type);
?>

<html>
  <head>

<?php
echo '<title>fosm.org - ';
echo $object_type . ' - ';
echo get_title($object, $object_type, $fosm_id);
echo '</title>';
?>

    <link href="/browse/style.css" type="text/css" rel="stylesheet" />
    <script src="http://www.openlayers.org/api/OpenLayers.js"></script>
<!--
    <script src="/openlayers/OpenLayers.js"></script>
-->
    <script src="/jsf/menu.js"></script>
    <script src="/browse/jsf/fosm_org.js"></script>

<!-- This is where all variables are passed from php to javascript -->

<script language="javascript" type="text/javascript">

var $lat =  <?php echo $points['lat']?>;
var $lon =  <?php echo $points['lon']?>;
var $zoom = <?php echo $points['zoom']?>;
var $type = "<?php echo $object_type?>";
var $id = "<?php echo $fosm_id?>";
var $api_url = "<?php echo $api_url?>";

var minlon = <?php echo $points['min_lon']?>;
var minlat = <?php echo $points['min_lat']?>;
var maxlon = <?php echo $points['max_lon']?>;
var maxlat = <?php echo $points['max_lat']?>;


</script>

  </head>

  <body onload="init();">

   <div id="main">
   <div id="top">
   </div>

   <div id="left_side">
   </div>

   <div id="data">

   <table id="data_table">
   <tr valign="top">

   <td id="heading" colspan="2">

<?php
echo ucfirst($object_type) . " : ";
echo get_title($object, $object_type, $fosm_id);
?>

   </td>
   </tr>

   <tr valign="top">
   <td id="left">

<?php
if ($object_type != 'changeset')
 {
  echo "Edited at:";
 }
else
 {
  echo "Created at:";
 }
?>

   </td>
   <td id="right">

<?php
if ($object_type != 'changeset')
 {
  echo $object->{$object_type}['timestamp'];
 }
else
 {
  echo $object->changeset['created_at'];
 }
?>

   </td>
   </tr>

<?php
if ($object_type == 'changeset')
 {
  echo '<tr valign="top">
         <td id="left">';
  echo "Closed at:";
  echo '</td>
         <td id="right">';
  echo $object->{$object_type}['closed_at'];
  echo '</td>
        </tr>';
 }
?>

   <tr valign="top">
   <td id="left">
   Edited by:
   </td>
   <td id="right">

<?php
echo $object->{$object_type}['user'];
?>

   </td>
   </tr>

<?php
if ($object_type != 'changeset')
 {
  print '
   <tr valign="top">
   <td id="left">
   Version:
   </td>
   <td id="right">';

   echo $object->{$object_type}['version'];

  print '
   </td>
   </tr>';

  print '
   <tr valign="top">
   <td id="left">
   In Changeset:
   </td>
   <td id="right">';
  print '<a href="/browse/changeset/';

  echo $object->{$object_type}['changeset'];

  print '" target="_blank">';

  echo $object->{$object_type}['changeset'];

  print '</a>';

  print '
   </td>
   </tr>';
 }
?>

<?php
if ($object_type == 'node')
 {
  print '
   <tr valign="top">
   <td id="left">
   Coordinates:
   </td>
   <td id="right">';

  echo $points['lat'];
  echo ", ";
  echo $points['lon'];

  echo '
   </td>
   </tr>';
 }

if ($object_type == 'changeset')
 {
  print '
   <tr valign="top">
   <td id="left">
   Bounding Box:
   </td>
   <td id="right_center">';

  echo $points['max_lat'] . "<br />";

  echo $points['min_lon'] . " " . $points['max_lon'] . "<br />";

  echo $points['min_lat'];

  echo '
   </td>
   </tr>';
 }
?>


<?php
$tmp = get_tags($object->{$object_type});

if ($tmp != '')
 {
  echo'
   <tr valign="top">
   <td id="left">
   Tags:
   </td>
   <td id="right">';
  echo $tmp;
  echo'
   </td>
   </tr>';
 }
?>


<?php
if ($object_type == 'way')
 {
  echo '
   <tr valign="top">
   <td id="left">
   Nodes:
   </td>
   <td id="right">';
  echo get_nodes($object->{$object_type});
  echo '
   </td>
   </tr>';
 }
?>

<?php
if ($object_type == 'relation')
 {
  $tmp = get_members($object->{$object_type});
  if ($tmp != '')
   {
    echo'
   <tr valign="top">
   <td id="left">
   Ways:
   </td>
   <td id="right">';

   echo $tmp;

   echo'
   </td>
   </tr>';
   }
 }
?>

<?php
if ($object_type == 'changeset')
 {
  $changeset_url = $object_url . '/download';

  $changeset = simplexml_load_file($changeset_url);

  $tmp = get_changeset_nodes($changeset);

  if ($tmp != '')
   {
    echo'
   <tr valign="top">
   <td id="left">
   Nodes:
   </td>
   <td id="right">';

   echo $tmp;

   echo'
   </td>
   </tr>';
   }

  $tmp = get_changeset_ways($changeset);

  if ($tmp != '')
   {
    echo'
   <tr valign="top">
   <td id="left">
   Ways:
   </td>
   <td id="right">';

   echo $tmp;

   echo'
   </td>
   </tr>';
   }

  $tmp = get_changeset_relations($changeset);

  if ($tmp != '')
   {
    echo'
   <tr valign="top">
   <td id="left">
   Relations:
   </td>
   <td id="right">';

   echo $tmp;

   echo'
   </td>
   </tr>';
   }
 }
?>

    <tr>
     <td colspan="2">

<?php
     if ($object_type != 'changeset')
      {
       echo '<a href="' . $history_link . $object_type . '/' . $fosm_id . '/history" target="_blank">View history</a>';
      }
?>
     </td>
    </tr>
    </table>

   <p></p>

    </div>

<!-- this is the map -->
    <div id="browse_map">
      <div id="small_map">
      </div>

<!--
      <span class="loading" id="loading">Loading...</span>

      <a href="/?box=yes" class="geolink bbox" id="area_larger_map">View area on larger map</a>
      <br />
      <a href="/edit" class="geolink bbox" id="area_edit">Edit area</a>

      <br />
      <a href="/" class="geolink object" id="object_larger_map"></a>
      <br />
      <a href="/edit" class="geolink object" id="object_edit"></a>

     <div id="area_edit_menu" class="menu">
      <ul>
        <li><a href="/edit?editor=potlatch" class="geolink bbox" id="potlatch_area_edit">Edit with Potlatch 1 (in-browser editor)</a></li>
        <li><a href="/edit?editor=potlatch2" class="geolink bbox" id="potlatch2_area_edit">Edit with Potlatch 2 (in-browser editor)</a></li>
        <li><a href="/edit?editor=remote" class="geolink bbox" id="remote_area_edit">Edit with Remote Control (JOSM or Merkaartor)</a></li>
      </ul>
     </div>

     <div id="object_edit_menu" class="menu">
      <ul>
        <li><a href="/edit?editor=potlatch" class="geolink object" id="potlatch_object_edit">Edit with Potlatch 1 (in-browser editor)</a></li>
        <li><a href="/edit?editor=potlatch2" class="geolink object" id="potlatch2_object_edit">Edit with Potlatch 2 (in-browser editor)</a></li>
        <li><a href="/edit?editor=remote" class="geolink object" id="remote_object_edit">Edit with Remote Control (JOSM or Merkaartor)</a></li>
      </ul>
     </div>
-->

    </div>

   </div>
  </body>
</html>
