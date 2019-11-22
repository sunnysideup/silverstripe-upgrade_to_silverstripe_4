<?php
//use either of the following to create your info.json file
//composer info --format=json > info.json
//composer info --direct --format=json > info.json

class ComposerCompatibilityChecker {

    public $jsonFileLocation = "/var/www/ss3/337/info.json";

    public $outputArray = [];

    public $resetOutputFiles = true;

    public function run(){
        $jsonFile = file_get_contents($this->jsonFileLocation);
        $jsonData = json_decode($jsonFile, true);
        $libraries = $jsonData["installed"];

        if($this->resetOutputFiles){
            file_put_contents('results.txt', '');
            file_put_contents('array.txt', '');
        }


        foreach($libraries as $library){
            $commit = '';
            $name = $library["name"];
            $version = $library["version"];
            if(strpos($version, 'dev-master') !== false){
                $commit = $version;
                $version = str_replace(' ', '#', $version);
            }
            $composerCommand = "composer require " . $name . " '" . $version . "' 2>&1 ";

            exec($composerCommand, $output, $return_var);

            if(in_array('Installation failed, reverting ./composer.json to its original content.', $output)){
                $message = "composer require " . $name . " '" . $version . "' unsuccessful, searching for next best version.\n";
                $this->outputMessage($message);
                $composerCommand = "composer show -a " . $name . " 2>&1 ";
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
                        $this->outputMessage($message);
                        $this->addToOutputArray($name, $newVersion);
                        break;
                    }
                    $message = "composer require " . $name . " '" . $newVersion . "' unsuccessful, searching for next best version.\n";
                    $this->outputMessage($message, false);
                    $output++;
                }

                if(!$versionFound){
                    $message = "Could not find any compatiable versions for:  " . $name . "!'\n ";
                    $this->outputMessage($message);
                }
            }
            else {
                $message = "composer require " . $name . " '" . $version . "' successful!\n ";
                $this->outputMessage($message);
                $version = $commit ? $commit : $version;
                $this->addToOutputArray($name, $version);
            }
        }


        file_put_contents('array.json', json_encode($this->outputArray), FILE_APPEND | LOCK_EX);
    }


    public function outputMessage($message, $toFile = true){
        echo $message;
        if($toFile){
            file_put_contents('results.txt', $message, FILE_APPEND | LOCK_EX);
        }
    }

    public function addToOutputArray($name, $version){
        $pos = strpos($name, '/') + 1;
        $array['folder'] = substr($name, $pos);
        $array['tag'] = $version;
        $composerCommand = "composer show -a " . $name . " 2>&1 ";
        exec($composerCommand, $strings, $return_var);
        $source = '';
        foreach($strings as $string){
            if(strpos($string, 'source') !== false){
                $source = $string;
                break;
            }
        }
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $source, $match);
        $array['repo'] = $match[0][0];
        array_push($this->outputArray, $array);
    }

}

$me = new ComposerCompatibilityChecker();
$me->run();
