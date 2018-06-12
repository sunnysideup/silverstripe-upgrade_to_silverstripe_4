<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks;


abstract class MetaUpgraderTask
{

    protected function $params = null;

    /**
     * Module Object
     * @var [type]
     */
    protected function $mo = null;

    public function __construct($mo, $params = null)
    {
        $this->params = $params;
        $this->mo = $mo;
    }

    public function getTitle()
    {
        return $this->params['StepName'];
    }

    public function run()
    {
        $this->starter();
        $this->upgrader($this->params);
        $this->ender();
    }

    abstract function upgrader($params = []);


    protected function starter()
    {

    }

    protected function ender()
    {
        if($this->hasCommit()) {

        }
    }

    protected function hasCommit()
    {
        return true;
    }

    protected $commitMessage = 'MAJOR: upgrade to new version of Silverstripe - step: '.$this->getTitle();

    protected function commitMessage()
    {
        return $this->commitMessage;
    }


    /**
     * resets the upgrade dir
     * the upgrade dir is NOT the module dir
     * it is the parent dir in which everything takes place
     */
    protected function ResetWebRootDir($params = [])
    {

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
