<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FindFiles;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class FindFilesWithSimpleUseStatements extends Task
{
    protected $taskStep = 's60';

    protected $listOfOKOnes = [
        'Page',
        'PageController',
    ];

    public function getTitle()
    {
        return 'Finds files simple use statements (may indicate error!)';
    }

    public function getDescription()
    {
        return '
            Goes through all the PHP files and makes sure that there are no simple use statements, apart from things like use \\page;. ';
    }

    public function runActualTask($params = [])
    {
        $errors = [];
        foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $this->mu()->colourPrint('Searching ' . $moduleDir, 'grey');
            $fileFinder = new FindFiles($moduleDir);
            $searchPath = $this->mu()->findMyCodeDir($moduleDir);
            if (file_exists($searchPath)) {
                $flatArray = $fileFinder
                    ->setSearchPath($searchPath)
                    ->setExtensions(['php'])
                    ->getFlatFileArray();
                if (is_array($flatArray) && count($flatArray)) {
                    foreach ($flatArray as $path) {
                        $this->mu()->colourPrint('Searching ' . $path, 'grey');
                        $className = basename($path, '.php');
                        $classNames = [];
                        $content = file_get_contents($path);
                        $tokens = token_get_all($content);
                        $namespace = '';
                        for ($index = 0; isset($tokens[$index]); $index++) {
                            if (! isset($tokens[$index][0])) {
                                continue;
                            }
                            if ($tokens[$index][0] === T_USE &&
                                $tokens[$index + 1][0] === T_WHITESPACE &&
                                $tokens[$index + 2][0] === T_STRING &&
                                $tokens[$index + 3] === ';'
                            ) {
                                $string = $tokens[$index + 2][1];
                                if (! in_array($string, $this->listOfOKOnes, true)) {
                                    $testPhrase = ltrim($string, '\\');
                                    if (! strpos($testPhrase, '\\')) {
                                        $errors[] = $path . ': ' . $tokens[$index][1] . $tokens[$index + 1][1] . $tokens[$index + 2][1] . ';';
                                    }
                                }
                                $index += 3; // Skip checked ones ...
                            }
                        }
                    }
                } else {
                    $this->mu()->colourPrint('Could not find any files in ' . $searchPath, 'red');
                }
            } else {
                $this->mu()->colourPrint('Could not find ' . $searchPath, 'blue');
            }
        }
        if (count($errors)) {
            $error = 'Found errors in use statements: ' . "\n---\n---\n---\n" . implode("\n ---\n", $errors);
            if (count($errors) > 10) {
                return $error;
            }
            $this->mu()->colourPrint($error, 'red');
        } else {
            $this->mu()->colourPrint('Clean bill of health in terms of use statements.', 'green');
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }

    protected function testme()
    {
        // $string = "<?php
        // echo 'xxx';";
        // /* Use tab and newline as tokenizing characters as well  */
        // $tok = token_get_all($string);
        //
        // for ($index = 0; isset($tok[$index]); $index++) {
        //     print_r($tok[$index]);
        //     echo '-----';
        // }
        // die('xxx');
    }
}
