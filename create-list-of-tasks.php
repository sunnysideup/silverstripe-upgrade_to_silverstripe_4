<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeSilverstripe\Api\CreateListOfTasks;

$obj = new CreateListOfTasks();

$obj->run();
