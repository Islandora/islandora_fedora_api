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
 * This function is
 *
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

/*
 //format query results for use (xml->array)
  $result_nodes = $query_return_dom->getElementsByTagName('result');
  $result_nodes_length = $result_nodes->length;
  if ($result_nodes_length > 0) {
    for ($i=0; $i < $result_nodes_length; $i++) {
      $result_node = $result_nodes->item($i);
      $result_values = $result_node->childNodes;

      $label_element = $result_node->getElementsByTagName('label')->item(0);

      $member_object = $member_object_element->getAttribute('uri');
      //info:fedora/ is 12 chars
      $member_object = substr($member_object, 12, strlen($member_object));//need to extract the pid

      $member_data[$member_object] = array();

      if ($label_element) {
        $label = $label_element->nodeValue;
        if ($label) {
          //the label may not be pupulated
          $member_data[$member_object]['label'] = $label;
        }
      }
    }
 */