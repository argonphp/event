<?php

require "vendor/autoload.php";

$event = new \Neeble\Event\EventManager;

$event->on('teste', function() {
	echo "TESTE\n";
});

$event->fire('teste');