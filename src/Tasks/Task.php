<?php
/**
 * mu stands for Module Object
 * @var [type]
 */
namespace Sunnysideup\UpgradeToSilverstripe4\Tasks;

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;



abstract class Task
{
    /**
     * Are we running in debug mode
     * @var bool
     */
    protected $debug = false;

    /**
     * A static array for holding all the different Tasks that are running
     * @var Task[]
     */
    private static $_singleton = [];

    /**
     * Creates all the singletons and puts them in the array of singletons. Depending on if they exist already or not.
     * While creating the new instances it also passes a reference to the ModuleUpgrader or MU for short.
     *
     * @param  ModuleUpgrader $mu A reference to the ModuleUpgrader; to be passed on to all instantiated singletons
     * @param  array  $params Params that should be passed on to the given Task once it is instantiated
     * @return Task Returns the newly created task
     */
    public static function create($mu, $params = [])
    {
        $className = get_called_class();
        if (empty(self::$_singleton[$params['TaskName']])) {
            self::$_singleton[$params['TaskName']] = new $className($mu, $params);
        }

        return self::$_singleton[$params['TaskName']];
    }

    /**
     * Deletes reference to given task and removes it from list of tasks
     * @param array $params array containing the 'TaskName' of target task to delete
     * @return null
     */
    public static function delete($params)
    {
        self::$_singleton[$params['TaskName']] = null;
        unset(self::$_singleton[$params['TaskName']]);

        return null;
    }

    /**
     * Array of params that define this task, holds information such as its TaskName etc.
     * @var mixed
     */
    protected $params = [];

    /**
     * @var null|ModuleUpgrader the 'Manager' of all the modules responsible for holding Meta data and runtime
     * specific runtime arguments.
     */
    protected $mu = null;

    /**
     * On instantiation sets the parameters and a refernece to the parent ModuleUpgrader
     * @param ModuleUpgrader $mu Reference to the master ModuleUpgrader
     * @param array  $params All parameters that define this task or are required at any stage during its execution
     */
    public function __construct($mu, $params = [])
    {
        $this->params = $params;
        $this->mu = ModuleUpgrader::create();
    }

    /**
     * @return string Returns the 'TaskName' of the current Task, this is the main Identifier when
     * used in looking up and managing this task
     */
    public function getTitle()
    {
        return $this->params['TaskName'];
    }

    /**
     * Executes the seperate stages of this task in chronological ordering
     * @return null
     */
    public function run()
    {
        $this->starter();
        $this->upgrader($this->params);
        $this->ender();
    }

    /**
     * TODO explain this with NTHELP
     */
    abstract public function upgrader($params = []);


    /**
     * Runs everything that should be run and begining of execution, I.e commiting everything to get or creating a
     * backup branch before making changes
     */
    protected function starter()
    {
    }

    /**
     * Executed as the last step of a task. Used primarily for finishing off of changes made during execution of task.
     * I.e Making a git commit or tagging the new branch etc etc after all changes are made
     */
    protected function ender()
    {
        if ($this->hasCommit()) {
            $this->commitAndPush();
        }
    }

    /**
     * Does the task require the module changes to be committed after the task has run.
     * @return bool Defaults to true
     */
    protected function hasCommit()
    {
        return true;
    }

    /**
     * What to write in the commit message after the task is run, only useful if hasCommit() returns true
     * @var string message
     */
    protected $commitMessage = '';

    public function setCommitMessage($s)
    {
        $this->commitMessage = $s;

        return $this;
    }

    /**
     * The commit message that is used for the final git commit after running this task. IF none are set it will
     * return a default message
     * @return string commit message
     */
    protected function getCommitMessage()
    {
        if (! $this->commitMessage) {
            $this->commitMessage = 'MAJOR: upgrade to new version of Silverstripe - step: '.$this->getTitle();
        }
        return $this->commitMessage;
    }

    /**
     * Adds all files to Git staging and commits them with set commit message after execution and pushes it via git
     */
    protected function commitAndPush()
    {
        $message = $this->getCommitMessage();

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'git add . -A',
            'git add all',
            false
        );

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'git commit . -m "'.$message.'"',
            'commit changes: '.$message,
            false
        );

        $this->mu->execMe(
            $this->mu->getModuleDirLocation(),
            'git push origin '.$this->mu->getNameOfTempBranch(),
            'pushing changes to origin on the '.$this->mu->getNameOfTempBranch().' branch',
            false
        );
    }

    /**
     * TODO fill this out with NTHELP
     * Runs the SilverStripe made ModuleUpgrader
     * @param  [type] $task     [description]
     * @param  string $rootDir  modules root directory
     * @param  string $param1   [description]
     * @param  string $param2   [description]
     * @param  string $settings [description]
     * @return [type]           [description]
     */
    protected function runSilverstripeUpgradeTask($task, $rootDir = '', $param1 = '', $param2 = '', $settings = '')
    {
        if (! $rootDir) {
            $rootDir = $this->mu->getWebRootDirLocation();
        }
        $this->mu->execMe(
            $this->mu->getWebRootDirLocation(),
            'php '.$this->mu->getLocationOfUpgradeModule().' '.$task.' '.$param1.' '.$param2.' --root-dir='.$rootDir.' --write -vvv '.$settings,
            'running php upgrade '.$task.' see: https://github.com/silverstripe/silverstripe-upgrader',
            false
        );
    }
}