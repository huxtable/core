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
	protected $reservedKeys=['id'];

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
		if( in_array( $name, $this->reservedKeys ) )
		{
			return $this;
		}
		if( !in_array( $name, $this->fields ) )
		{
			$this->fields[] = $name;

			foreach( $this->records as &$record )
			{
				if( !isset( $record[$name] ) )
				{
					$record[$name] = null;
				}
			}

			$this->write();
		}

		return $this;
	}

	/**
	 * @param	string	$key	Local key name
	 * @param	string	$table	Name of foreign table
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
	 * @todo	Implement matching in arrays
	 * @param	array	$constraints	Array of key/value constraints (ex., "id" => 1)
	 * @return	self
	 */
	public function delete( array $constraints )
	{
		$count = count( $this->records );
		$matches = [];

		for( $i = 0; $i < $count; $i++ )
		{
			$isMatch = true;
			$record = $this->records[$i];

			foreach( $constraints as $key => $value )
			{
				$isMatch = $isMatch && (isset( $record[ $key ] ) && $record[ $key ] == $value);
			}

			if( $isMatch )
			{
				$matches[] = $i;
			}
		}

		if( count( $matches ) > 0 )
		{
			foreach( $matches as $match )
			{
				unset( $this->records[$match] );
			}

			// Re-index array to prevent mangled JSON
			$this->records = array_values( $this->records );

			$this->write();
		}

		return $this;
	}

	/**
	 * @todo	Implement matching in arrays
	 * @param	array	$constraints
	 * @return	array					Array of references to matching records
	 */
	protected function findRecords( array $constraints=[] )
	{
		$matches = [];

		foreach( $this->records as &$record )
		{
			if( count( $constraints ) > 0 )
			{
				$isMatch = true;

				foreach( $constraints as $key => $value )
				{
					$isMatch = $isMatch && array_key_exists( $key, $record );
					$isMatch = $isMatch && $record[ $key ] == $value;
				}

				if( $isMatch )
				{
					$matches[] = &$record;
				}
			}
			else
			{
				$matches[] = &$record;
			}
		}

		return $matches;
	}

	/**
	 * @param	array	$data	Array of keys & values
	 * @return	array			New record
	 */
	public function insert( array $data )
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
	 * Remove $name from the table's fieldset and write the results
	 *
	 * @param	string	$name	Name of field
	 * @return	self
	 */
	public function removeField( $name )
	{
		if( ($index = array_search( $name, $this->fields )) !== false )
		{
			unset( $this->fields[$index] );

			// Re-index array to prevent mangled JSON
			$this->fields = array_values( $this->fields );
		}

		foreach( $this->records as &$record )
		{
			if( isset( $record[$name] ) )
			{
				unset( $record[$name] );
			}
		}

		$this->write();
		return $this;
	}

	/**
	 * Remove $name from the table's fieldset and write the results
	 *
	 * @param	string	$name	Name of field
	 * @return	self
	 */
	public function removeUniqueKey( $name )
	{
		if( ($index = array_search( $name, $this->uniqueKeys )) !== false )
		{
			unset( $this->uniqueKeys[$index] );

			// Re-index array to prevent mangled JSON
			$this->uniqueKeys = array_values( $this->uniqueKeys );

			$this->write();
		}

		return $this;
	}

	/**
	 * @param	array	$constraints	Array of key/value constraints (ex., "id" => 1)
	 * @return	array
	 */
	public function select( array $constraints=[] )
	{
		$matches = [];

		// @todo	Implement matching in arrays

		foreach( $this->records as &$record )
		{
			if( count( $constraints ) > 0 )
			{
				$isMatch = true;

				foreach( $constraints as $key => $value )
				{
					$isMatch = $isMatch && (array_key_exists( $key, $record ) && $record[ $key ] == $value);
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
