<?php


namespace Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes;


abstract class BaseClass
{

    protected $varsToProvide = [
        'nameOfTempBranch',
        'defaultNamespaceForTasks',
        'taskSteps',
        'listOfTasks',
        'frameworkComposerRestraint',
    ];


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

    /**
     *
     * @return array
     */
    public function getVariables() : array
    {
        $returnArray = [];
        foreach($this->varsToProvide as $var) {
            $returnArray[$var] = $this->returnValidValue($var);
        }

        return $returnArray;
    }

    protected function returnValidValue($nameOfVar)
    {
        if(empty($this->$nameOfVar) === true) {
            return user_error('You have not defined a variable "'.$nameOfVar.'" in your recipe: '. __CLASS__);
        } else {
            return $this->$nameOfVar;
        }
    }
}
