<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\SearchAndReplaceAPI;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class FixTemplateIncludeStatements extends Task
{
    protected $taskStep = 's30';

    protected $debug = false;

    private $extensionArray = [
        'ss',
    ];

    private $findArray = [
        '<% include ',
    ];

    public function getTitle()
    {
        return 'Add Namespacing to include statements in templates.';
    }

    public function getDescription()
    {
        return '
            Go through all templates and fix the include statements by adding namespacing e.g. % include Order % becomes % include Sunnysideup\Ecommerce\Includes\Order %.';
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
        $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
        foreach ($codeDirs as $baseNameSpace => $codeDir) {
            $baseDir = dirname($codeDir);
            //Start search machine from the module location. replace API
            $textSearchMachine = new SearchAndReplaceAPI($baseDir);
            $textSearchMachine->setIsReplacingEnabled(true);
            // $textSearchMachine->setFileNameMustContain('<% include');
            $this->mu()->colourPrint("Checking ${baseDir}");
            $baseDir = $this->mu()->checkIfPathExistsAndCleanItUp($baseDir);
            if (! file_exists($baseDir)) {
                $this->mu()->colourPrint("SKIPPING ${baseDir} as it does not exist.");
            } else {
                $textSearchMachine->setSearchPath($baseDir);
                $textSearchMachine->setExtensions($this->extensionArray); //setting extensions to search files within
                $this->mu()->colourPrint(
                    "++++++++++++++++++++++++++++++++++++\n" .
                    "CHECKING\n" .
                    "IN ${baseDir}\n" .
                    'FOR ' . implode(',', $this->extensionArray) . " FILES\n" .
                    'BASE ' . $baseDir . "\n" .
                    "++++++++++++++++++++++++++++++++++++\n"
                );
                foreach ($this->findArray as $finalFind) {
                    $caseSensitive = false;
                    $replacementType = 'BASIC';
                    $finalReplace = '<% include ' . $baseNameSpace . '\Includes';
                    $this->mu()->colourPrint(
                        '    --- FIND: ' . $finalFind . "\n" .
                        '    --- REPLACE: ' . $finalReplace . "\n"
                    );

                    $textSearchMachine->setSearchKey($finalFind, $caseSensitive, $replacementType);
                    $textSearchMachine->setReplacementKey($finalReplace);
                    // $textSearchMachine->setComment($comment);
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
