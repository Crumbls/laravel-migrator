<?php

namespace Crumbls\Migrator\DataMappers;

class Column extends Generic {
	private string $name;
	private int $order = 1000000000;
	private mixed $default;

	private bool $unsigned = false;
	private bool $nullable = false;

	private string $type;
	private string $typeDetailed;
	private int|null $characterLengthMax;
	private int|null $numericLengthMax;
	private int|null $numericScale;
	private int|null $octetLength;
private int|null $characterMaxLength;
private int|null $datetimePrecision;

	/**
	 * @param string $value
	 * @return $this
	 */
	public function name(string $value) : self {
		$this->name = $value;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName() : string  {
		return $this->name;
	}

	/**
	 * @param int $value
	 * @return $this
	 */
	public function order(int $value) : self {
		$this->order = $value;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getOrder() : int {
		return $this->order;
	}


	/**
	 * @param int $value
	 * @return $this
	 */
	public function default(mixed $value) : self {
		if (is_numeric($value) && !is_null($value)) {
			$value = (int)$value;
		}
		$this->default = $value;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDefault() : mixed {
		return $this->default;
	}

	/**
	 * @param int $value
	 * @return $this
	 */
	public function type(mixed $value) : self {
		$this->type = $value;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getType() : mixed {
		return $this->type;
	}


	/**
	 * @param int $value
	 * @return $this
	 */
	public function typeDetailed(mixed $value) : self {
		$this->typeDetailed = $value;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTypeDetailed() : mixed {
		return $this->typeDetailed;
	}

	/**
	 * @param int $value
	 * @return $this
	 */
	public function characterMaxLength(mixed $value) : self {
		$this->characterMaxLength = $value;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCharacterMaxLength() : mixed {
		return $this->characterMaxLength;
	}

	/**
	 * @param bool $value
	 * @return $this
	 */
	public function nullable(bool $value) : self {
		$this->nullable = $value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getNullable() : bool {
		return $this->nullable;
	}

	/**
	 * @param int|null $value
	 * @return $this
	 */
	public function numericPrecision(int|null $value) : self {
		$this->numericLengthMax = $value;
		return $this;
	}


	/**
	 * @return int|null
	 */
	public function getNumericPrecision() : int|null {
		return $this->numericLengthMax;
	}

	/**
	 * @param int|null $value
	 * @return $this
	 */
	public function numericScale(int|null $value) : self {
		$this->numericScale = $value;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getNumericScale() : int|null {
		return $this->numericScale;
	}


	/**
	 * @param bool $value
	 * @return $this
	 */
	public function unsigned(bool $value) : self {
		$this->unsigned = $value;
		return $this;
	}

	public function getUnsigned() : bool {
		return $this->unsigned;
	}

	/**
	 * Test and clean everything below.
	 */

	public function columnDefault(mixed $value) : self {
		$this->default = $value;
		return $this;
	}


	public function characterMaximumLength(int|null $value) : self {
		$this->characterLengthMax = $value;
		return $this;
	}
	public function characterOctetLength(int|null $value) : self {
		$this->octetLength = $value;
		return $this;
	}

	public function datetimePrecision(int|null $value) : self {
		$this->datetimePrecision = $value;
		return $this;
	}


	public function columnKey(string|null $value) : self {
		$this->key = $value;
		return $this;
	}

	public function columnComment(string $value) : self {
		$this->comment = $value;
		return $this;
	}

	public function extra(string|null $value) : self {
		$this->extra = $value;
		return $this;
	}


	public function toArray()
	{
		$result = [
			'fromName' => $this->fromName,
			'toName' => $this->toName,
			'order' => $this->order,
			'nullable' => $this->nullable,
			'from_type' => $this->getType(),
			'from_type_extended' => $this->fromTypeExtended,
			'to_type' => $this->getLaravelType($this->fromTypeExtended),
			'unsigned' => $this->unsigned,
			'character_length_max' => $this->characterLengthMax,
			'numeric_length_max' => $this->numericLengthMax,
			'numeric_scale' => $this->numericScale,
			'octet_length' => $this->octetLength,
		];

		return $result;
	}

}