<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks;

abstract class MetaUpgraderTask
{
    protected $params = [];

    /**
     * Module Object
     * @var [type]
     */
    protected $mo = null;

    public function __construct($mo, $params = [])
    {
        $this->params = $params;
        $this->mo = $mo;
    }

    public function getTitle()
    {
        return $this->params['TaskName'];
    }

    public function run()
    {
        $this->starter();
        $this->upgrader($this->params);
        $this->ender();
    }

    abstract public function upgrader($params = []);


    protected function starter()
    {
    }

    protected function ender()
    {
        if ($this->hasCommit()) {
        }
    }

    protected function hasCommit()
    {
        return true;
    }

    protected $commitMessage = '';

    protected function commitMessage()
    {
        if (! $this->commitMessage) {
            $this->commitMessage = 'MAJOR: upgrade to new version of Silverstripe - step: '.$this->getTitle();
        }
        return $this->commitMessage;
    }


    protected function commitAndPush($message)
    {
        $this->mo->execMe(
            $this->mo->getModuleDir(),
            'git add . -A',
            'git add all',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDir(),
            'git commit . -m "'.$message.'"',
            'commit changes: '.$message,
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDir(),
            'git push origin '.$this->mo->getNameOfTempBranch(),
            'pushing changes to origin on the '.$this->mo->getNameOfTempBranch().' branch',
            false
        );
    }


    protected function runSilverstripeUpgradeTask($task, $rootDir = '', $param1 = '', $param2 = '', $settings = '')
    {
        if (! $rootDir) {
            $rootDir = $this->mo->getWebRootDir();
        }
        $this->mo->execMe(
            $this->mo->getWebRootDir(),
            'php '.$this->mo->getLocationOfUpgradeModule().' '.$task.' '.$param1.' '.$param2.' --root-dir='.$rootDir.' --write -vvv '.$settings,
            'running php upgrade '.$task.' see: https://github.com/silverstripe/silverstripe-upgrader',
            false
        );
    }
}
