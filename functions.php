<?php

function get_lat_lon_zoom($xml, $type)
 {
  global $api_url;

  $r = array('lat' => 0.0,
             'lon' => 0.0,
             'min_lat' => 0.0,
             'max_lat' => 0.0,
             'min_lon' => 0.0,
             'max_lon' => 0.0,
             'zoom' => 16.0,
             'valid' => false);

  if ($type == 'node')
   {
    $r['lat'] = $xml->{$type}['lat'];
    $r['lon'] = $xml->{$type}['lon'];
    $r['valid'] = true;
   }
  else if ($type == 'way')
   {
    $url = $api_url . 'node/' . $xml->{$type}->nd['ref'];
    $node = simplexml_load_file($url);
    $r['lat'] = $node->node['lat'];
    $r['lon'] = $node->node['lon'];
    $r['valid'] = true;
   }
  else if ($type == 'relation')
   {
    if (isset($xml->{$type}->node))
     {
      $url = $api_url . 'node/' . $xml->{$type}->node['id'];
      $node = simplexml_load_file($url);
      $r['lat'] = $node->node['lat'];
      $r['lon'] = $node->node['lon'];
      $r['valid'] = true;
     }
    else if (isset($xml->{$type}->way))
     {
     }
   }
  else if ($type == 'changeset')
   {
    $r['min_lat'] = floatval($xml->{$type}['min_lat']);
    $r['min_lon'] = floatval($xml->{$type}['min_lon']);
    $r['max_lat'] = floatval($xml->{$type}['max_lat']);
    $r['max_lon'] = floatval($xml->{$type}['max_lon']);
    $r['lat'] = $r['max_lat'] + (($r['max_lat'] - $r['min_lat'])/2.0);
    $r['lon'] = $r['min_lon'] + (($r['max_lon'] - $r['min_lon'])/2.0);
    $r['zoom'] = 12;
    $r['valid'] = true;
   }
  else
   {
    $r['valid'] = false;
   }
  return $r;
 }


function get_name($xml)
 {
  foreach ($xml->tag as $tag)
   {
    if ($tag['k'] == 'name')
     {
      return $tag['v'];
     }
   }
  return '';
 }

function get_title($xml, $type, $id)
 {
  $tmp = '';
  if ($type != 'changset')
   {
    $tmp = get_name($xml->{$type});
   }
  if ($tmp == '')
   {
    return $id;
   }
  else
   {
    return $tmp . "(" . $id . ")";
   }
 }

function get_tags($xml)
 {
  $output = '';
  foreach ($xml->tag as $tag)
   {
    $output .= $tag['k'] . " = " . $tag['v'] . "<br />";
   }
  return $output;
 }

function get_nodes($xml)
 {
  $output = '';
  foreach ($xml->nd as $nd)
   {
    $output .= '<a href="/browse/node/' . $nd['ref'] . '">' . $nd['ref'] . "</a><br />";
   }
  return $output;
 }

function get_members($xml)
 {
  $output = '';
  foreach ($xml->member as $member)
   {
    $output .= ucfirst($member['type']) . ' <a href="/browse/' . $member['type']. '/' . $member['ref'] . '">' . $member['ref'] . '</a>' .  " as " . $member['role'] . "<br />";
   }
  return $output;
 }


function get_changeset_nodes($xml)
 {
  $output = '';

  if (isset($xml->modify->node))
   {
    foreach ($xml->modify as $m)
     {
      foreach ($m->node as $nd)
       {
        $output .= '<a href="/browse/node/' . $nd['id'] . '">' . $nd['id'] . "</a><br />";
       }
     }
   }
  if (isset($xml->create->node))
   {
    foreach ($xml->create as $m)
     {
      foreach ($m->node as $nd)
       {
        $output .= '<a href="/browse/node/' . $nd['id'] . '">' . $nd['id'] . "</a><br />";
       }
     }
   }
  if (isset($xml->delete->node))
   {
    foreach ($xml->delete as $m)
     {
      foreach ($m->node as $nd)
       {
        $output .= '<a class="deleted" href="/browse/node/' . $nd['id'] . '">' . $nd['id'] . "</a><br />";
       }
     }
   }

  return $output;
 }

function get_changeset_ways($xml)
 {
  $output = '';
  if (isset($xml->modify))
   {
    foreach ($xml->modify as $m)
     {
      if (isset($m->way))
       {
        foreach ($m->way as $nd)
         {
          $output .= '<a href="/browse/way/' . $nd['id'] . '">' . $nd['id'] . "</a><br />";
         }
       }
     }
   }
  if (isset($xml->create))
   {
    foreach ($xml->create as $m)
     {
      if (isset($m->way))
       {
        foreach ($m->way as $nd)
         {
          $output .= '<a href="/browse/way/' . $nd['id'] . '">' . $nd['id'] . "</a><br />";
         }
       }
     }
   }
  if (isset($xml->delete))
   {
    foreach ($xml->delete as $m)
     {
      if (isset($m->way))
       {
        foreach ($m->way as $nd)
         {
          $output .= '<a class="deleted" href="/browse/way/' . $nd['id'] . '">' . $nd['id'] . "</a><br />";
         }
       }
     }
   }
  return $output;
 }

function get_changeset_relations($xml)
 {
  $output = '';
  if (isset($xml->modify))
   {
    foreach ($xml->modify as $m)
     {
      if (isset($m->relation))
       {
        foreach ($m->relation as $nd)
         {
          $output .= '<a href="/browse/relation/' . $nd['id'] . '">' . $nd['id'] . "</a><br />";
         }
       }
     }
   }
  if (isset($xml->create))
   {
    foreach ($xml->create as $m)
     {
      if (isset($m->relation))
       {
        foreach ($m->relation as $nd)
         {
          $output .= '<a href="/browse/relation/' . $nd['id'] . '">' . $nd['id'] . "</a><br />";
         }
       }
     }
   }
  if (isset($xml->delete))
   {
    foreach ($xml->delete as $m)
     {
      if (isset($m->relation))
       {
        foreach ($m->relation as $nd)
         {
          $output .= '<a class="deleted" href="/browse/relation/' . $nd['id'] . '">' . $nd['id'] . "</a><br />";
         }
       }
     }
   }
  return $output;
 }


?>
