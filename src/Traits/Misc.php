<?php

trait Misc
{

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

    protected function getCommandLineOrArgumentAsBoolean(string $variableName = '') : bool
    {
        if (PHP_SAPI === 'cli') {
            return isset($this->argv[1]) && $this->argv[1] === $variableName ? true : false;
        } else {
            return isset($_GET[$variableName]) ? true : false;
        }
    }
}
