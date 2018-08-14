<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class CheckThatFoldersAreReady extends Task
{
    public function getTitle()
    {
        return 'Check Folders Are Ready';
    }

    public function getDescription()
    {
        return '

            ' ;
    }


    public function runActualTask($params = [])
    {
        //check dir above web dir is ready
        //check that we can write to dir above web dir
        //check that log dir is ready
        //check that we can write to log dir
        //

    }
}
