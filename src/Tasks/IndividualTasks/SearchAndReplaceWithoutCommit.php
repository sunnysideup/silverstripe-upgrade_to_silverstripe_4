<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

/**
 * Replaces a bunch of code snippets in preparation of the upgrade.
 * Controversial replacements will be replaced with a comment
 * next to it so you can review replacements easily.
 */
class SearchAndReplaceWithoutCommit extends SearchAndReplace
{
    protected function hasCommitAndPush()
    {
        return false;
    }
}
