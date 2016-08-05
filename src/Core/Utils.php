<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core;

class Utils
{
	/**
	 * Return a random element from the given array
	 *
	 * @param	array	$array
	 * @return	mixed
	 */
	public static function randomElement( array $array )
	{
		$index = rand( 0, count( $array ) - 1 );
		return $array[$index];
	}

	/**
	 * @param	array	$array
	 * @return	array
	 */
	static public function reindexArray( array $array )
	{
		$newArray = [];

		foreach( $array as $item )
		{
			$newArray[] = $item;
		}

		return $newArray;
	}
}
