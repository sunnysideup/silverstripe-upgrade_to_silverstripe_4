<?php

$getInfo = 'composer info --format=json > info.json';

$jsonFile = file_get_contents("/var/www/ss3/337/info.json");
$jsonData = json_decode($jsonFile, true);


$libraries = $jsonData["installed"];


foreach($libraries as $library){
    $name = $library["name"];
    $version = $library["version"];
    if(strpos($version, 'dev-master') !== false){
        $version = str_replace(' ', '#', $version);
    }
    // echo "Name: " . $name . "<br>";
    // echo "Version: " . $version . "<br>";
    // echo "====================================================================<br><br><br><br>";
    $composerCommand = "composer require " . $name . " '" . $version . "' 2>&1 ";

    exec($composerCommand, $output, $return_var);

    if(in_array('Installation failed, reverting ./composer.json to its original content.', $output)){
        $message = "composer require " . $name . " '" . $version . "' unsuccessful, searching for next best version.\n";
        outputMessage($message);
        $composerCommand = "composer show -a " . $name . " --format=json 2>&1 ";
        exec($composerCommand, $show, $return_var);
        $versionsString = $show[3];
        $versionsString = str_replace('versions : ', '', $versionsString);
        $currentVersionPos = strpos($versionsString, ', ' . $version);
        $versionsString = substr($versionsString, 0, $currentVersionPos);
        $newerVersions = explode(", ", $versionsString);
        $newerVersions = array_reverse($newerVersions);
        $output = 0;
        $versionFound = false;
        foreach($newerVersions as $newVersion){
            $composerCommand = "composer require " . $name . " '" . $newVersion . "' 2>&1 ";
            exec($composerCommand, $$output, $return_var);
            if(! in_array('Installation failed, reverting ./composer.json to its original content.', $$output)){
                $versionFound = true;
                $message = "composer require " . $name . " '" . $newVersion . "' is the next best version.\n";
                outputMessage($message);
                break;
            }
            $message = "composer require " . $name . " '" . $newVersion . "' unsuccessful, searching for next best version.\n";
            outputMessage($message, false);
            $output++;
        }

        if(!$versionFound){
            $message = "Could not find any compatiable versions for:  " . $name . "!'\n ";
            outputMessage($message);
        }
    }
    else {
        $message = "composer require " . $name . " '" . $version . "' successful!\n ";
        outputMessage($message);
    }
}


function outputMessage($message, $toFile = true){
    echo $message;
    if($toFile){
        file_put_contents('results.txt', $message, FILE_APPEND | LOCK_EX);
    }
}

