<?php
use Workerman\Worker;

$worker  =  new Worker ('0.0.0.0', 2206); 
$worker -> onWorkerStart  =  function () 
{ 
    publish($event_name, $event_data);
};

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}