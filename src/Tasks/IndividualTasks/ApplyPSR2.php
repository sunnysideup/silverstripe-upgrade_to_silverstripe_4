<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class ApplyPSR2 extends Task
{
    protected $taskStep = 's60';

    public function getTitle()
    {
        return 'Apply PSR2 Cleanup.';
    }

    public function getDescription()
    {
        return '
            Applies a light cleanup of the code to match PSR-2 standards.' ;
    }

    public function runActualTask($params = [])
    {
        foreach($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            $this->mu()->execMe(
                $codeDir,
                '
                    cd '.$codeDir.'
                    vendor/bin/php-cs-fixer fix ./ --using-cache=no --rules=@PSR2
                ',
                'Apply PSR-2 to '.$codeDir.' ('.$baseNameSpace.')',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
