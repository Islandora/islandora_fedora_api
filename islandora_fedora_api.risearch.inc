<?php
/**
 * @file
 *   A group of functions meant to make interacting with the resource index easier
 * @author
 *   William Panting
 * This is deffinatly exparimental quality right now.
 * @todo support export as object
 * @todo support export as json serialized
 * @todo support export as array (paul did this for itql see .utils.php)
 */


/**
 * This function will run any ri query.
 * @param string $query
 * @param array $arguments
 * @return
 * @todo error handling
 * @todo test if all frogs are green
 * @todo refactor this to use an array input rather than this long arg list
 */
function query_resource_index($query, $limit = -1, $offset = 0, $format='Sparql', $lang='sparql', $type='tuples', $flush='TRUE') {

  module_load_include('connection.inc', 'islandora_fedora_api');
  $fedora_connection = new FedoraConnection();

  $query_url = $fedora_connection->requestURL();

  //run query
  $query_url .= "/risearch?type=$type&flush=$flush" . (($limit > 0)?("&limit=$limit"):("")) . "&format=$format&offset=$offset&lang=$lang&query=" . urlencode($query);
  module_load_include('inc', 'fedora_repository', 'api/fedora_utils');
  dsm($query_url);

  $query_return_object = drupal_http_request($query_url);

  return $query_return_object->data;
}


/**
 * This function is used to get a list containing all of the islandora collections in a Fedora repo
 * @author
 *   William Panting
 * @return $collection_list
 *   an associated array of collection pids and names
 * @todo make respect namespace restrictions
 */
/*
function get_all_collections() {

  $query_file_name = drupal_get_path('module', 'islandora_workflow') . '/collection_query.sparql';
  $query_file_handle = fopen($query_file_name, "r");
  $query_string = fread($query_file_handle, filesize($query_file_name));
  fclose($query_file_handle);

  $query_return_string = query_resource_index($query_string);

  if ($query_return_string) {
    $query_return_dom = DOMDocument::loadXML($query_return_string);
  }

  //format query results for use (xml->array)
  $collection_data = array();
  $result_nodes = $query_return_dom->getElementsByTagName('result');
  $result_nodes_length = $result_nodes->length;
  if ($result_nodes_length > 0) {
    for ($i=0; $i < $result_nodes_length; $i++) {
      $result_node = $result_nodes->item($i);

      $label_element = $result_node->getElementsByTagName('label')->item(0);
      $collection_pid_element = $result_node->getElementsByTagName('collection_object')->item(0);
      $workflow_tracking_element = $result_node->getElementsByTagName('workflow_tracking')->item(0);

      $collection_object_uri = $collection_pid_element->getAttribute('uri');
      //info:fedora/ is 12 chars
      $collection_object_pid = substr($collection_object_uri, 12, strlen($collection_object_uri));//need to extract the pid

      $collection_data[$collection_object_pid] = array();

      if ($label_element) {
        $label = $label_element->nodeValue;
        if ($label) {
          //the label may not be pupulated
          $collection_data[$collection_object_pid]['label'] = $label;
        }
      }
      if ($workflow_tracking_element) {
        $workflow_tracking = $workflow_tracking_element->nodeValue;
        if ($workflow_tracking && $workflow_tracking == 'TRUE') {
          $collection_data[$collection_object_pid]['workflow_tracking'] = TRUE;
        }
        else {
          $collection_data[$collection_object_pid]['workflow_tracking'] = FALSE;
        }
      }
    }
  }
}*/