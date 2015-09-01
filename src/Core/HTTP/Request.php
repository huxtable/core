<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core\HTTP;

class Request
{
	/**
	 * @var array
	 */
	protected $headers=[];

	/**
	 * @var array
	 */
	protected $parameters=[];

	/**
	 * @var string
	 */
	protected $url='';

	/**
	 * @param	string	$url
	 */
	public function __construct( $url )
	{
		$this->url = $url;
	}

	/**
	 * Add query string parameter
	 *
	 * @param	string	$key
	 * @param	string	$value
	 * @return	self	$this	For chaining
	 */
	public function addParameter( $key, $value )
	{
		$this->parameters[ $key ] = $value;
		return $this;
	}

	/**
	 * Add HTTP header
	 *
	 * @param	string	$key
	 * @param	string	$value
	 * @return	self	$this	For chaining
	 */
	public function addHeader( $key, $value )
	{
		$this->headers[ $key ] = $value;
		return $this;
	}

	/**
	 * @return	array
	 */
	public function getHeaders()
	{
		$headerStrings = [];

		foreach( $this->headers as $key => $value )
		{
			$headerStrings[] = "{$key}: {$value}";
		}

		return $headerStrings;
	}

	/**
	 * @return	array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * @return	string
	 */
	public function getURL()
	{
		$url = $this->url;

		$queryString = http_build_query( $this->parameters );
		if( strlen( $queryString ) > 0 )
		{
			$url .= "?{$queryString}";
		}

		return $url;
	}
}
