<?php
//Change this to TRUE
$config['check_cors'] = TRUE;

//No change here
$config['allowed_cors_headers'] = [
  'Origin',
  'X-Requested-With',
  'Content-Type',
  'Accept',
  'Access-Control-Request-Method'
];

//No change here
$config['allowed_cors_methods'] = [
  'GET',
  'POST',
  'OPTIONS',
  'PUT',
  'PATCH',
  'DELETE'
];

//Set to TRUE to enable Cross-Origin Resource Sharing (CORS) from any source domain
$config['allow_any_cors_domain'] = TRUE;


//Used if $config['check_cors'] is set to TRUE and $config['allow_any_cors_domain'] is set to FALSE. 
//Set all the allowable domains within the array
//e.g. $config['allowed_origins'] =['http://www.example.com','https://spa.example.com']

$config['allowed_cors_origins'] = [];
