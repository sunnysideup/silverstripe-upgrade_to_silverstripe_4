<?php
/**
 * mo stands for Module Object
 * @var [type]
 */
namespace Sunnysideup\UpgradeToSilverstripe4\Tasks;

abstract class MetaUpgraderTask
{
    protected $debug = false;

    private static $_singleton = [];

    public static function create($mo, $params = [])
    {
        $className = get_called_class();
        if (empty(self::$_singleton[$params['TaskName']])) {
            self::$_singleton[$params['TaskName']] = new $className($mo, $params);
        }

        return self::$_singleton[$params['TaskName']];
    }

    public static function delete($params)
    {
        self::$_singleton[$params['TaskName']] = null;
        unset(self::$_singleton[$params['TaskName']]);

        return null;
    }

    protected $params = [];

    /**
     * Module Object
     * @var MetaUpgrader
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
            $this->commitAndPush();
        }
    }

    protected function hasCommit()
    {
        return true;
    }

    protected $commitMessage = '';

    public function setCommitMessage($s)
    {
        $this->commitMessage = $s;

        return $this;
    }

    protected function getCommitMessage()
    {
        if (! $this->commitMessage) {
            $this->commitMessage = 'MAJOR: upgrade to new version of Silverstripe - step: '.$this->getTitle();
        }
        return $this->commitMessage;
    }


    protected function commitAndPush()
    {
        $message = $this->getCommitMessage();

        $this->mo->execMe(
            $this->mo->getModuleDirLocation(),
            'git add . -A',
            'git add all',
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDirLocation(),
            'git commit . -m "'.$message.'"',
            'commit changes: '.$message,
            false
        );

        $this->mo->execMe(
            $this->mo->getModuleDirLocation(),
            'git push origin '.$this->mo->getNameOfTempBranch(),
            'pushing changes to origin on the '.$this->mo->getNameOfTempBranch().' branch',
            false
        );
    }


    protected function runSilverstripeUpgradeTask($task, $rootDir = '', $param1 = '', $param2 = '', $settings = '')
    {
        if (! $rootDir) {
            $rootDir = $this->mo->getWebRootDirLocation();
        }
        $this->mo->execMe(
            $this->mo->getWebRootDirLocation(),
            'php '.$this->mo->getLocationOfUpgradeModule().' '.$task.' '.$param1.' '.$param2.' --root-dir='.$rootDir.' --write -vvv '.$settings,
            'running php upgrade '.$task.' see: https://github.com/silverstripe/silverstripe-upgrader',
            false
        );
    }
}
