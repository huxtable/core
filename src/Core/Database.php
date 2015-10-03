<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core;

class Database
{
	/**
	 * @var Huxtable\Core\FileInfo
	 */
	protected $source;

	/**
	 * @param	Huxtable\Core\FileInfo	$source
	 */
	public function __construct( FileInfo $source )
	{
		if( !$source->exists() )
		{
			throw new \Exception( "Data source not found '{$source}'" );
		}
		$this->source = $source;
	}

	/**
	 * @param	string	$name	Table name (ex., 'users')
	 * @return	Huxtable\Core\Database\Table
	 */
	public function table( $name )
	{
		$tableFile = $this->source->child( "{$name}.json" );
		return new Database\Table( $tableFile );
	}
}
