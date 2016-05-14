<?php

/**
*   Utility functions used by many other classes and functions.
*
*   @author Albert Nel
*/

/**
*   Parse the $_SERVER['QUERY_STRING'] variable.
*
*   @param string $query_string The query string
*   @return array The query string converted to a key/value array
*/
function parse_query_string($query_string)
{
    parse_str($query_string, $query_array);
    return $query_array;
}

/**
*   Catches SQL errors from DB.
*
*   @param array $error Array containing error type, code and message.
*   @throws MeekroDBException $e
*/
function error_handler($error)
{
    $e = new MeekroDBException($error['error'], $error['query'], $error['type']);
    throw $e;
}

?>
