<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Checks that all the directories needed to run this tool exist and are writable.
 */
class CheckThatFoldersAreReady extends Task
{
    protected $taskStep = 's10';

    public function getTitle()
    {
        return 'Check folders are ready for upgrade process';
    }

    public function getDescription()
    {
        return '
            Checks that all the directories needed to run this tool exist and are writable.
            ';
    }

    /**
     * Checks that the required folder variables link to folders that file_exists
     * and which are writable.
     */
    public function runActualTask($params = []): ?string
    {
        $abovewebdir = $this->mu()->getAboveWebRootDirLocation();
        //check dir above web dir exists
        if (! file_exists($abovewebdir)) {
            $this->mu()->colourPrint('Above web dir does not exists: ' . $abovewebdir, 'red');
            return 'No point in running tool with directory not ready';
        }
        //Directory exists, now check if writable.
        if (! is_writable($abovewebdir)) {
            //Not writable send warning
            $this->mu()->colourPrint('Above web dir is not writable: ' . $abovewebdir, 'red');
            return 'No point in running tool with directory not ready';
        }
        //It has been found and is writable; Success!
        $this->mu()->colourPrint('Found and checked above web dir ✔', 'green');

        //LogFileLocation
        $logDir = $this->mu()->getLogFolderDirLocation();
        if ($logDir) {
            //check that log dir is exists
            if (! file_exists($logDir)) {
                return 'Log dir not exists: ' . $logDir . '
                    set your log dir to an empty string if you prefer to continue without a log.';
            }
            //Directory exists, now check if writable.
            if (! is_writable($logDir)) {
                return $logDir . ' is not writable' . '.
                Set the log dir to an empty string or provide a writable directory. ';
            }
            //all ok
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
