<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core\File;

class Filter
{
	const METHOD_EXCLUDE = 1;
	const METHOD_INCLUDE = 2;

	/**
	 * @var	int
	 */
	protected $defaultMethod;

	/**
	 * @var	array
	 */
	protected $exclusionRules = [];

	/**
	 * @var	array
	 */
	protected $inclusionRules = [];

	/**
	 * @return	void
	 */
	public function __construct()
	{
		$this->defaultMethod = self::METHOD_EXCLUDE;
	}

	/**
	 * @param	\Closure	$rule
	 * @return	self
	 */
	public function addExclusionRule( \Closure $rule )
	{
		$this->exclusionRules[] = $rule;
		return $this;
	}

	/**
	 * @param	\Closure	$rule
	 * @return	self
	 */
	public function addInclusionRule( \Closure $rule )
	{
		$this->inclusionRules[] = $rule;
		return $this;
	}

	/**
	 * Runs an array of files through filters and returns the result
	 *
	 * @param	array	$files		Array of Huxtable\Core\FileInfo objects
	 * @param	int		$method		self::METHOD_EXCLUDE or self::METHOD_INCLUDE
	 * @return	array
	 */
	public function filterFiles( array $files, $method = -1 )
	{
		switch( $method )
		{
			case self::METHOD_EXCLUDE:
				$result = $this->filterFilesByExclusion( $files );
				break;

			case self::METHOD_INCLUDE:
				$result = $this->filterFilesByInclusion( $files );
				break;

			default:
				$result = $this->filterFiles( $files, $this->defaultMethod );
				break;
		}

		return $result;
	}

	/**
	 * Return all files unless they are excluded by a rule (i.e., blacklisting)
	 *
	 * @param	array	$files		Array of Huxtable\Core\FileInfo objects
	 * @return	array
	 */
	protected function filterFilesByExclusion( array $files )
	{
		$result = [];

		foreach( $files as $file )
		{
			if( !($file instanceof \Huxtable\Core\FileInfo ) )
			{
				continue;
			}

			// Always exclude the following
			$alwaysExclude = ['.','..','.DS_Store'];
			if( in_array( $file->getFilename(), $alwaysExclude ) )
			{
				continue;
			}

			/*
			 * Apply exclusion rules; one is all it takes
			 */
			$shouldExclude = false;
			foreach( $this->exclusionRules as $rule )
			{
				$shouldExclude = $shouldExclude || call_user_func( $rule, $file );
			}

			if( !$shouldExclude )
			{
				$result[] = $file;
			}
		}

		return $result;
	}

	/**
	 * Return no files unless they are included by a rule (i.e., whitelisting)
	 *
	 * @param	array	$files		Array of Huxtable\Core\FileInfo objects
	 * @return	array
	 */
	protected function filterFilesByInclusion( array $files )
	{
		$result = [];

		foreach( $files as $file )
		{
			if( !($file instanceof \Huxtable\Core\FileInfo ) )
			{
				continue;
			}

			/*
			 * Always exclude the following
			 */
			$alwaysExclude = ['.','..','.DS_Store'];
			if( in_array( $file->getFilename(), $alwaysExclude ) )
			{
				continue;
			}

			/*
			 * Apply inclusion rules; one is all it takes
			 */
			foreach( $this->inclusionRules as $rule )
			{
				$shouldInclude = call_user_func( $rule, $file );
				if( $shouldInclude == true )
				{
					$result[] = $file;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * @param	int		$method		self::METHOD_EXCLUDE or self::METHOD_INCLUDE
	 * @return	self
	 */
	public function setDefaultMethod( $method )
	{
		switch( $method )
		{
			case self::METHOD_EXCLUDE:
			case self::METHOD_INCLUDE:
				$this->defaultMethod = $method;
				break;
		};

		return $this;
	}
}
