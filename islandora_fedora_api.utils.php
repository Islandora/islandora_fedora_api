<?php
/**
 * @file
 *   This file is meant to provide usefull functions for dealing with fedora
 *   that are not done on individual objects or datastreams
 *   THIS IS PLACEHOLDER DON'T USE IT,
 *   these are functions we want to re-implement in the new api
 * @todo
 *   remove all calls on islandora core
 * @todo
 *   test
 *
 * some of these should appear in different files:
 * perhaps we should sublcass object with a collection,
 * and some query things in the repository class... or an ri search funciton library
 * I would like to discuss this in a committers meeting
 */



/*
* Operations that affect a Fedora repository at a collection level.
*/

module_load_include('inc', 'fedora_repository', 'CollectionClass');
module_load_include('inc', 'fedora_repository', 'api/fedora_item');
module_load_include('inc', 'fedora_repository', 'api/fedora_utils');
module_load_include('module', 'fedora_repository');

/**
* Exports a fedora collection object and all of its children in a format
* that will let you import them into another repository.
* @param <type> $format
*/
function export_collection($collection_pid, $relationship = 'isMemberOfCollection', $format = 'info:fedora/fedora-system:FOXML-1.1' ) {
  $collection_item = new Fedora_Item($collection_pid);
$foxml = $collection_item->export_as_foxml();

$file_dir = file_directory_path();

// Create a temporary directory to contain the exported FOXML files.
$container = tempnam($file_dir, 'export_');
file_delete($container);
print $container;
if (mkdir($container) && mkdir($container . '/'. $collection_pid)) {
$foxml_dir = $container . '/'. $collection_pid;
$file = fopen($foxml_dir . '/'. $collection_pid . '.xml', 'w');
fwrite($file, $foxml);
    fclose($file);

$member_pids = get_related_items_as_array($collection_pid, $relationship);
foreach ($member_pids as $member) {
$file = fopen($foxml_dir . '/'. $member . '.xml', 'w');
$item = new Fedora_Item($member);
      $item_foxml = $item->export_as_foxml();
fwrite($file, $item_foxml);
fclose($file);
}
if (system("cd $container;zip -r $collection_pid.zip $collection_pid/* >/dev/NULL") == 0) {
header("Content-type: application/zip");
header('Content-Disposition: attachment; filename="' . $collection_pid . '.zip'. '"');
$fh = fopen($container . '/'. $collection_pid . '.zip', 'r');
$the_data = fread($fh, filesize($container . '/'. $collection_pid . '.zip'));
      fclose($fh);
echo $the_data;
}
if (file_exists($container . '/'. $collection_pid)) {
      system("rm -rf $container"); // I'm sorry.
}
}
else {
drupal_set_message("Error creating temp directory for batch export.", 'error');
    return FALSE;
}
return TRUE;
}

/**

@TODO:
This is from islandora 6.x core, it needs to be rewritten to use this api
There is also a problem in using DC fields in querries this needs to be refactored to use things
will not return multiple tupples when the person using the api expects one, or we can just remove all
duplicates before returning the array of results in get_array...


* Returns an array of pids that match the query contained in teh collection
 * object's QUERY datastream or in the suppled $query parameter.
* @param <type> $collection_pid
* @param <type> $query
* @param <type> $query_format R

function get_related_items_as_xml($collection_pid, $relationship = array('isMemberOfCollection'), $limit = 10000, $offset = 0, $active_objects_only = TRUE, $cmodel = NULL, $orderby = '$title') {
module_load_include('inc', 'fedora_repository', 'ObjectHelper');
$collection_item = new Fedora_Item($collection_pid);

global $user;
if (!fedora_repository_access(OBJECTHELPER :: $OBJECT_HELPER_VIEW_FEDORA, $pid, $user)) {
drupal_set_message(t("You do not have access to Fedora objects within the attempted namespace or access to Fedora denied."), 'error');
return array();
}

$query_string = 'select $object $title $content from <#ri>
                             where ($object <dc:title> $title
and $object <fedora-model:hasModel> $content
                             and (';

  if (is_array($relationship)) {
    foreach ($relationship as $rel) {
      $query_string .= '$object <fedora-rels-ext:'. $rel . '> <info:fedora/'. $collection_pid . '>';
      if (next($relationship)) {
$query_string .= ' OR ';
}
}
}
elseif (is_string($relationship)) {
$query_string .= '$object <fedora-rels-ext:'. $relationship . '> <info:fedora/'. $collection_pid . '>';
}
else {
return '';
}

  $query_string .= ') ';
  $query_string .=  $active_objects_only ? 'and $object <fedora-model:state> <info:fedora/fedora-system:def/model#Active>' : '';

  if ($cmodel) {
  $query_string .= ' and $content <mulgara:is> <info:fedora/' . $cmodel . '>';
}

  $query_string .= ')
                    minus $content <mulgara:is> <info:fedora/fedora-system:FedoraObject-3.0>
  order by '.$orderby;

  $query_string = htmlentities(urlencode($query_string));


  $content = '';
  $url = variable_get('fedora_repository_url', 'http://localhost:8080/fedora/risearch');
  $url .= "?type=tuples&flush=TRUE&format=Sparql&limit=$limit&offset=$offset&lang=itql&stream=on&query=". $query_string;
  $content .= do_curl($url);

  return $content;
}

  function get_related_items_as_array($collection_pid, $relationship = 'isMemberOfCollection', $limit = 10000, $offset = 0, $active_objects_only = TRUE, $cmodel = NULL, $orderby = '$title') {
  $content = get_related_items_as_xml($collection_pid, $relationship, $limit, $offset, $active_objects_only, $cmodel, $orderby);
  if (empty($content)) {
  return array();
  }

  $content = new SimpleXMLElement($content);

  $resultsarray = array();
  foreach ($content->results->result as $result) {
  $resultsarray[] = substr($result->object->attributes()->uri, 12); // Remove 'info:fedora/'.
  }
  return $resultsarray;
  }*/

