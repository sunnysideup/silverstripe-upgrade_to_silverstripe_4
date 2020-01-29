<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

//use either of the following to create the info.json file required
//your project will also require a composer.json.default file
//this file is used to reset the project to the default state before attempting to install each library
//composer info --format=json > info.json
//composer info --direct --format=json > info.json

class ComposerCompatibilityCheckerStep1 extends Task
{

    protected $taskStep = 's10';

    protected $infoFileFileName = 'composer-requirements-info.json';

    protected $composerSettings = '--direct';

    public function getTitle()
    {
        return 'Composer requirements analysis STEP 1';
    }

    public function getDescription()
    {
        return '
            This first step works out what requirements you have before you upgrade.
            It saves the data into: '.$this->infoFileFileName.' (customisable).
            ';
    }

    public function run()
    {
        $this->mu()->execMe(
            $webRoot,
            'composer info '.$this->composerSettings.' --format=json > '.$this->infoFileFileName,
            'getting requirement details',
            false
        );

    }

    protected function hasCommitAndPush()
    {
        return true;
    }

}
