<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Traits;

trait Misc
{
    public function newLine()
    {
        if (PHP_SAPI === 'cli') {
            return PHP_EOL;
        }
        return nl2br("\n");
    }

    /**
     * returns path in a consistent format
     * e.g. /var/www
     *
     * @param  string $path
     *
     * @return string
     */
    public function checkIfPathExistsAndCleanItUp($path, $returnEvenIfItDoesNotExists = false): string
    {
        // $originalPath = $path;
        $path = str_replace('///', '/', $path);
        $path = str_replace('//', '/', $path);
        if (file_exists($path)) {
            $path = realpath($path);
        }
        if (file_exists($path) || $returnEvenIfItDoesNotExists) {
            return rtrim($path, '/');
        }
        user_error('Could not find path: ' . $path);
    }

    /**
     * Cleans an input string and returns a more natural human readable version
     * @param  string $str input string
     * @param  array  $noStrip
     *
     * @return string cleaned string
     */
    public function cleanCamelCase($str, array $noStrip = []): string
    {
        $str = str_replace('-', ' ', $str);
        $str = str_replace('_', ' ', $str);
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode('', $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);

        return str_replace(' ', '', $str);
    }

    protected function URLExists($url): bool
    {
        if ($url && $this->isValidURL($url)) {
            $headers = get_headers($url);
            if (is_array($headers) && count($headers)) {
                foreach ($headers as $header) {
                    if (substr($header, 9, 3) === '200') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    protected function isValidURL($url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return true;
    }

    protected function getCommandLineOrArgumentAsBoolean(string $variableName = ''): bool
    {
        if (PHP_SAPI === 'cli') {
            return isset($this->argv[1]) && $this->argv[1] === $variableName ? true : false;
        }
        return isset($_GET[$variableName]) ? true : false;
    }
}
