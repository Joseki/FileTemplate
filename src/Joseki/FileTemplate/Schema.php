<?php

namespace Joseki\FileTemplate;

class Schema
{

    /** @var array of FILE_NAME => templatePath */
    private $files;

    /** @var array */
    private $variables;



    /**
     * Schema constructor.
     * @param array $variables
     * @param array $files
     */
    public function __construct(array $variables, array $files)
    {
        $this->variables = $variables;
        $this->files = $files;
    }



    /**
     * @return array
     */
    public function getUndefinedVariables()
    {
        $keys = array_keys($this->variables, null);
        $undefinedVariables = array_intersect_key($this->variables, array_flip($keys));

        return array_keys($undefinedVariables);
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
        if (!array_key_exists($var, $this->variables)) {
            throw new InvalidArgumentException("Variable '$var' not found");
        }

        return $this->translate($this->variables[$var]);
    }



    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }



    private function format($var)
    {
        return sprintf('${%s}', $var);
    }



    public function translate($value)
    {
        $formattedVars = [];
        foreach (array_keys($this->variables) as $var) {
            $formattedVars[] = $this->format($var);
        }
        return str_replace($formattedVars, $this->variables, $value);
    }
}
