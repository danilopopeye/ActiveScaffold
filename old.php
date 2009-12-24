#!/usr/bin/php
<?php

define('BASEPATH','/home/danilo/code/scruwp/system');
$path = array();

$path['application']    = BASEPATH . DIRECTORY_SEPARATOR . "application";
$path['config']         = $path['application'] . DIRECTORY_SEPARATOR . 'config';

include( $path['config'] . DIRECTORY_SEPARATOR . 'database.php' );

$db = $db[ $active_group ];

$conn = @mysql_connect( $db['hostname'], $db['username'], $db['password'] );

if( !$conn ){
    die( 'Unable to connect to the database' );
}

if( !@mysql_select_db( $db['database'], $conn ) ){
    die( "Unable to select database: ".$this->database );
}

var_dump( $argv );

$validActions = array('M','C','V');

$action = '';
?>

