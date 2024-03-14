<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers;

use Sunnysideup\UpgradeToSilverstripe4\Traits\HelperInst;

class Git
{
    use HelperInst;

    public function Clone(string $dir, string $gitLink, string $gitRootDir, ?string $branchName = 'master'): Git
    {
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

        $this->fetchAll($gitRootDir);

        return $this;
    }

    public function CommitAndPush(string $dir, string $message, string $branchName): Git
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

    public function deleteBranch(string $dir, string $branchName): Git
    {
        $this->fetchAll($dir);

        $this->deleteBranchLocally($dir, $branchName);
        $this->deleteBranchRemotely($dir, $branchName);

        return $this;
    }

    public function createNewBranchIfItDoesNotExist(string $dir, string $newBranchName, string $fromBranchName): Git
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

    public function createNewBranch(string $dir, string $newBranchName, string $fromBranch): Git
    {
        $this->fetchAll($dir);

        $this->checkoutBranch($dir, $fromBranch);
        $this->mu()->execMe(
            $dir,
            'git checkout -b ' . $newBranchName . ' ' . $fromBranch,
            'create and checkout new branch: ' . $newBranchName . ' from ' . $fromBranch,
            false
        );
        $this->mu()->execMe(
            $dir,
            'git push -u origin ' . $newBranchName,
            'push it ' . $fromBranch,
            false
        );

        return $this;
    }

    public function checkoutBranch(string $dir, string $branch): Git
    {
        $this->fetchAll($dir);

        $this->mu()->execMe(
            $dir,
            'git checkout -b ' . $branch,
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

    public function Merge(string $dir, string $fromBranch, string $intoBranch): Git
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
            ',
            'merging ' . $fromBranch . ' into ' . $intoBranch . ' in ' . $dir,
            false
        );

        return $this;
    }

    public function deleteBranchRemotely(string $dir, string $branchName): Git
    {
        $this->mu()->execMe(
            $dir,
            'git push origin --delete ' . $branchName,
            'delete branch (' . $branchName . ') remotely',
            false
        );

        return $this;
    }

    public function fetchAll(string $dir): Git
    {
        $this->mu()->execMe(
            $dir,
            'git fetch --all && git branch -a && git status',
            'get the latest',
            false,
            '',
            false //verbose = false!
        );

        return $this;
    }

    protected function deleteBranchLocally(string $dir, string $branchName): Git
    {
        $this->mu()->execMe(
            $dir,
            'if git show-ref --quiet refs/heads/' . $branchName . '; then git branch -d ' . $branchName . '; git push origin --delete ' . $this->mu()->getNameOfTempBranch() . '; fi',
            'delete branch (' . $branchName . ') locally',
            false
        );

        return $this;
    }
}