/**
 * This function will get the collection that the indicated object is a member of
 * @param string $object_id
 *   The id of the object to get the parent of
 * @return mixed $parent
 *   The id of the collection object that contains the $object_id object or FALSE if no parent found
 * @todo:
 *   should return all parents... make it return an array
 *   put in the object class
 */
function get_object_parent($object_id) {
  //init
  $parent_relationship='isMemberOf';
  $parent_relationship_namespace='info:fedora/fedora-system:def/relations-external#';
  module_load_include('raw.inc', 'islandora_fedora_api'); //for getting an object
  $apim_object= new FedoraAPIM();
  $relationships_parser = new DOMDocument();
  $parent=FALSE;

  //get relation ship data
  try {
    $relationships=$apim_object->getRelationships($object_id, $parent_relationship_namespace . $parent_relationship);
    //grab realtionship
    $relationships_parser->loadXML($relationships->data);
    $relationship_elements=$relationships_parser->getElementsByTagNameNS($parent_relationship_namespace, $parent_relationship);
    $relationship=$relationship_elements->item(0);
  }
  catch (FedoraAPIRestException $e) {
    return FALSE;
  }

  //handle second collecion memberships string if the first wasn't found
  if (empty($relationship)) {
    $parent_relationship='isMemberOfCollection';
    try {
      $relationships=$apim_object->getRelationships($object_id, $parent_relationship_namespace . $parent_relationship);
      //grab realtionship
      $relationships_parser->loadXML($relationships->data);
      $relationship_elements=$relationships_parser->getElementsByTagNameNS($parent_relationship_namespace, $parent_relationship);
      $relationship=$relationship_elements->item(0);
    }
    catch (FedoraAPIRestException $e) {
      return FALSE;
    }
  }

  //handle relationship data
  if (!empty($relationship)) {
    $parent=$relationship->getAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'resource');
    //cut out 'info:fedora/'
    if (substr($parent, 0, 12)=='info:fedora/') {
      $parent=substr($parent, 12, strlen($parent));
    }
  }
  return $parent;
}


/**
 * WE MAY WANT TO USE THIS AS A TEMPLATE FOR A SIMILAR FUNCTION
* This function will set the indicated relationship on the indicated object.
* It will create or replace the relationship  as apropriate.
* The
* @param $object_id
*   the fedora pid of the object whos rels-ext will be modified
* @param string $relationship
*   the relationship to set
* @param string $target
*   a litteral or fedora pid string (object of the relationship)
* @param string $subject
*   This defaults to the object's pid in the REST interface, only specify if a datastream
* @return
*   the response from fedora for adding/modifying the relationship
* @todo
*   Update to use subject and object so it will work with rels-int
*
function islandora_workflow_set_object_relationship($object_id, $relationship_in, $target, $subject = NULL) {
  //init
  $islandora_workflow_namespace='info:islandora/islandora-system:def/islandora_workflow#';
  module_load_include('raw.inc', 'islandora_fedora_api'); //for getting an object
  $apim_object= new FedoraAPIM();

  //get existing relationshp
  $relationships = $apim_object->getRelationships($object_id, $islandora_workflow_namespace . $relationship_in, $subject);


  $relationships_parser = new DOMDocument();
  $relationships_parser->loadXML($relationships->data);
  $relationship_elements = $relationships_parser->getElementsByTagNameNS($islandora_workflow_namespace, $relationship_in);
  $current_relationship = NULL;
  $relationship = $relationship_elements->item(0);

  if (!empty($relationship)) {
    foreach ($relationship->childNodes as $relationship_text_node) {
      $current_relationship = $relationship_text_node->nodeValue;
      //clear current relationship
      $purge_response = $apim_object->purgeRelationship($object_id, $islandora_workflow_namespace . $relationship_in, $current_relationship, array('isLiteral' => 'true', 'subject' => $subject));
    }
  }
  //set new relationship
  $apim_object->addRelationship($object_id, $islandora_workflow_namespace . $relationship_in, $target, array('isLiteral' => 'true', 'subject' => $subject));
}
*/