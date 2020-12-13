<?php


namespace AutoBackup;

use AutoBackup\Exception\OptionsException;

abstract class ProviderOptions
{
    private array $options;

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws OptionsException
     */
    public function __get(string $name)
    {
        if (!isset($this->options[$name])) {
            throw new OptionsException("No option $name provided");
        }
        return $this->options[$name];
    }
}