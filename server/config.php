<?php

$conf=new stdClass();


$conf->DB=new stdClass();
$conf->s3=new stdClass();

//tigris
$conf->DB->host="localhost";
$conf->DB->DBName="applesee_digclass2";
$conf->DB->userName="applesee_digcla2";
$conf->DB->pass="ST7xe@7k]o@U";
$conf->s3->bucket="null";
/**/

$conf->DB->logError="log/sqlError.log";
$conf->dynamicFilePath="dynamic/";
$conf->firebase=new stdClass();
$conf->firebase->serverKey='AAAAz3ligfs:APA91bGFBy2qPRUTqtykf3CRkqOuqSm--LIzf-uBWS2j8aqqJMFr1XhwOMEi1fwRUIMU3VBr5Pxgq_dirqBVcWzTu4x3McFNQf2vKwdJaMm2DbxqXIA6dQib6L9V0wsDdE_1WKwRNncE';
$conf->adminPass="123456";

