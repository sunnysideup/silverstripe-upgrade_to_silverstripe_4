<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Git;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Install a basic / standard install of Silverstripe ('.$this->versionToLoad.')
 * using composer' ;
 */
class ComposerInstallProject extends Task
{
    protected $taskStep = 's20';

    protected $versionToLoad = '';

    /**
     * e.g. sunnysideup/ecommerce => master
     * e.g. sunnysideup/test => 1.2.3
     * @var array
     */
    protected $alsoRequire = [];

    protected $installModuleAsVendorModule = false;

    /**
     * @var array
     */
    protected $ignoredPackageForModuleRequirements = [
        'php',
        'silverstripe/recipe-plugin',
        'silverstripe/recipe-cms',
        'silverstripe/admin',
        'silverstripe/asset-admin',
        'silverstripe/assets',
        'silverstripe/campaign-admin',
        'silverstripe/config',
        'silverstripe/cms',
        'silverstripe/framework',
        'silverstripe/errorpage',
        'silverstripe/reports',
        'silverstripe/siteconfig',
        'silverstripe/versioned-admin',
        'silverstripe/versioned',
    ];

    protected $allowedPlugins = [
        'composer/installers',
        'silverstripe/recipe-plugin',
        'silverstripe/vendor-plugin',
    ];

    /**
     * @var string
     */
    protected $composerOptions = '--prefer-source --update-no-dev';

    protected $defaultSilverstripeProject = 'silverstripe/installer';

    public function getTitle()
    {
        return 'use Composer to install vanilla Silverstripe project and add project / module to it.';
    }

    public function getDescription()
    {
        return '
            Install a basic / standard install of Silverstripe (' . ($this->versionToLoad ?: $this->mu()->getFrameworkComposerRestraint()) . ')
            using composer and install module / project into the vanilla silverstripe install.';
    }

    public function runActualTask($params = []): ?string
    {
        $this->mu()->setBreakOnAllErrors(true);
        $fixer = FileSystemFixes::inst($this->mu())
            ->removeDirOrFile($this->mu()->getWebRootDirLocation(), $this->mu()->getAboveWebRootDirLocation())
            ->mkDir($this->mu()->getWebRootDirLocation(), $this->mu()->getAboveWebRootDirLocation());
        if (! $this->versionToLoad) {
            $this->versionToLoad = $this->mu()->getFrameworkComposerRestraint();
        }

        //install project / silverstripe clean.
        if ($this->mu()->getIsModuleUpgrade()) {
            $alterniveGitLink = $this->mu()->getParentProjectForModule();
            if ($alterniveGitLink) {
                $altBranch = $this->mu()->getParentProjectForModuleBranchOrTag();
                if (! $altBranch) {
                    $altBranch = 'master';
                }
                Git::inst($this->mu())
                    ->Clone(
                        $this->mu()->getWebRootDirLocation(),
                        $alterniveGitLink,
                        $this->mu()->getGitRootDir(),
                        $altBranch
                    );
            } else {
                $this->mu()->execMe(
                    $this->mu()->getAboveWebRootDirLocation(),
                    $this->mu()->getComposerEnvironmentVars() . ' composer create-project -n ' . $this->defaultSilverstripeProject . ' ' . $this->mu()->getWebRootDirLocation() . ' ' . $this->versionToLoad,
                    'set up vanilla install of ' . $this->defaultSilverstripeProject . ' - version: ' . $this->versionToLoad,
                    false
                );
                foreach($this->allowedPlugins as $plugin) {
                    $this->mu()->execMe(
                        $this->mu()->getWebRootDirLocation(),
                        $this->mu()->getComposerEnvironmentVars() . 'composer config --no-interaction allow-plugins.' . $plugin . ' true',
                        'composer config --no-interaction allow-plugins.' . $plugin . ' true',
                        false
                    );
                }
            }
        }
        if ($this->installModuleAsVendorModule) {
            Composer::inst($this->mu())
                ->ClearCache()
                ->Require(
                    $this->mu()->getVendorName() . '/' . $this->mu()->getPackageName(),
                    'dev-' . $this->mu()->getNameOfTempBranch(),
                    $this->composerOptions
                );
            $branch = $this->mu()->getParentProjectForModuleBranchOrTag() ?: 'master';
            if($this->mu()->getNameOfTempBranch() !== $branch) {
                $gitLink = $this->mu()->getGitLink();
                $command = '
                    git init;
                    git remote add origin ' . $gitLink . ';
                    git pull origin ' . $altBranch . ';
                    git status;';
                $this->mu()->execMe(
                    $this->mu()->getGitRootDir(),
                    $command,
                    'Make sure it is a git repo',
                    false
                );
            }
        } else {
            Git::inst($this->mu())
                ->Clone(
                    $this->mu()->getWebRootDirLocation(),
                    $this->mu()->getGitLink(),
                    $this->mu()->getGitRootDir(),
                    $this->mu()->getNameOfTempBranch()
                );
            if ($this->mu()->getIsModuleUpgrade()) {
                $this->workoutExtraRequirementsFromModule();
            }
        }
        foreach ($this->alsoRequire as $package => $version) {
            Composer::inst($this->mu())
                ->ClearCache()
                ->Require(
                    $package,
                    $version,
                    $this->composerOptions
                );
        }

        $this->mu()->setBreakOnAllErrors(false);
        return null;
    }

    protected function workoutExtraRequirementsFromModule()
    {
        $composerJson = ComposerJsonFixes::inst($this->mu())
            ->getJSON($this->mu()->getGitRootDir());
        if (isset($composerJson['require'])) {
            foreach ($composerJson['require'] as $package => $version) {
                if (in_array($package, $this->ignoredPackageForModuleRequirements, true)) {
                    $this->mu()->colourPrint('Skipping ' . $package . ' as requirement');
                } else {
                    if ($version === 'dev-master') {
                        $this->mu()->colourPrint('Sticking with dev-master as version for ' . $package);
                    } else {
                        $version = '*';
                    }
                    if (! isset($this->alsoRequire[$package])) {
                        $this->alsoRequire[$package] = $version;
                    }
                }
            }
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
