<?

$CONF = array();

$CONF['db_host'] = 'localhost';
$CONF['db_user'] = 'example';
$CONF['db_passwd'] = 'passwordhere';
$CONF['db_name'] = 'database';

$CONF['db_prefix'] = 'geoportal_'; //used as a prefix on all tables - CAN be empty if you wish. 


//CREATE DATABASE  `geoportal` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
//GRANT ALL PRIVILEGES ON  `geoportal` . * TO  'geoportal'@'localhost' WITH GRANT OPTION ;


//this should be a unique string, 30-40 chars, that only you know. Leave the default of hashing password, if unsure what to use
$CONF['token_secret'] = md5($CONF['db_passwd']);


//this is the domain the portal is linked to 
$CONF['geograph_domain'] = 'www.geograph.org.uk';

