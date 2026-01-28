<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Api\SearchAndReplaceAPI;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

class ChangeControllerInitToProtected extends Task
{
    protected $taskStep = 'SS3->SS4';

    protected $debug = false;

    private $extensionArray = [
        'php',
    ];

    private $findArray = [
        '    public function init()',
        "\t" . 'public function init()',
        '    function init()',
        "\t" . 'function init()',
    ];

    public function getTitle()
    {
        return 'Change Controller::init function to protected';
    }

    public function getDescription()
    {
        return '
            Look for all init functions in Controllers (based on file name) and change to protected functions.';
    }

    public function setExtensionArray($a)
    {
        $this->extensionArray = $a;

        return $this;
    }

    public function setFindArray($a)
    {
        $this->findArray = $a;

        return $this;
    }

    public function runActualTask($params = []): ?string
    {
        foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $moduleDir = $this->mu()->findMyCodeDir($moduleDir);
            //Start search machine from the module location. replace API
            $textSearchMachine = new SearchAndReplaceAPI($moduleDir);
            $textSearchMachine->setIsReplacingEnabled(true);
            $textSearchMachine->setFileReplacementMaxCount(1);
            $textSearchMachine->setFileNameMustContain('Controller');
            $this->mu()->colourPrint("Checking ${moduleDir}");
            $moduleDir = $this->mu()->checkIfPathExistsAndCleanItUp($moduleDir);
            if (! file_exists($moduleDir)) {
                $this->mu()->colourPrint("SKIPPING ${moduleDir} as it does not exist.");
            } else {
                $textSearchMachine->setSearchPath($moduleDir);
                $textSearchMachine->setExtensions($this->extensionArray); //setting extensions to search files within
                $this->mu()->colourPrint(
                    "++++++++++++++++++++++++++++++++++++\n" .
                        "CHECKING\n" .
                        "IN ${moduleDir}\n" .
                        'FOR ' . implode(',', $this->extensionArray) . " FILES\n" .
                        'BASE ' . $moduleDir . "\n" .
                        "++++++++++++++++++++++++++++++++++++\n"
                );
                foreach ($this->findArray as $finalFind) {
                    $caseSensitive = false;
                    $replacementType = 'COMPLEX';
                    $comment = 'Controller init functions are now protected  please check that is a controller.';
                    $finalReplace = '    protected function init()';
                    $this->mu()->colourPrint(
                        '    --- FIND: ' . $finalFind . "\n" .
                            '    --- REPLACE: ' . $finalReplace . "\n"
                    );

                    $textSearchMachine->setSearchKey($finalFind, $caseSensitive, $replacementType);
                    $textSearchMachine->setReplacementKey($finalReplace);
                    $textSearchMachine->setComment($comment);
                    $textSearchMachine->startSearchAndReplace();
                }

                //SHOW TOTALS
                $replacements = $textSearchMachine->showFormattedSearchTotals();
                if (! $replacements) {
                    //flush output anyway!
                    $this->mu()->colourPrint('No replacements for  ' . implode(',', $this->extensionArray));
                }
                $this->mu()->colourPrint($textSearchMachine->getOutput());
            }
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
