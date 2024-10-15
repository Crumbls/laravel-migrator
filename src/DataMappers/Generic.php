<?php

namespace Crumbls\Migrator\DataMappers;

use BadMethodCallException;
use ReflectionClass;

abstract class Generic
{
	protected array $children = [];

	public function __get($key)
	{
		$method = 'get'.ucFirst($key);

		if (method_exists($this, $method)) {
			return $this->$method();
		}

		dd($method);

		return $this->getAttribute($key);
	}
	public function __call($method, $parameters) : mixed {
		if (method_exists($this, $method)) {
			return call_user_func_array([$this, $method], $parameters);
		}

		throw new BadMethodCallException("Method {$method} does not exist.");
	}

	public static function create() : self {
		$class = get_called_class();
		return new $class();
	}

	public function addChild(Generic $child)
	{
		$this->children[] = $child;
		return $this;
	}

	public function removeChild(Generic $child)
	{
		$index = array_search($child, $this->children, true);
		if ($index !== false) {
			unset($this->children[$index]);
		}
		return $this;
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function getChild($name)
	{
		foreach ($this->children as $child) {
			if ($child->name === $name) {
				return $child;
			}
		}
		return null;
	}


	public function toArray()
	{
		$result = [];

		$reflection = new ReflectionClass($this);
		$properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

		foreach ($properties as $property) {
			$value = $property->getValue($this);

			if (is_array($value)) {
				$result[$property->getName()] = array_map(function ($item) {
					return $item instanceof self ? $item->toArray() : $item;
				}, $value);
			} else {
				$result[$property->getName()] = $value;
			}
		}

		$result['children'] = [];

		foreach ($this->children as $child) {
			$result['children'][] = $child->toArray();
		}


		return $result;
	}
}