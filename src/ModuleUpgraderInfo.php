<?php

namespace Sunnysideup\UpgradeToSilverstripe4;

class ModuleUpgraderInfo
{
    public function printVarsForModule($mu, $moduleDetails)
    {
        //output the confirmation.
        $mu->colourPrint('---------------------', 'light_cyan');
        $mu->colourPrint('UPGRADE DETAILS', 'light_cyan');
        $mu->colourPrint('---------------------', 'light_cyan');

        $mu->colourPrint('- Type: ' . $mu->getIsModuleUpgradeNice(), 'light_cyan');

        $mu->colourPrint('- Recipe: ' . ($mu->getRecipe() ?: 'no recipe selected'), 'light_cyan');

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint('- Vendor Name: ' . $mu->getVendorName(), 'light_cyan');

        $mu->colourPrint('- Package Name: ' . $mu->getPackageName(), 'light_cyan');

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint('- Upgrade as Fork: ' . ($mu->getUpgradeAsFork() ? 'yes' : 'no'), 'light_cyan');

        $mu->colourPrint('- Run Interactively: ' . ($mu->getRunInteractively() ? 'yes' : 'no'), 'light_cyan');

        $mu->colourPrint('- Run Irreversibly: ' . ($mu->getRunIrreversibly() ? 'yes' : 'no'), 'light_cyan');

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint('- Vendor Namespace: ' . $mu->getVendorNamespace(), 'light_cyan');

        $mu->colourPrint('- Package Namespace: ' . $mu->getPackageNamespace(), 'light_cyan');

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint(
            '- Code Base Branch (what we started from): ' . $mu->getNameOfBranchForBaseCode(),
            'light_cyan'
        );

        $mu->colourPrint(
            '- Base Upgrade Branch (edit this branch manually if needed): ' . $mu->getNameOfUpgradeStarterBranch(),
            'light_cyan'
        );

        $mu->colourPrint(
            '- Automated Upgrade Branch (temp only, do not edit manually!): ' . $mu->getNameOfTempBranch(),
            'light_cyan'
        );

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint('- Upgrade Dir (root of install): ' . $mu->getWebRootDirLocation(), 'light_cyan');

        $mu->colourPrint('- Package Folder Name For Install: ' . $mu->getPackageFolderNameForInstall(), 'light_cyan');

        $mu->colourPrint('- Module / Project Dir(s): ' . implode(', ', $mu->getModuleDirLocations()), 'light_cyan');

        $mu->colourPrint('- Theme Dir: ' . ($mu->getThemeDirLocation() ?: 'not set'), 'light_cyan');

        $mu->colourPrint('- Git and Composer Root Dir: ' . $mu->getGitRootDir(), 'light_cyan');

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint('- Git Repository Link (SSH): ' . $mu->getGitLink(), 'light_cyan');

        $mu->colourPrint('- Git Repository Link (HTTPS): ' . $mu->getGitLinkAsHTTPS(), 'light_cyan');

        $mu->colourPrint('- Git Repository Link (RAW): ' . $mu->getGitLinkAsRawHTTPS(), 'light_cyan');

        $mu->colourPrint('- Origin composer file location: ' .
            ($mu->getOriginComposerFileLocation() ?: 'not set'), 'light_cyan');

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint('- Session file: ' . $mu->getSessionManager()->getSessionFileLocation(), 'light_cyan');

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint('- Last Step: ' . ($mu->getLastMethodRun() ?: 'not set'), 'light_cyan');

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint('- Log File Location: ' . ($mu->getLogFileLocation() ?: 'not logged'), 'light_cyan');

        $mu->colourPrint('- ---', 'light_cyan');

        $mu->colourPrint('- List of Steps: ' . $mu->newLine() . '    -' .
            implode($mu->newLine() . '    -', array_keys($mu->getListOfTasks())), 'light_cyan');

        $mu->colourPrint('---------------------', 'light_cyan');

        $mu->colourPrint('- parameter "again" ... runs last comand again', 'light_cyan');

        $mu->colourPrint('- parameter "restart" ... starts process from beginning', 'light_cyan');
    }
}
