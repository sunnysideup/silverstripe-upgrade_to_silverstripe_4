<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Checks that all the directories needed to run this tool exist and are writable.
 */
class CheckThatFoldersAreReady extends Task
{
    public function getTitle()
    {
        return 'Check Folders Are Ready';
    }

    public function getDescription()
    {
        return '
            Checks that all the directories needed to run this tool exist and are writable.
            ' ;
    }


    /**
     * Checks that the required folder variables link to folders that file_exists
     * and which are writable.
     */
    public function runActualTask($params = [])
    {

        $abovewebdir = $this->mu->getAboveWebRootDirLocation();
        //check dir above web dir exists
        if(! file_exists($abovewebdir)){
            $this->mu->colourPrint('Above web dir does not exists: ' . $abovewebdir, 'red');
            return 'No point in running tool with directory not ready';
        } else {
            //Directory exists, now check if writable.
            if(! is_writable($abovewebdir)) {
                //Not writable send warning
                $this->mu->colourPrint('Above web dir is not writable: ' . $abovewebdir, 'red');
                return 'No point in running tool with directory not ready';
            } else{
                //It has been found and is writable; Success!
                $this->mu->colourPrint('Found and checked above web dir ✔', 'green');
            }

        }

    }

    public function hasCommitAndPush()
    {
        return false;
    }

}
