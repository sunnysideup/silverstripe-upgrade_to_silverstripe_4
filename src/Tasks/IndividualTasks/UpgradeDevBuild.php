<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\ComposerJsonFixes;

/**
 * Remove installer folder from composer.json file so that package
 * installs into vendor folder.
 */
class UpgradeDevBuild extends Task
{
    protected $taskStep = 's20';

    protected $package = '';

    protected $newVersion = '';

    protected $newPackage = '';

    public function getTitle()
    {
        return 'Upgrade references to sake';
    }

    public function getDescription()
    {
        return '
            Upgrades any reference to framework/sake or framework/cli-script.php';
    }

    public function runActualTask($params = [])
    {
        $command = <<<'EOT'

        if (isset($data["scripts"])) {
            foreach ($data["scripts"] as $type => $commands) {
                foreach ($commands as $key => $command) {
                    $data[$type][$key] = str_replace("php framework/cli-script.php", "vendor/bin/sake", $command);
                    $data[$type][$key] = str_replace("framework/sake ", "vendor/bin/sake ", $command);
                }
            }
        }

EOT;
        $comment = 'Updating framework/sake to vendor/bin/sake';
        ComposerJsonFixes::inst($this->mu())->UpdateJSONViaCommandLine(
            $this->mu()->getGitRootDir(),
            $command,
            $comment
        );
        $this->setCommitMessage('MINOR: Updating framework/sake to vendor/bin/sake');
    }

    protected function hasCommitAndPush()
    {
        return $this->mu()->getIsModuleUpgrade();
    }
}
