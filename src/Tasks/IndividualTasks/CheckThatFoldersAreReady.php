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


    /**
     * Checks that the required folder variables link to folders that file_exists
     * and which are writable.
     */
    public function runActualTask($params = [])
    {
        $abovewebdir = $this->mu->getAboveWebRootDirLocation();
        $logdir = $this->mu->getLogFolderDirLocation();

        //check dir above web dir exists
        if(!file_exists($abovewebdir)){
            $this->mu->colourPrint("Above web dir does not exists: " . $abovewebdir, "red");
        } else {
            //Directory exists, now check if writable.
            if(!is_writable($abovewebdir)) {
                //Not writable send warning
                $this->mu->colourPrint("Above web dir is not writable: " . $abovewebdir, "red");
            } else{
                //It has been found and is writable; Success!
                $this->mu->colourPrint("Found and checked above web dir ✔", "green");
            }
        }

        //check that log dir is exists
        if(!file_exists($logdir)){
            $this->mu->colourPrint("Log dir not exists: " . $logdir, "red");
        } else {
            //Directory exists, now check if writable.
            if(!is_writable($logdir)){
                $this->mu->colourPrint("Log dir is not writable: " . $logdir, "red");
            } else {
                $this->mu->colourPrint("Found and checked log dir ✔", "green");
            }
        }

    }

    public function hasCommitAndPush()
    {
        return false;
    }

}
