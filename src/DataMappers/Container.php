<?php

namespace Crumbls\Migrator\DataMappers;

class Container extends Generic {
	public function toArray() : array {
		dd(__METHOD__);
		return [];
	}

	/**
	 * Set the source table.
	 * @param Table $value
	 * @return $this
	 */
	public function source(Table $value) : self {
		$this->children['source'] = $value;
		return $this;
	}

	/**
	 * Return the source table.
	 * @return Table|null
	 */
	public function getSource() : Table|null {
		return $this->children['source'] ?? null;
	}


	/**
	 * Set the source table.
	 * @param Table $value
	 * @return $this
	 */
	public function destination(Table $value) : self {
		$this->children['destination'] = $value;
		return $this;
	}

	/**
	 * Return the destination table.
	 * @return Table|null
	 */
	public function getDestination() : Table|null {
		return $this->children['destination'] ?? null;
	}
}