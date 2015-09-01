<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core;

class HTTP
{
	/**
	 * @param	mixed	$request	Core\Request object or URL string
	 * @return	Huxtable\HTTP\Response
	 */
	public function get( $request )
	{
		return $this->request( $request );
	}

	/**
	 * @param	Huxtable\Core\HTTP\Request or string	$request
	 * @return	Huxtable\Core\HTTP\Response
	 */
	protected function request( $request, $method="GET" )
	{
		if( is_string( $request ) )
		{
			$request = new Request( $request );
		}

		$curl = curl_init();

		curl_setopt( $curl, CURLOPT_URL, $request->getURL() );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $request->getHeaders() );
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $method );

		$body = curl_exec( $curl );
		$info = curl_getinfo( $curl );
		$error = [
			'code' => curl_errno( $curl ),
			'message' => curl_error( $curl )
		];

		curl_close( $curl );

		return new HTTP\Response( $body, $info, $error );
	}

	/**
	 * @param	mixed	$request	Core\Request object or URL string
	 * @return	Huxtable\HTTP\Response
	 */
	public function post( $request )
	{
		return $this->request( $request, 'POST' );
	}

	/**
	 * @param	mixed	$request	Core\Request object or URL string
	 * @return	Huxtable\HTTP\Response
	 */
	public function put( $request )
	{
		return $this->request( $request, 'PUT' );
	}
}
