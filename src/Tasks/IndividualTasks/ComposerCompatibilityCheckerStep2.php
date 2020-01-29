<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

//use either of the following to create the info.json file required
//your project will also require a composer.json.default file
//this file is used to reset the project to the default state before attempting to install each library
//composer info --format=json > info.json
//composer info --direct --format=json > info.json

class ComposerCompatibilityCheckerStep1 extends Task
{

    protected $taskStep = 's10';

    protected $infoFileFileName = 'composer-requirements-info.json';

    protected $resultsFileAsJSON = 'composer-requirements-info.upgraded.json';

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

    protected $lessUpgradeIsBetter = false;

    private $outputArray = [];

    public function run()
    {
        file_put_contents($this->getJsonFileLocationJSONResults(), '');

        $jsonFile = file_get_contents($this->getJsonFileLocation());
        $jsonData = json_decode($jsonFile, true);

        $libraries = $jsonData["installed"];

        foreach($libraries as $library){
            $this->resetProject();
            $commit = '';
            $name = $library["name"];
            $version = $library["version"];
            if(strpos($version, 'dev-master') !== false){
                $commit = $version;
                $version = str_replace(' ', '#', $version);
            }
            unset($libraryOutput);
            $libraryOutput = $this->mu()->execMe(
                $webRoot,
                "composer require " . $name . " '" . $version . "' 2>&1 ",
                'adding module'
            );
            if(in_array('  [InvalidArgumentException]', $libraryOutput)){
                $message = "composer require " . $name . " '" . $version . "' unsuccessful, could not find a matching version of package.\n";
                $this->mu()->colourPrint($message);
            }
            else if(in_array('Installation failed, reverting ./composer.json to its original content.', $libraryOutput)){
                $message = "composer require " . $name . " '" . $version . "' unsuccessful, searching for next best version.\n";
                $this->mu()->colourPrint($message);
                unset($show);
                $show = $this->mu()->execMe(
                    $webRoot,
                    "composer show -a " . $name . " 2>&1 ",
                    'show details of module'
                );
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
                    unset($output);
                    $output = $this->mu()->execMe(
                        $webRoot,
                        "composer require " . $name . " '" . $newVersion . "' 2>&1 ",
                        'show details of module',
                        false
                    );
                    if(! in_array('Installation failed, reverting ./composer.json to its original content.', $output)){
                        $versionFound = true;
                        $message = "composer require " . $name . " '" . $newVersion . "' is the next best version.\n";
                        $this->mu()->colourPrint($message);
                        $this->addToOutputArray($name, $newVersion);
                        break;
                    }
                    $message = "composer require " . $name . " '" . $newVersion . "' unsuccessful, searching for next best version.\n";
                    $this->mu()->colourPrint($message, false);
                    $output++;
                }

                if(!$versionFound){
                    $message = "Could not find any compatiable versions for:  " . $name . "!'\n ";
                    $this->mu()->colourPrint($message);
                }
            }
            else {
                $message = "composer require " . $name . " '" . $version . "' successful!\n ";
                $this->mu()->colourPrint($message);
                $version = $commit ? $commit : $version;
                $this->addToOutputArray($name, $version);
            }

        }


        file_put_contents(
            $this->getJsonFileLocationJSONResults(),
            json_encode($this->outputArray),
            FILE_APPEND | LOCK_EX
        );
    }

    public function resetProject($firstTime = false){
        $this->mu()->colourPrint('resetting project to composer.json.default', false);
        if($firstTime) {
            $this->mu()->execMe(
                $webRoot,
                'cp composer.json composer.json.temp.default',
                'make temporary copy of composer.json'
            );
        }
        $this->mu()->execMe(
            $webRoot,
            'rm composer.json',
            'remove composer.json'
        );
        $this->mu()->execMe(
            $webRoot,
            'cp composer.json.temp.default composer.json',
            'back to default composer file'
        );
        $this->mu()->execMe(
            $webRoot,
            'composer update',
            'run composer update'
        );
    }

    public function addToOutputArray($name, $version){
        $pos = strpos($name, '/') + 1;
        $array['folder'] = substr($name, $pos);
        $array['tag'] = $version;
        $array['repo'] = null;
        unset($strings);
        $strings = $this->mu()->execMe(
            $webRoot,
            "composer show -a " . $name . " 2>&1 ",
            'run composer update'
        );
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

    protected function getJsonFileLocation()
    {
        return $this->mu()->getWebRootDirLocation().$this->infoFileFileName;
    }

    protected function getJsonFileLocationJSONResults()
    {
        return $this->mu()->getWebRootDirLocation().$this->resultsFileAsJSON;
    }

}
