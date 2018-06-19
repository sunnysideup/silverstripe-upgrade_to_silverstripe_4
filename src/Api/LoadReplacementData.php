<?php

/**
 * loads yml data if strings to replace in
 * code.
 *
 * Replacement data can be found in the following places:
 *
 * 1. root of this module:
 */

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

use SilverStripe\Upgrader\Util\ConfigFile;


class LoadReplacementData
{

    /**
     * Standard file name
     */
    const NAME = '.upgrade.replacements.yml';

    /**
     * Module Object
     * @var MetaUpgrader
     */
    protected $mo = null;

    public function __construct($mo, $params = [])
    {
        $this->params = $params;
        $this->mo = $mo;
        $this->fullArray = $this->getData();
        $count = 0;
        foreach ($this->fullArray as $to => $subArray) {
            $this->tos[$to] = $to;
            foreach ($subArray as $language => $subSubArray) {
                $this->languages[$language] = $language;
                foreach ($subSubArray as $findKey => $details) {
                    $replaceKey = $details['R'];
                    $this->flatFindArray[$language][$language."_".$to."_".$count] = $findKey;
                    $this->flatReplacedArray[$language][$language."_".$to."_".$count] = $replaceKey;
                    $count++;
                }
            }
        }
    }

    protected $to = 'SS4';

    public function setTo($s)
    {
        $this->to = $s;

        return $this;
    }

    public function getReplacementArrays()
    {
        if (!$this->to) {
            return $this->fullArray;
        }
        if (isset($this->fullArray[$this->to])) {
            return $this->fullArray[$this->to];
        } else {
            user_error("no data is available for upgrading to: ".$this->to);
        }

        return [];
    }

    protected $fullArray = [];


    protected $tos = [];

    public function getTos()
    {
        return $this->tos;
    }

    protected $languages = [];

    public function getLanguages()
    {
        return $this->languages;
    }

    protected $flatFindArray = [];

    public function getFlatFindArray()
    {
        return $this->flatFindArray;
    }

    protected $flatReplacedArray = [];

    public function getFlatReplacedArray()
    {
        return $this->flatReplacedArray;
    }

    protected $paths = [];

    protected function getPaths()
    {
        $array = [
            $this->mo->getModuleDirLocation(),
        ];
        $globalFixes = $this->mo->checkIfPathExistsAndCleanItUp(__DIR__.'/../../');
        if($globalFixes) {
            $array[] = $globalFixes;
        }
        $this->paths = array_unique($array);
    }

    protected function getData()
    {
        $this->getPaths();
        // Merge with any other upgrade spec in the top level
        $config = [];
        foreach ($this->paths as $path) {
            $nextFile = $path . DIRECTORY_SEPARATOR . static::NAME;
            if (file_exists($nextFile)) {
                $nextConfig = ConfigFile::loadConfig($nextFile);
                // Merge
                $config = $this->mergeConfig($config, $nextConfig);
            } else {
                $this->mo->colourPrint('could not find: '.$nextFile);
            }
        }
        ksort($config);

        return $config;
    }


    protected static function mergeConfig(array $left, array $right)
    {
        //see ConfigFile for original
        $merged = $left;
        foreach ($right as $key => $value) {
            // if non-associative, just merge in unique items
            if (is_numeric($key)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
                continue;
            }

            // If not merged into left hand side, then simply assign
            if (!isset($merged[$key])) {
                $merged[$key] = $value;
                continue;
            }

            // Make sure both sides are the same type
            if (is_array($merged[$key]) !== is_array($value)) {
                user_error(
                    "Config option $key cannot merge non-array with array value."
                );
            }

            // If array type, then merge
            if (is_array($value)) {
                $merged[$key] = $this->mergeConfig($merged[$key], $value);
                continue;
            }

            // If non array types, don't merge, but instead assert both values are set
            if ($merged[$key] !== $value) {
                user_error(
                    "Config option $key is defined with different values in multiple files."
                );
            }
        }

        return $merged;
    }



}
