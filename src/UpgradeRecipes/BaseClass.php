<?php


namespace Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes;


abstract class BaseClass
{


    /**
     * A list of task groups
     *
     * @var array
     */
    protected $taskSteps = [
        's00' => 'Generic',
        's10' => 'Prepare Codebase',
        's20' => 'Upgrade Structure',
        's30' => 'Prepare Code',
        's40' => 'Upgrade Code',
        's50' => 'Upgrade Fixes',
        's60' => 'Check',
        's70' => 'Finalise',
        's99' => 'ERROR!',
    ];

    public function getVariables() : array
    {
        $vars = [
            'nameOfTempBranch',
            'defaultNamespaceForTasks',
            'taskSteps',
            'listOfTasks',
            'frameworkComposerRestraint',
        ];
        foreach($vars as $var) {
            $vars[$var] = $this->returnValidValue($var);
        }
    }

    protected function returnValidValue($nameOfVar)
    {
        if(empty($this->$nameOfVar) === false) {
            return $this->$nameOfVar;
        } else {
            return user_error('You have not defined a variable "'.$nameOfVar.'" in youre recipe.');
        }
    }
}
