<?php
/**
 * This is deffinatly exparimental quality right now.
 * @do_curl is old api and needs to be nixed
 */

/**
 *
 * Enter description here ...
 * @param unknown_type $query
 * @param unknown_type $count
 * @return unknown
 */
function query_by_SPARQL($query, $count=FALSE) {

  //get info for query
  $limit = -1;
  $offset = 0 ;
  $lang = 'sparql';
  $format = (($count)?("count"):("sparql"));
  $query_url = variable_get('fedora_repository_url', 'http://localhost:8080/fedora/risearch');

  //run query
  $query_url .= "?type=tuples&flush=TRUE" . (($limit > 0)?("&limit=$limit"):("")) . "&format=$format&offset=$offset&lang=$lang&query=" . urlencode($query);
  module_load_include('inc', 'fedora_repository', 'api/fedora_utils');
  drupal_set_message($query_url);

  $query_return_string = do_curl($query_url);

  return $query_return_string;
}

/**
 *
 * @author
 *   Adam
 * @param unknown_type $query
 * @param unknown_type $limit
 * @param unknown_type $offset
 * @return Ambigous <DOMDocument, mixed>
 */
function query_by_ITQL($query, $limit = -1, $offset = 0) {
  $queryUrl = variable_get('fedora_repository_url', 'http://localhost:8080/fedora/risearch');
  $queryUrl .= "?type=tuples&flush=TRUE&format=Sparql" . (($limit > 0)?("&limit=$limit"):("")) . "&offset=$offset&lang=itql&stream=on&query=" . urlencode($query);
  $doc = DOMDocument::loadXML(do_curl($queryUrl));
  return ((!$doc)?(new DOMDocument()):($doc));
}

/**
 *
 * Enter description here ...
 */
function query_resource_index() {
  return;
}