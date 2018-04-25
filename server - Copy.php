<?php
use Workerman\Worker;
use Workerman\WebServer;
use Workerman\Autoloader;
use PHPSocketIO\SocketIO;

// composer autoload
require_once __DIR__ . '/vendor/autoload.php';

$io = new SocketIO(2020);
$names=[];$ids=[]; $id; //declaring the arrays to store names and socket id's and anvariable id.
$io->on('connection', function($socket){
    $socket->addedUser = false;
    // when the client emits 'new message', this listens and executes
    $socket->on('message', function ($message,$indx,$user)use($socket){
     global $names, $ids,$id;
       $id=array_search($indx, $ids);
        var_dump($id);
        $name=$names[$id];
        var_dump($name);
        $socket->broadcast->to($indx)->emit('message',$user, $message,$name);

    });

    // when the client emits 'new_client', this listens and executes
    $socket->on('new_client', function ($username,$cb) use($socket){
        global $names, $ids,$id,$io ;
        if(in_array($username,$names)){
          $cb(false);
        }else{
        $cb(true);
        // we store the username in the socket session for this client
        $socket->username = $username;
        // add the client's username to the global list
        array_push($names,$username);
         array_push($ids,$socket->id);
         var_dump($username.'  is online');
      $io->sockets->emit('client',$names,$ids);
           var_dump($names);
           var_dump($ids);
        $socket->addedUser = true;
       /* $socket->emit('login', array(
            'numUsers' => $numUsers
        ));*/
        }
    });

    // when the client emits 'typing', we broadcast it to others
    $socket->on('typing', function () use($socket) {
        $socket->broadcast->emit('typing', array(
            'username' => $socket->username
        ));
    });

    // when the client emits 'stop typing', we broadcast it to others
    $socket->on('stop typing', function () use($socket) {
        $socket->broadcast->emit('stop typing', array(
            'username' => $socket->username
        ));
    });

    // when the user disconnects.. perform this
    $socket->on('disconnect', function () use($socket) {
       global $names, $ids,$id,$io;
        // remove the username from global usernames list
        if(!$socket->username) return;// {
       $id=array_search($socket->id, $ids);
       unset($names[$id]);
       unset($ids[$id]);
         var_dump($id);
           // echo globally that this client has left
       $io->sockets->emit('client',$names,$ids);
        //}
   });
});

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}