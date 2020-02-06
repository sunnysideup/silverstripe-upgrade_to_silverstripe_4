<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

class CreateListOfTasks
{
    protected $mu = null;

    public function run()
    {
        $this->mu = new ModuleUpgrader();
        foreach (array_keys($this->mu->getAvailableRecipes()) as $recipeKey) {
            $html = '';
            $this->mu->applyRecipe($recipeKey);
            $html .= '<h1>List of Tasks in run order for recipe: ' . $recipeKey . '</h1>';
            $count = 0;
            $totalCount = count($this->mu->getListOfTasks());
            $previousStep = '';
            foreach ($this->mu->getListOfTasks() as $class => $params) {
                $properClass = current(explode('-', $class));
                $nameSpacesArray = explode('\\', $class);
                $shortClassCode = end($nameSpacesArray);
                if (! class_exists($properClass)) {
                    $properClass = $this->mu->getDefaultNamespaceForTasks() . '\\' . $properClass;
                }
                if (class_exists($properClass)) {
                    $count++;
                    // $runItNow = $this->mu->shouldWeRunIt($shortClassCode);
                    $params['taskName'] = $shortClassCode;
                    $obj = $properClass::create($this, $params);
                    if ($obj->getTaskName()) {
                        $params['taskName'] = $obj->getTaskName();
                    }
                    $reflectionClass = new \ReflectionClass($properClass);
                    $path = 'https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/';
                    $path .= str_replace('\\', '/', $reflectionClass->getName()) . '.php';
                    $path = str_replace('Sunnysideup/UpgradeToSilverstripe4/', '', $path);
                    $currentStepCode = $obj->getTaskStepCode();
                    $currentStep = $obj->getTaskStep($currentStepCode);
                    if ($currentStepCode === 's00') {
                        //do nothing when it is an anytime step
                    } else {
                        if ($previousStep !== $currentStep) {
                            $html .= '<h2>' . $currentStep . '</h2>';
                        }
                        $previousStep = $currentStep;
                    }
                    $html .= '<h4>' . $count . '/' . $totalCount . ': ' . $obj->getTitle() . '</h4>';
                    $html .= '<p>' . $obj->getDescription() . '<br />';
                    $html .= '<strong>Code: </strong>' . $class;
                    $html .= '<br /><strong>Class Name: </strong>';
                    $html .= '<a href="' . $path . '">' . $reflectionClass->getShortName() . '</a>';
                    $html .= '</p>';
                    $obj = $properClass::deleteTask($params);
                } else {
                    user_error($properClass . ' could not be found as class', E_USER_ERROR);
                }
            }
            $dir = $this->mu->checkIfPathExistsAndCleanItUp(__DIR__ . '/../../docs/en/');
            if ($dir) {

                $html = str_replace(' _', ' \_', $html);

                file_put_contents(
                    rtrim($dir, '/') . '/AvailableTasks.md',
                    $html
                );
            } else {
                user_error('Coult not find '.$dir.' directory');
            }
        }
    }
}
