<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

//use either of the following to create the info.json file required
//your project will also require a composer.json.default file
//this file is used to reset the project to the default state before attempting to install each library
//composer info --format=json > info.json
//composer info --direct --format=json > info.json

class ComposerCompatibilityChecker extends Task
{

    protected $taskStep = 's10';

    public function getTitle()
    {
        return 'Check what composer requirements packages are best to use.';
    }

    public function getDescription()
    {
        return '
            THIS IS STILL UNDER CONSTRUCTION!
            ';
    }



    protected $jsonFileLocation = "/var/www/ss3/337/info.json";

    protected  $outputArray = [];

    protected $resetOutputFiles = true;

    protected $lessUpgradeIsBetter = false;

    public function run()
    {
        $jsonFile = file_get_contents($this->jsonFileLocation);
        $jsonData = json_decode($jsonFile, true);
        $libraries = $jsonData["installed"];

        if($this->resetOutputFiles){
            file_put_contents('results.txt', '');
            file_put_contents('array.json', '');
        }

        $libraryOutput = 0;

        foreach($libraries as $library){
            $this->resetProject();
            $commit = '';
            $name = $library["name"];
            $version = $library["version"];
            if(strpos($version, 'dev-master') !== false){
                $commit = $version;
                $version = str_replace(' ', '#', $version);
            }
            $composerCommand = "composer require " . $name . " '" . $version . "' 2>&1 ";

            exec($composerCommand, $$libraryOutput, $return_var);


            if(in_array('  [InvalidArgumentException]', $$libraryOutput)){
                $message = "composer require " . $name . " '" . $version . "' unsuccessful, could not find a matching version of package.\n";
                $this->outputMessage($message);
            }
            else if(in_array('Installation failed, reverting ./composer.json to its original content.', $$libraryOutput)){
                $message = "composer require " . $name . " '" . $version . "' unsuccessful, searching for next best version.\n";
                $this->outputMessage($message);
                $composerCommand = "composer show -a " . $name . " 2>&1 ";
                exec($composerCommand, $show, $return_var);
                $versionsString = $show[3];
                $versionsString = str_replace('versions : ', '', $versionsString);
                $currentVersionPos = strpos($versionsString, ', ' . $version);
                $versionsString = substr($versionsString, 0, $currentVersionPos);
                $newerVersions = explode(", ", $versionsString);
                if($this->lessUpgradeIsBetter) {
                    $newerVersions = array_reverse($newerVersions);
                }
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

            $libraryOutput++;
        }


        file_put_contents('array.json', json_encode($this->outputArray), FILE_APPEND | LOCK_EX);
    }

    public function resetProject(){
        $this->outputMessage('reseting project to composer.json.default', false);
        exec('rm composer.json', $remove, $return_var);
        exec('cp composer.json.default composer.json', $copy, $return_var);
        exec('composer update', $update, $return_var);
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
        $array['repo'] = null;
        $composerCommand = "composer show -a " . $name . " 2>&1 ";
        exec($composerCommand, $strings, $return_var);
        $source = '';

        foreach ($strings as $string){
            if(strpos($string, 'source') !== false){
                $source = $string;
                preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $source, $match);
                if(!isset($match[0][0])){
                    preg_match_all('#((git|ssh|http(s)?)|(git@[\w\.]+))(:(//)?)([\w\.@\:/\-~]+)(\.git)(/)?#', $source, $match);
                }
                if(isset($match[0][0])){
                    $array['repo'] = $match[0][0];
                }
                break;
            }
        }

        array_push($this->outputArray, $array);
    }

}
