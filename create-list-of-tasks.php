<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\Api\CreateListOfTasks;

$obj = new CreateListOfTasks();

$obj->run();
