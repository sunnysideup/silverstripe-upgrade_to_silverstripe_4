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
     * Are we running in debug mode?
     * @var bool
     */
    protected $debug = false;

    /**
     * A static array for holding all the different Tasks that are running
     * @var Task[]
     */
    private static $_singletons = [];

    /**
     * set a specific task name if needed
     * @var string
     */
    protected $taskName = '';

    public function getTaskName()
    {
        return $this->taskName;
    }

    protected $taskStep = 's99';

    public function getTaskStepCode()
    {
        return $this->taskStep;
    }

    public function getTaskStep($currentStepCode = '')
    {
        $taskSteps = $this->mu()->getTaskSteps();
        if(! $currentStepCode) {
            $currentStepCode = $this->getTaskStepCode();
        }

        return $taskSteps[$currentStepCode];
    }

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
        if (empty(self::$_singletons[$params['taskName']])) {
            self::$_singletons[$params['taskName']] = new $className($mu, $params);
        }

        return self::$_singletons[$params['taskName']];
    }

    /**
     * Deletes reference to given task and removes it from list of tasks
     * @param array $params array containing the 'taskName' of target task to delete
     *
     * @return null
     */
    public static function deleteTask($params)
    {
        self::$_singletons[$params['taskName']] = null;
        unset(self::$_singletons[$params['taskName']]);

        return null;
    }

    /**
     * Array of params that define this task, holds information such as its taskName etc.
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

    public function mu()
    {
        if (! $this->mu) {
            $this->mu = ModuleUpgrader::create();
        }

        return $this->mu;
    }

    /**
     * returns title of the task at hand ...
     *
     * @return string
     */
    abstract public function getTitle();

    /**
     *
     * @return string
     */
    abstract public function getDescription();

    /**
     * remove white space from description and add # at the end
     * lines are also wordwrapped
     *
     * @return string
     */
    public function getDescriptionNice()
    {
        $des = $this->getDescription();
        $des = trim(preg_replace('/\s+/', ' ', $des));
        $des = trim(wordwrap($des));
        $des = str_replace("\n", "\n".'# ', $des);

        return $des;
    }

    /**
     * Executes the seperate stages of this task in chronological ordering
     */
    public function run()
    {
        $this->starter($this->params);
        $error = $this->runActualTask($this->params);
        if (is_string($error) && strlen($error) > 0) {
            $this->mu()->colourPrint("\n\n".'------------------- EXIT WITH ERROR -------------------------', 'red');
            $this->mu()->colourPrint($error, 'red');
            $this->mu()->colourPrint("\n\n".'------------------- EXIT WITH ERROR -------------------------', 'red');
            die("\n\n\n---");
        }
        $this->ender($this->params);
    }

    /**
     * Runs everything that should be run and begining of execution, I.e commiting everything to get or creating a
     * backup branch before making changes
     */
    protected function starter($params = [])
    {
        foreach ($params as $paramKey => $paramValue) {
            if (isset($this->$paramKey)) {
                $this->$paramKey = $paramValue;
            } else {
                user_error('You are trying to set '.$paramKey.' but it is meaninguless to this class: '.get_called_class());
            }
        }
    }

    /**
     * runs the actual task and needs to be defined in any class that extends
     * this class.
     *
     * When it returns a string, we regard this to be a description of a fatal error!
     *
     * @return null|string
     */
    abstract public function runActualTask($params = []);


    /**
     * Executed as the last step of a task. Used primarily for finishing off of changes made during execution of task.
     * I.e Making a git commit or tagging the new branch etc etc after all changes are made
     */
    protected function ender()
    {
        if ($this->hasCommitAndPush()) {
            $this->commitAndPush();
        }
    }

    /**
     * Does the task require the module changes to be committed after the task has run.
     * @return bool Defaults to true
     */
    abstract protected function hasCommitAndPush();

    /**
     * What to write in the commit message after the task is run, only useful if hasCommitAndPush() returns true
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
        if($this->mu()->getIsModuleUpgrade()) {
            $moduleDirs = $this->mu()->getExistingModuleDirLocations();
        } else {
            $moduleDirs = [$this->mu()->getWebRootDirLocation()];
        }
        foreach($moduleDirs as $moduleDir) {
            $message = $this->getCommitMessage();
            $this->mu()->execMe(
                $moduleDir,
                'git add . -A',
                'git add all',
                false
            );

            $this->mu()->execMe(
                $moduleDir,
                // 'if ! git diff --quiet; then git commit . -m "'.addslashes($message).'"; fi;',
                '
                if [ -z "$(git status --porcelain)" ]; then
                    echo \'OKI DOKI - Nothing to commit\';
                else
                    git commit . -m "'.addslashes($message).'"
                fi',
                'commit changes: '.$message,
                false
            );

            $this->mu()->execMe(
                $moduleDir,
                'git push origin '.$this->mu()->getNameOfTempBranch(),
                'pushing changes to origin on the '.$this->mu()->getNameOfTempBranch().' branch',
                false
            );
        }
    }

    /**
     * Runs the SilverStripe made upgrader
     * @param  string $task     [description]
     * @param  string $param1   [description]
     * @param  string $param2   [description]
     * @param  string $rootDirForCommand  modules root directory
     * @param  string $settings [description]
     * @return [type]           [description]
     */
    protected function runSilverstripeUpgradeTask(
        $task,
        $param1 = '',
        $param2 = '',
        $rootDirForCommand = '',
        $settings = ''
    )
    {
        if (! $rootDirForCommand) {
            $rootDirForCommand = $this->mu()->getWebRootDirLocation();
        }
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'php '.$this->mu()->getLocationOfSSUpgradeModule().' '.$task.' '.$param1.' '.$param2.' --root-dir='.$rootDirForCommand.' --write -vvv '.$settings,
            'running php upgrade '.$task.' see: https://github.com/silverstripe/silverstripe-upgrader',
            false
        );
    }


    public function getJSON($dir)
    {
        $location = $dir.'/composer.json';
        return json_decode($jsonString, true);
    }

    public function setJSON($dir, $data)
    {
        $location = $dir.'/composer.json';
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents("'.$location.'", $newJsonString);
        return $this;
    }

    public function updateJSONViaCommandLine($dir, $code, $comment)
    {
        $location = $dir.'/composer.json';
        $this->mu()->execMe(
            $dir,
            'php -r  \''
                .'$jsonString = file_get_contents("'.$location.'"); '
                .'$data = json_decode($jsonString, true); '
                .$code
                .'$newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); '
                .'file_put_contents("'.$location.'", $newJsonString); '
                .'\'',
            $comment . ' --- in '.$location,
            false
        );
    }
}
