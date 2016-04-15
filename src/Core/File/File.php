<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core\File;

class File extends \SplFileInfo
{
	const FILE = 1;
	const DIRECTORY = 2;

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
	 * Given an existing file or directory path, return a properly typed instance
	 *
	 * @param	string	$pathname
	 * @return	mixed
	 */
	final static public function getTypedInstance( $pathname )
	{
		$file = new File( $pathname );
		if( !$file->exists() )
		{
			return $file;
		}

		if( !$file->isDir() )
		{
			return $file;
		}

		$directory = new Directory( $pathname );
		return $directory;
	}

	/**
	 * @return	Huxtable\FileInfo
	 */
	public function parent()
	{
		return new Directory( dirname( $this->getPathname() ) );
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
	public function unlink()
	{
		if( $this->isFile() || $this->isLink() )
		{
			return unlink( $this->getPathname() );
		}

		return false;
	}
}
