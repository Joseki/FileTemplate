<?php

namespace Joseki\FileTemplate;

use Nette\Utils\Strings;

class Schema
{

    /** @var array of FILE_NAME => templatePath */
    private $files;

    /** @var array */
    private $variables;
    
    private $resolved = false;



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
        if ($this->resolved) {
            throw new InvalidStateException("Cannot set variable '$var'. Variables has been already resolved and used in translation. This will lead into translation inconsistency.");
        }
        $this->variables[$var] = $answer;
    }



    public function getVariable($var)
    {
        if (!array_key_exists($var, $this->variables)) {
            throw new InvalidArgumentException("Variable '$var' not found");
        }

        return $this->translateValue($this->variables, $this->variables[$var]);
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
        $this->resolveVariables();
        return $this->translateValue($this->variables, $value);
    }



    private function translateValue($variables, $value)
    {
        $formattedVars = [];
        foreach (array_keys($variables) as $var) {
            $formattedVars[] = $this->format($var);
        }
        return str_replace($formattedVars, $variables, $value);
    }



    /**
     * @internal
     */
    public function resolveVariables()
    {
        if ($this->resolved) {
            return;
        }

        $variables = $this->variables;

        for ($i = 0; $i < count($this->variables); $i++) {
            $definedVars = [];
            foreach ($this->variables as $key => $value) {
                if (!Strings::match($value, '#\${\w+}#')) {
                    $definedVars[$key] = $value;
                    unset($variables[$key]);
                }
            }
            if (count($variables) === 0) {
                break;
            }
            foreach ($this->variables as $key => $value) {
                $this->setVariable($key, $this->translateValue($definedVars, $value));
            }
        }

        foreach ($this->variables as $key => $value) {
            if (Strings::match($value, '#\${\w+}#')) {
                throw new InvalidStateException(
                    "Variable '$key' ('$value') could not be resolved. Perhaps due undefined variable or circular dependencies."
                );
            }
        }

        $this->resolved = true;
    }
}
