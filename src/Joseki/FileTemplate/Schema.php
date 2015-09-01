<?php

namespace Joseki\FileTemplate;

class Schema
{

    /** @var array of FILE_NAME => templatePath */
    private $files;

    /** @var array */
    private $variables;

    /** @var array */
    private $undefinedVariables;



    /**
     * Schema constructor.
     * @param array $variables
     * @param array $files
     */
    public function __construct(array $variables, array $files)
    {
        $this->variables = $variables;
        $this->files = $files;

        $keys = array_keys($variables, null);
        $this->undefinedVariables = array_intersect_key($variables, array_flip($keys));
    }



    /**
     * @return array
     */
    public function getUndefinedVariables()
    {
        return array_keys($this->undefinedVariables);
    }



    /**
     * @param $var
     * @param $answer
     */
    public function setVariable($var, $answer)
    {
        $this->variables[$var] = $answer;
    }



    public function getVariable($var)
    {
        if (!array_key_exists("$$var$", $this->variables)) {
            throw new InvalidArgumentException("Variable '$var' not found");
        }

        return $this->translate($this->variables["$$var$"]);
    }



    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }



    public function translate($value)
    {
        return str_replace(array_keys($this->variables), $this->variables, $value);
    }
}
