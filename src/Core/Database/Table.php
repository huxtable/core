<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core\Database;

use Huxtable\Core\FileInfo;

class Table
{
	/**
	 * @var array
	 */
	protected $fields=[];

	/**
	 * @var array
	 */
	protected $foreignKeys=[];

	/**
	 * @var int
	 */
	protected $nextId=1;

	/**
	 * @var array
	 */
	protected $records=[];

	/**
	 * @var array
	 */
	protected $uniqueKeys=[];

	/**
	 * @param	Huxtable\Core\FileInfo	$source
	 */
	public function __construct( FileInfo $source )
	{
		$this->source = $source;
		$this->read();
	}

	/**
	 * Add an entry to the table's fieldset and write the results
	 *
	 * @param	string	$name	Name of field
	 * @return	self
	 */
	public function addField( $name )
	{
		if( !in_array( $name, $this->fields ) )
		{
			$this->fields[] = $name;
			$this->write();
		}

		return $this;
	}

	/**
	 * @return	self
	 */
	public function addForeignKey( $key, $table )
	{
		if( !isset( $this->foreignKeys[$key] ) )
		{
			$this->foreignKeys[$key] = $table;
			$this->write();
		}

		return $this;
	}

	/**
	 * @return	array		New record
	 */
	public function addRecord( array $data )
	{
		// Ensure no conflict with unique keys
		foreach( $this->uniqueKeys as $key )
		{
			foreach( $this->records as $record )
			{
				if( isset( $record[ $key ] ) && isset( $data[ $key ] ) )
				{
					if( $record[ $key ] == $data[ $key ] )
					{
						throw new UniqueKeyViolationException( "Violation of unique key constraint: '{$key}'" );
					}
				}
			}
		}

		$newRecord['id'] = $this->nextId;

		// Only insert defined fields
		foreach( $this->fields as $field )
		{
			$newRecord[$field] = isset( $data[$field] ) ? $data[$field] : null;
		}

		$this->records[] = $newRecord;
		$this->nextId++;

		$this->write();
		return $newRecord;
	}

	/**
	 * Add an entry to the table's unique key set and write the results
	 *
	 * @param	string	$name	Name of field
	 * @return	self
	 */
	public function addUniqueKey( $name )
	{
		if( !in_array( $name, $this->uniqueKeys ) )
		{
			$this->uniqueKeys[] = $name;
			$this->write();
		}

		return $this;
	}

	/**
	 * Read contents of source file into internal arrays
	 * Note: source file contents will overwrite any internal definitions
	 *
	 * @return	void
	 */
	protected function read()
	{
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
				if( isset( $contents['meta']['fields'] ) )
				{
					$this->fields = $contents['meta']['fields'];
				}

				if( isset( $contents['meta']['foreignKeys'] ) )
				{
					$this->foreignKeys = $contents['meta']['foreignKeys'];
				}

				if( isset( $contents['meta']['nextId'] ) )
				{
					$this->nextId = $contents['meta']['nextId'];
				}

				if( isset( $contents['meta']['uniqueKeys'] ) )
				{
					$this->uniqueKeys = $contents['meta']['uniqueKeys'];
				}
			}
		}
	}

	/**
	 * @return	boolean
	 */
	protected function write()
	{
		$contents = 
		[
			'meta' =>
			[
				'fields'		=> $this->fields,
				'foreignKeys'	=> $this->foreignKeys,
				'nextId'		=> $this->nextId,
				'uniqueKeys'	=> $this->uniqueKeys,
			],
			'records' => $this->records
		];

		return $this->source->putContents( json_encode( $contents, JSON_PRETTY_PRINT ) );
	}
}