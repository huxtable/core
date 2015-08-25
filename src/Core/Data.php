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
				$this->meta = [
					'nextId' => 1,
					'uniqueKeys' => []
				];
			}
		}
		else
		{
			$this->meta = [
				'nextId' => 1,
				'uniqueKeys' => []
			];
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
							// @todo	throw new Core\Data\UniqueKeyViolationException
							return false;
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
	 * @param	array	$constraints	Array of key/value constraints (ex., "id" => 1)
	 * @return	Huxtable\Core\Data\Record
	 */
	public function getRecords( array $constraints=[] )
	{
		if( count( $constraints ) > 0 )
		{
			$matches = [];

			foreach( $this->records as $record )
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

			return $matches;
		}
		else
		{
			return $this->records;
		}
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
}
