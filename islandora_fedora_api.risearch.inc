<?php
/**
 * @file
 *   A group of functions meant to make interacting with the resource index easier
 * @author
 *   William Panting
 * This is deffinatly exparimental quality right now.
 */


/**
 * This function is
 *
 * @todo error handling
 * @todo test if all frogs are green
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