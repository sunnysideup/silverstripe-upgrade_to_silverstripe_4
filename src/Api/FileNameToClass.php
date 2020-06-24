<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

class FileNameToClass
{
    /**
     * get the full name (name \ namespace) of a class from its file path
     * result example: (string) "I\Am\The\Namespace\Of\This\Class"
     *
     * @param string $filePathName
     *
     * @return  string
     */
    public function getClassFullNameFromFile(string $filePathName): string
    {
        return $this->getClassNamespaceFromFile($filePathName) . '\\' . $this->getClassNameFromFile($filePathName);
    }

    /**
     * build and return an object of a class from its file path
     *
     * @param string $filePathName
     * @return mixed
     */
    public function getClassObjectFromFile(string $filePathName)
    {
        $classString = $this->getClassFullNameFromFile($filePathName);

        return new $classString();
    }

    /**
     * get the class namespace form file path using token
     *
     * @param string $filePathName
     *
     * @return string|null
     */
    public function getClassNamespaceFromFile(string $filePathName): ?string
    {
        $src = (string) file_get_contents($filePathName);

        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if (! $namespace_ok) {
            return null;
        }
        return $namespace;
    }

    /**
     * get the class name form file path using token
     *
     * @param string $filePathName
     *
     * @return string
     */
    public function getClassNameFromFile(string $filePathName): string
    {
        $php_code = (string) file_get_contents($filePathName);

        $classes = [];
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] === T_CLASS
                && $tokens[$i - 1][0] === T_WHITESPACE
                && $tokens[$i][0] === T_STRING
            ) {
                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }
        if (isset($classes[0])) {
            return $classes[0];
        }
        return 'Error';
    }
}
