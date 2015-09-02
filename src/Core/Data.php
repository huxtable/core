<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core;

class Data
{
	/**
	 * @var array
	 */
	protected $meta=[];

	/**
	 * @var array
	 */
	protected $records=[];

	/**
	 * @var Huxtable\Core\FileInfo
	 */
	protected $source;

	/**
	 * @param	Huxtable\Core\FileInfo	$fileInfo
	 */
	public function __construct( FileInfo $fileInfo )
	{
		$this->source = $fileInfo;

		// Defaults
		$defaultMeta =
		[
			'nextId' => 1,
			'uniqueKeys' => [],
			'foreignKeys' => [],
		];

		if( $this->source->isFile() )
		{
			$json = $this->source->getContents();
			$contents = json_decode( $json, true );

			if( json_last_error() != JSON_ERROR_NONE )
			{
				// @todo	Error handling
			}

			if( isset( $contents['records'] ) )
			{
				$this->records = $contents['records'];
			}

			if( isset( $contents['meta'] ) )
			{
				$this->meta = $contents['meta'];
			}
			else
			{
				$this->meta = $defaultMeta;
			}
		}
		else
		{
			$this->meta = $defaultMeta;
		}
	}

	/**
	 * @return	array		New record
	 */
	public function addRecord( array $data )
	{
		if( isset( $this->meta['uniqueKeys'] ) )
		{
			// Ensure no conflict with unique keys
			foreach( $this->meta['uniqueKeys'] as $key )
			{
				foreach( $this->records as $record )
				{
					if( isset( $record[ $key ] ) && isset( $data[ $key ] ) )
					{
						if( $record[ $key ] == $data[ $key ] )
						{
							throw new Data\UniqueKeyViolationException( "Violation of unique key constraint: '{$key}'" );
						}
					}
				}
			}
		}

		$data['id'] = $this->meta['nextId'];
		$this->records[] = $data;

		$this->meta['nextId']++;
		$this->save();

		return $data;
	}

	/**
	 * @param	string	$source			Name of foreign source (ex., "people")
	 * @param	array	$constraints	Array of key/value constraints (ex., "id" => 1)
	 * @return	array
	 */
	protected function getForeignRecord( $source, array $constraints=[] )
	{
		// @todo	Cache foreign records instead of reading off disk every time

		$sourceForeign = $this->source->parent()->child( "{$source}.json" );
		$dataForeign = new Data( $sourceForeign );

		return $dataForeign->getRecords( $constraints );
	}

	/**
	 * @param	array	$constraints	Array of key/value constraints (ex., "id" => 1)
	 * @return	array
	 */
	public function getRecords( array $constraints=[] )
	{
		$matches = [];

		// @todo	Elevate this to a class property
		$localKeys = array_keys( $this->meta['foreignKeys'] );

		foreach( $this->records as &$record )
		{
			// Check foreign keys
			foreach( $localKeys as $localKey )
			{
				if( isset( $record[ $localKey ] ) )
				{
					$foreignSourceName = $this->meta['foreignKeys'][ $localKey ];

					if( is_array( $record[ $localKey ] ) )
					{
						$foreignRecordMatches = [];
						foreach( $record[ $localKey ] as $foreignValue )
						{
							$foreignConstraints = [ 'id' => $foreignValue ];
							$results = $this->getForeignRecord( $foreignSourceName, $foreignConstraints );

							if( count( $results ) > 0 )
							{
								$foreignRecordMatches[] = $results[0];
							}
						}

						if( count( $foreignRecordMatches ) > 0 )
						{
							$record[ $localKey ] = $foreignRecordMatches;
						}
					}
					else
					{
						$foreignConstraints = [ 'id' => $record[ $localKey ] ];
						$foreignRecordMatches = $this->getForeignRecord( $foreignSourceName, $foreignConstraints );

						if( count( $foreignRecordMatches ) == 1 )
						{
							$record[ $localKey ] = $foreignRecordMatches[0];
						}
					}
				}
			}

			if( count( $constraints ) > 0 )
			{
				$isMatch = true;

				foreach( $constraints as $key => $value )
				{
					$isMatch = $isMatch && (isset( $record[ $key ] ) && $record[ $key ] == $value);
				}
	
				if( $isMatch )
				{
					$matches[] = $record;
				}
			}
			else
			{
				$matches[] = $record;
			}
		}

		return $matches;
	}

	/**
	 * @return	boolean
	 */
	protected function save()
	{
		$contents = [
			'meta' => $this->meta,
			'records' => $this->records
		];

		return $this->source->putContents( json_encode( $contents, JSON_PRETTY_PRINT ) );
	}

	/**
	 * @param	array	$constraints	Array of key/value constraints (ex., "id" => 1)
	 * @param	array	$data			Array of keys and values
	 * @return	array
	 */
	public function updateRecords( array $constraints, array $data )
	{
		$updatedRecords = [];

		// @todo	Honor uniqueness constraints
		foreach( $this->records as &$record )
		{
			foreach( $constraints as $constraintKey => $constraintValue )
			{
				if( isset( $record[ $constraintKey ] ) && $record[ $constraintKey ] == $constraintValue )
				{
					foreach( $data as $dataKey => $dataValue )
					{
						$record[ $dataKey ] = $dataValue;
					}

					$updatedRecords[] = $record;
				}
			}
		}

		$this->save();
		return $updatedRecords;
	}
}
