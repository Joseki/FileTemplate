<?php

namespace Joseki\FileTemplate;

class Schema
{

	/** @var array of filenamePath => templatePath */
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
}
