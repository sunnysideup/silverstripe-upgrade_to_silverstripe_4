<?php


namespace Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes;


abstract class BaseClass
{

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
            return user_error('You have not defined "'.$nameOfVar.'" in youre recipe.');
        }
    }
}
