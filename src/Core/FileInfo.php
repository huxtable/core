<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core;

class FileInfo extends \SplFileInfo
{
	/**
	 * @param	string	$name
	 * @return	Huxtable\FileInfo
	 */
	public function child( $name )
	{
		return new self( $this->getPathname() . '/' . $name );
	}

	/**
	 * If $this is a directory, return an array of Huxtable\FileInfo objects
	 *   representing each child file
	 *
	 * @param	array	$skip	Filenames to skip
	 * @return	array|false
	 */
	public function children( $skip=[] )
	{
		if( !$this->isDir() )
		{
			return false;
		}

		$children = [];
		$filenames = scandir( $this->getPathname() );

		foreach( $filenames as $filename )
		{
			if( $filename == '.' || $filename == '..' || in_array( $filename, $skip ) )
			{
				continue;
			}

			$children[] = new self( $this->getPathname() . '/' . $filename );
		}

		return $children;
	}

	/**
	 * @return	void
	 */
	public function copyTo( FileInfo $dest )
	{
		if( $this->isDir() )
		{
			exec( "cp -r '{$this}' '{$dest}'", $output, $code );
			return $code == 0;
		}

		copy( $this->getPathname(), $dest->getPathname() );
	}

	/**
	 * File-type agnostic deletion
	 *
	 * @return	void
	 */
	public function delete()
	{
		if( $this->isDir() )
		{
			return $this->rmdir( true );
		}

		return $this->unlink();
	}

	/**
	 * @return	boolean
	 */
	public function exists()
	{
		return file_exists( $this->getPathname() );
	}

	/**
	 * @return	string
	 */
	public function getContents()
	{
		if( !$this->isFile() )
		{
			throw new \Exception( "Could not read contents of '{$this->getPathname()}'" );
		}

		return file_get_contents( $this->getPathname() );
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
	 * @return	Huxtable\FileInfo
	 */
	public function parent()
	{
		return new self( dirname( $this->getPathname() ) );
	}

	/**
	 * @param	string	$data
	 * @return	int
	 */
	public function putContents( $data, $append=false )
	{
		$flags = $append ? FILE_APPEND : 0;
		return file_put_contents( $this->getPathname(), $data, $flags );
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

	/**
	 * @return	boolean
	 */
	public function unlink()
	{
		if( $this->isFile() || $this->isLink() )
		{
			return unlink( $this->getPathname() );
		}

		return false;
	}
}
