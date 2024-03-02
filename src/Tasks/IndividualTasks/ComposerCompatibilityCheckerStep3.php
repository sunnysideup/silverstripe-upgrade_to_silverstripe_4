<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

//use either of the following to create the info.json file required
//your project will also require a composer.json.default file
//this file is used to reset the project to the default state before attempting to install each library
//composer info --format=json > info.json
//composer info --direct --format=json > info.json

class ComposerCompatibilityCheckerStep3 extends Task
{
    protected $taskStep = 's10';

    protected $infoFileFileName = 'composer-requirements-info.json';

    protected $resultsFileAsJSON = 'composer-requirements-info.upgraded.json';

    protected $lessUpgradeIsBetter = false;

    private $outputArray = [];

    private $firstTimeReset = true;

    private $webRootLocation = '';

    public function getTitle()
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            return 'For moduls upgrades, this is not being used right now.';
        }
        return 'Check what composer requirements packages are best to use, using the '
            . $this->getJsonFileLocation() . ' file and placing results in '
            . $this->getJsonFileLocationJSONResults();
    }

    public function getDescription()
    {
        return '
            THIS IS STILL UNDER CONSTRUCTION!
            ';
    }

    /**
     * @param array $params
     * @return string|null
     */
    public function runActualTask($params = [])
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            return null;
        }
        $this->webRootLocation = $this->mu()->getWebRootDirLocation();

        file_put_contents($this->getJsonFileLocationJSONResults(), '');

        $jsonFile = file_get_contents($this->getJsonFileLocation());
        $jsonData = json_decode($jsonFile, true);

        $libraries = $jsonData['installed'] ?? [];

        $this->resetProject();
        foreach ($libraries as $library) {
            $commit = '';
            $name = $library['name'];
            $version = $library['version'];
            if (strpos($version, 'dev-master') !== false) {
                $commit = $version;
                $version = str_replace(' ', '#', $version);
            }
            unset($libraryOutput);
            $libraryOutput = $this->mu()->execMe(
                $this->webRootLocation,
                'composer require ' . $name . " '" . $version . "' 2>&1 --no-interaction",
                'adding module'
            );
            $message = 'composer require ' . $name . ':' . $version . ' ... ';
            if (in_array('  [InvalidArgumentException]', $libraryOutput, true)) {
                $message .= "unsuccessful, could not find a matching version of package.\n";
                $this->mu()->colourPrint($message);
            } elseif (in_array('Installation failed, reverting ./composer.json to its original content.', $libraryOutput, true)) {
                $message .= "unsuccessful, searching for next best version.\n";
                $this->mu()->colourPrint($message);
                unset($show);
                $show = $this->mu()->execMe(
                    $this->webRootLocation,
                    'composer show -a ' . $name . ' --no-interaction 2>&1 ',
                    'show details of module'
                );
                $versionsString = $show[3];
                $versionsString = str_replace('versions : ', '', $versionsString);
                $currentVersionPos = strpos($versionsString, ', ' . $version);
                $versionsString = substr($versionsString, 0, $currentVersionPos);
                $newerVersions = explode(', ', $versionsString);
                if ($this->lessUpgradeIsBetter) {
                    $newerVersions = array_reverse($newerVersions);
                }
                $output = 0;
                $versionFound = false;
                foreach ($newerVersions as $newVersion) {
                    unset($output);
                    $output = $this->mu()->execMe(
                        $this->webRootLocation,
                        'composer require ' . $name . " '" . $newVersion . "' 2>&1 ",
                        'show details of module',
                        false
                    );
                    $message = 'composer require ' . $name . ':' . $newVersion . ' ...... ';
                    if (! in_array('Installation failed', $output, true)) {
                        $versionFound = true;
                        $message .= "successful!, it is the next best version.\n";
                        $this->mu()->colourPrint($message);
                        $this->addToOutputArray($name, $newVersion);
                        break;
                    }
                    $message .= "unsuccessful, searching for next best version.\n";
                    $this->mu()->colourPrint($message, false);
                }

                if (! $versionFound) {
                    $message = 'Could not find any compatible versions for:  ' . $name . "!'\n ";
                    $this->mu()->colourPrint($message);
                }
            } else {
                $message .= "successful!\n ";
                $this->mu()->colourPrint($message);
                $version = $commit ?: $version;
                $this->addToOutputArray($name, $version);
            }
        }

        file_put_contents(
            $this->getJsonFileLocationJSONResults(),
            json_encode($this->outputArray),
            FILE_APPEND | LOCK_EX
        );

        return null;
    }

    protected function resetProject()
    {
        $this->mu()->colourPrint('resetting project to composer.json.default', false);
        if ($this->firstTimeReset === true) {
            $this->mu()->execMe(
                $this->webRootLocation,
                'cp composer.json composer.json.temp.default',
                'make temporary copy of composer.json'
            );
        }
        $fixer = FileSystemFixes::inst($this->mu())
            ->removeDirOrFile($this->webRootLocation . '/composer.json');
        $this->mu()->execMe(
            $this->webRootLocation,
            'cp composer.json.temp.default composer.json',
            'back to default composer file'
        );
        if ($this->firstTimeReset === false) {
            $this->mu()->execMe(
                $this->webRootLocation,
                'composer update',
                'run composer update'
            );
        }
        $this->firstTimeReset = false;
    }

    protected function addToOutputArray($name, $version)
    {
        $pos = strpos($name, '/') + 1;
        $array['folder'] = substr($name, $pos);
        $array['tag'] = $version;
        $array['repo'] = null;
        unset($strings);
        $strings = $this->mu()->execMe(
            $this->webRootLocation,
            'composer show -a ' . $name . ' 2>&1 ',
            'run composer update'
        );
        $source = '';

        foreach ($strings as $string) {
            if (strpos($string, 'source') !== false) {
                $source = $string;
                preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $source, $match);
                if (! isset($match[0][0])) {
                    preg_match_all(
                        '#((git|ssh|http(s)?)|(git@[\w\.]+))(:(//)?)([\w\.@\:/\-~]+)(\.git)(/)?#',
                        $source,
                        $match
                    );
                }
                if (isset($match[0][0])) {
                    $array['repo'] = $match[0][0];
                }
                break;
            }
        }

        array_push($this->outputArray, $array);
    }

    protected function getJsonFileLocation(): string
    {
        return $this->mu()->getWebRootDirLocation() . '/' . $this->infoFileFileName;
    }

    protected function getJsonFileLocationJSONResults(): string
    {
        return $this->mu()->getWebRootDirLocation() . '/' . $this->resultsFileAsJSON;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
