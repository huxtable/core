<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core;

use Huxtable\Core\File;

class Config
{
	/**
	 * @var	array
	 */
	protected $data = [];

	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $source;

	/**
	 * @param	Huxtable\Core\File\File		$source
	 * @return	void
	 */
	public function __construct( File\File $source )
	{
		if( $source->exists() )
		{
			$json = $source->getContents();
			$this->data = json_decode( $json, true );
		}

		$this->source = $source;
	}

	/**
	 * @param	string	$domain
	 * @return	array
	 */
	public function getDomain( $domain )
	{
		if( isset( $this->data[$domain] ) )
		{
			return $this->data[$domain];
		}
	}

	/**
	 * @param	string	$domain
	 * @param	string	$key
	 * @return	mixed
	 */
	public function getValue( $domain, $key )
	{
		if( isset( $this->data[$domain][$key] ) )
		{
			return $this->data[$domain][$key];
		}
	}

	/**
	 * @param	string	$key
	 * @param	string	$value
	 * @return	void
	 */
	public function setValue( $domain, $key, $value )
	{
		$this->data[$domain][$key] = $value;
	}

	/**
	 * @return	void
	 */
	public function write()
	{
		$encodedData = json_encode( $this->data, JSON_PRETTY_PRINT );
		$this->source->putContents( $encodedData );
	}
}
