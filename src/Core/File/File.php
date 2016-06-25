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
	 * @param	string	$filename
	 * @return	void
	 */
	public function __construct( $filename )
	{
		if( substr( $filename, 0, 1 ) == '~' )
		{
			$filename = getenv( 'HOME' ) . substr( $filename, 1 );
		}

		parent::__construct( $filename );
	}

	/**
	 * @param	Huxtable\Core\File\File		$target
	 * @return	void
	 */
	public function copyTo( File $target )
	{
		exec( "cp -r '{$this}' '{$target}'", $output, $code );
	}

	/**
	 * Create a file by touching it
	 *
	 * @return	void
	 */
	public function create()
	{
		$dirParent = $this->parent();
		if( !$dirParent->exists() )
		{
			$dirParent->create();
		}

		touch( $this->getPathname() );
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
	 * @param	Huxtable\Core\File\File		$target
	 * @return	void
	 */
	public function moveTo( File $target )
	{
		exec( "mv '{$this}' '{$target}'", $output, $code );
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
