<?php

$context = new ZMQContext();
$socket = new ZMQSocket($context, ZMQ::SOCKET_REQ);
$socket->connect('tcp://localhost:6767');

echo "sending Hello world";
$socket->send(serialize(function() { return 'This is from a function'; }));
echo $socket->recv() . "\n";
