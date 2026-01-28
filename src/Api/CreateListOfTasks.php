<?php

namespace Sunnysideup\UpgradeSilverstripe\Api;

use Sunnysideup\UpgradeSilverstripe\ModuleUpgrader;

class CreateListOfTasks
{

    private ModuleUpgrader $myMu;



    public function run()
    {
        $this->myMu = ModuleUpgrader::create();
        $html = '';
        $defaultNamespace = $this->mu()->getDefaultNamespaceForTasks();
        foreach (array_keys($this->mu()->getAvailableRecipes()) as $recipeKey) {
            $this->mu()->applyRecipe($recipeKey);
            $html .= '<h1>List of Tasks in run order for recipe: ' . $recipeKey . '</h1>';
            $count = 0;
            $totalCount = count($this->mu()->getListOfTasks());
            $previousStep = '';
            foreach ($this->mu()->getListOfTasks() as $class => $params) {
                $properClass = current(explode('-', $class));
                if (! class_exists($properClass)) {
                    $properClass = $defaultNamespace . '\\' . $properClass;
                    if (! class_exists($properClass)) {
                        foreach ($this->mu()->AdditionalNamespacesForTasks() as $namespace) {
                            $properClass = $defaultNamespace . '\\' . $namespace . '\\' . $properClass;
                            if (class_exists($properClass)) {
                                break;
                            }
                        }
                    }
                }
                if (class_exists($properClass)) {
                    $nameSpacesArray = explode('\\', $class);
                    $shortClassCode = end($nameSpacesArray);
                    $count++;
                    // $runItNow = $this->mu()->shouldWeRunIt((string) $shortClassCode);
                    $params['taskName'] = $shortClassCode;
                    $obj = $properClass::create($this, $params);
                    if ($obj->getTaskName()) {
                        $params['taskName'] = $obj->getTaskName();
                    }
                    $reflectionClass = new \ReflectionClass($properClass);
                    $path = 'https://github.com/sunnysideup/silverstripe-upgrade-silverstripe/tree/main/src/';
                    $path .= str_replace('\\', '/', $reflectionClass->getName()) . '.php';
                    $path = str_replace('Sunnysideup/UpgradeSilverstripe/', '', $path);
                    $currentStepCode = $obj->getTaskStepCode();
                    $currentStep = $obj->getTaskStep($currentStepCode);

                    if ($previousStep !== $currentStep) {
                        $html .= '<h2>' . $currentStep . '</h2>';
                    }
                    $previousStep = $currentStep;
                    $html .= '<h4>' . $count . '/' . $totalCount . ': ' . $obj->getTitle() . '</h4>';
                    $html .= '<p>' . $obj->getDescription() . '<br />';
                    $html .= '<strong>Class Code: </strong>' . $class;
                    $html .= '<br /><strong>Class Name: </strong>';
                    $html .= '<a href="' . $path . '">' . $reflectionClass->getShortName() . '</a>';
                    $html .= '</p>';
                    $obj = $properClass::deleteTask($params);
                } else {
                    user_error($properClass . ' could not be found as class', E_USER_ERROR);
                }
            }
        }
        $dir = $this->mu()->checkIfPathExistsAndCleanItUp(__DIR__ . '/../../docs/en/');
        if ($dir) {
            $html = str_replace(' _', ' \_', $html);
            file_put_contents(
                rtrim($dir, '/') . '/AvailableTasks.md',
                $html
            );
        } else {
            user_error('Could not find ' . $dir . ' directory');
        }
    }

    protected function mu()
    {
        return $this->myMu;
    }
}
