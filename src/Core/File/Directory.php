<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core\File;

class Directory extends File
{
	/**
	 * @param	string	$filename
	 * @return	void
	 */
	public function __construct( $filename )
	{
		parent::__construct( $filename );

		// Don't extend all these great directory features to lowly files
		if( $this->isFile() )
		{
			throw new \Exception( "Invalid directory '{$filename}'" );
		}
	}

	/**
	 * @param	string	$name
	 * @return	Huxtable\Core\File\File
	 */
	public function child( $name )
	{
		$filenameChild = $this->getPathname() . '/' . $name;
		$child = new File( $filenameChild );

		return $child;
	}

	/**
	 * @param	string	$name
	 * @return	Huxtable\Core\File\Directory
	 */
	public function childDir( $name )
	{
		$filenameChild = $this->getPathname() . '/' . $name;
		$child = new Directory( $filenameChild );

		return $child;
	}

	/**
	 * If $this is a directory, return an array of Huxtable\Core\File\File objects
	 *   representing each child file
	 *
	 * Filter files by inclusion or exclusion using an instance of Filter
	 *
	 * @param	Huxtable\Core\File\Filter	$filter
	 * @return	array
	 */
	public function children( Filter $filter=null )
	{
		$children = [];
		$filenames = scandir( $this->getPathname() );

		foreach( $filenames as $filename )
		{
			$child = $this->child( $filename );
			if( $child->isDir() )
			{
				$child = $this->childDir( $filename );
			}

			$children[] = $child;
		}

		$filter = is_null( $filter ) ? new Filter() : $filter;
		$filteredChildren = $filter->filterFiles( $children );

		return $filteredChildren;
	}

	/**
	 * Create a directory
	 *
	 * @return	void
	 */
	public function create()
	{
		$this->mkdir( 0777, true );
	}

	/**
	 * File-type agnostic deletion
	 *   Overrides parent method
	 *
	 * @return	void
	 */
	public function delete()
	{
		return $this->rmdir( true );
	}

	/**
	 * @param	int			$mode
	 * @param	boolean		$recursive
	 * @return	boolean
	 */
	public function mkdir( $mode=0777, $recursive=false )
	{
		// Already exists
		if( $this->isDir() || $this->isFile() )
		{
			return false;
		}

		return mkdir( $this->getPathname(), $mode, $recursive );
	}

	/**
	 * @return	boolean
	 */
	public function rmdir( $recursive=false )
	{
		if( $recursive )
		{
			exec( 'rm -r "' . $this->getPathname() . '"', $output, $code );
			return $code == 0;
		}

		return rmdir( $this->getPathname() );
	}
}
