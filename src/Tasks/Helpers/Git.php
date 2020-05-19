<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers;
use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

class Git
{
    protected static $inst = null;

    protected $myMu = null;

    public static function inst($mu)
    {
        if (self::$inst === null) {
            self::$inst = new Git();
            self::$inst->setMu($mu);
        }
        return self::$inst;
    }

    public function Clone(string $dir, string $gitLink, string $gitRootDir, string $branchName)
    {
        $this->fetchAll($dir);

        $this->mu()->execMe(
            $dir,
            'git clone ' . $gitLink . ' ' . $gitRootDir,
            'clone ' . $gitLink . ' into ' . $gitRootDir,
            false
        );
        $this->mu()->execMe(
            $gitRootDir,
            'git branch -a',
            'check branch exists',
            false
        );
        $this->mu()->execMe(
            $gitRootDir,
            'git checkout ' . $branchName,
            'checkout ' . $branchName,
            false
        );
        $this->mu()->execMe(
            $gitRootDir,
            'git branch',
            'confirm branch',
            false
        );
    }

    /**
     *
     * @param string $dir
     * @param string $message
     * @param string $branchName
     *
     * @return Git
     */
    public function CommitAndPush(string $dir, string $message, string $branchName)
    {
        $this->fetchAll($dir);

        $this->mu()->execMe(
            $dir,
            'git add . -A',
            'git add all',
            false
        );

        $this->mu()->execMe(
            $dir,
            // 'if ! git diff --quiet; then git commit . -m "'.addslashes($message).'"; fi;',
            '
            if [ -z "$(git status --porcelain)" ]; then
                echo \'OKI DOKI - Nothing to commit\';
            else
                git commit . -m "' . addslashes($message) . '"
            fi',
            'commit changes: ' . $message,
            false
        );

        $this->mu()->execMe(
            $dir,
            'git push origin ' . $branchName,
            'pushing changes to origin on the ' . $branchName . ' branch',
            false
        );

        return $this;
    }

    /**
     *
     * @param  string $dir
     * @param  string $branchName
     *
     * @return Git
     */
    public function deleteBranch(string $dir, string $branchName)
    {
        $this->fetchAll($dir);

        $this->deleteBranchLocally($dir, $branchName);
        $this->deleteBranchRemotely($dir, $branchName);

        return $this;
    }

    /**
     *
     * @param  string $dir
     * @param  string $branchName
     *
     * @return Git
     */
    protected function deleteBranchLocally(string $dir, string $branchName)
    {
        $this->mu()->execMe(
            $dir,
            'if git show-ref --quiet refs/heads/' . $branchName . '; then git branch -d ' . $branchName . '; git push origin --delete ' . $this->mu()->getNameOfTempBranch() . '; fi',
            'delete branch (' . $branchName . ') locally',
            false
        );

        return $this;
    }

    /**
     *
     * @param  string $dir
     * @param  string $branchName
     *
     * @return Git
     */
    protected function deleteBranchRemotely(string $dir, string $branchName)
    {
        $this->mu()->execMe(
            $dir,
            'git push origin --delete ' . $branchName,
            'delete branch (' . $branchName . ') remotely',
            false
        );

        return $this;
    }

    /**
     *
     * @param  string $dir
     * @param  string $newBranchName
     * @param  string $fromBranchName
     * @return Git
     */
    public function createNewBranchIfItDoesNotExist(string $dir, string $newBranchName, string $fromBranchName)
    {
        $this->fetchAll($dir);

        $this->checkoutBranch($dir, $fromBranchName);
        $this->mu()->execMe(
            $dir,
            '
            if $(git ls-remote --heads ${REPO} ${BRANCH} | grep -q ' . "'refs/heads/" . $newBranchName . "'" . '); then
                    echo "branch exists"
                else
                    git checkout -b ' . $newBranchName . ' ' . $fromBranchName . '
                    git push origin ' . $newBranchName . ';
            fi',
            'create branch ' . $newBranchName . ' from the ' . $fromBranchName . ' branch in ' . $dir,
            false
        );

        return $this;
    }

    /**
     *
     * @param  string $dir
     * @param  string $newBranchName
     * @param  string $fromBranch
     * @return Git
     */
    public function createNewBranch(string $dir, string $newBranchName, string $fromBranch)
    {
        $this->fetchAll($dir);

        $this->checkoutBranch($dir, $fromBranch);
        $this->mu()->execMe(
            $dir,
            'git checkout -b ' . $newBranchName . ' ' . $fromBranch . '  && git push origin ' . $newBranchName,
            'create and checkout new branch: ' . $newBranchName . ' from ' . $fromBranch,
            false
        );
    }

    /**
     * @param  string $dir
     * @param  string $branch
     * @return Git
     */
    public function checkoutBranch(string $dir, string $branch)
    {
        $this->fetchAll($dir);

        $this->mu()->execMe(
            $dir,
            'git checkout ' . $branch,
            'check out : ' . $branch . ' as a starting point',
            false
        );
        $this->mu()->execMe(
            $this->mu()->getGitRootDir(),
            'git pull origin ' . $branch,
            'get the latest details for : ' . $branch . '',
            false
        );

        return $this;
    }

    /**
     *
     * @param string $dir
     * @param string $fromBranch
     * @param string $intoBranch
     * @return Git
     */
    public function Merge(string $dir, string $fromBranch, string $intoBranch)
    {
        $this->fetchAll($dir);

        $this->mu()->execMe(
            $dir,
            '
                git checkout ' . $fromBranch . '
                git pull origin ' . $fromBranch . '
                git checkout ' . $intoBranch . '
                git merge --squash ' . $fromBranch . '
                git commit . -m "MAJOR: upgrade merge"
                git push origin ' . $intoBranch . '
                git branch -D ' . $fromBranch . '
                git push origin --delete ' . $fromBranch . '
            ',
            'merging ' . $fromBranch . ' into ' . $intoBranch . ' in ' . $dir,
            false
        );
    }

    /**
     *
     * @param ModuleUpgrader $mu
     * @return Git
     */
    protected function setMu(ModuleUpgrader $mu)
    {
        $this->myMu = $mu;

        return $this;
    }

    /**
     *
     * @return ModuleUpgrader
     */
    protected function mu()
    {
        return $this->myMu;
    }

    /**
     *
     * @param  string $dir
     */
    protected function fetchAll(string $dir)
    {
        $this->mu()->execMe(
            $dir,
            'git fetch --all && git branch -a && git status',
            'get the latest',
            false
        );

        return $this;
    }
}
