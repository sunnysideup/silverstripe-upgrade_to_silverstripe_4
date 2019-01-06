<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Adds a new branch to your repository that is going to be used for upgrading it.
 */
class FinaliseUpgradeWithMergeIntoMaster extends Task
{
    public function getTitle()
    {
        if($this->mu()->getRunIrreversibly()) {
            return 'Finalise Upgrade with merge into Master.';
        } else {
            return 'Finalise Upgrade with merge into Master (TURNED OFF)';
        }
    }

    public function getDescription()
    {
        if($this->mu()->getRunIrreversibly()) {
            return '
                CAREFUL - THIS STEP CAN NOT BE UNDONE. IT IS NOT RECOMMENDED!
                You should only run this upgrade task if you are 100% sure.
                You can turn off this task by setting `runIrreversibly` to false in the upgrader.
                You can do this as follows: `$upgrader->setRunIrreversibly(false)`
                 ' ;
        } else {
            return '
                This is currently turned off allowing you to run the upgrader more than once without any consequences to the project at hand.
                You can turn on this task by setting `runIrreversibly` to true in the upgrader.
                You can do this as follows: `$upgrader->setRunIrreversibly(true)`.';
        }
    }

    public function runActualTask($params = [])
    {
        $branchName = $this->mu()->getNameOfTempBranch();
        if($this->mu()->getRunIrreversibly()) {
            $this->mu()->execMe(
                $this->mu()->getModuleDirLocation(),
                '
                    cd '.$this->mu()->getModuleDirLocation().'
                    git checkout '.$branchName.'
                    git pull origin '.$branchName.'
                    git checkout master
                    git merge --squash '.$branchName.'
                    git commit . -m "MAJOR: upgrade to Silverstripe 4"
                    git push origin master
                    git branch -D '.$branchName.'
                    git push origin --delete '.$branchName.'
                ',
                'merging '.$branchName.' into master',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
