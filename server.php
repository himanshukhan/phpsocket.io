<?php

use Workerman\Worker;
use Workerman\WebServer;
use Workerman\Autoloader;
use PHPSocketIO\SocketIO;

// composer autoload
require_once __DIR__ . '/vendor/autoload.php';

$io = new SocketIO(2020);
$names = [];
$ids = [];
$id; //declaring the arrays to store names and socket id's and anvariable id.

$clients = [];
$bool = false;

$io->on('connection', function ($socket) {
    $socket->on('add-user', function($data) use ($socket) {
        global $clients;
        global $bool;

        if (count($clients) > 0) {
            $array_keys = array_keys($clients);
            if (in_array($data['username'], $array_keys)) {
                $bool = true;
            }
        }

        if ($bool) {
            $socket->emit('userExistonRegistration', "<h1>User exist: <strong>" . $data['username'] . "</strong></h1>");
        } else {
            $socket->emit('showchat', $data['username']);
            $clients[$data['username']] = [
                "socket" => $socket->id
            ];
        }
        $bool = false;
    });

    $socket->on('private-message', function($data) use ($socket) {
        global $clients;
        global $io;
        //console.log("Sending: " + data.content + " to " + data.username + " from " + data.from);
        if ($clients[$data['username']]) {
            $io->sockets->connected[$clients[$data['username']]['socket']]->emit("add-message", $data);
        } else {
            $socket->emit('userExist', "<h1>User does not exist: <strong>" . $data['username'] . "</strong></h1>");
        }
    });

    $socket->on('boardcast-message', function ($data) {
        global $io;
        $io->emit('boardcast-message', $data);
    });

    $socket->on('disconnect', function() use ($socket) {
        global $clients;
        foreach ($clients as $key => $client) {
            if ($client[$key]['socket'] === $socket->id) {
                unset($client[$key]);
                break;
            }
        }
    });
});

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}