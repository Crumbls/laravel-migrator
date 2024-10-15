<?php

namespace Crumbls\Migrator\Drivers;

use Crumbls\Migrator\Contracts\DriverInterface;
use Crumbls\Migrator\Fluents\Author;
use Crumbls\Migrator\Fluents\Export;
use Crumbls\Migrator\Fluents\Generic;
use Crumbls\Migrator\Fluents\Post;
use Crumbls\Migrator\Fluents\PostMeta;
use Crumbls\Migrator\Fluents\PostType;
use Crumbls\Migrator\Fluents\Term;
use Crumbls\Migrator\Traits\HasFile;
use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Storage;
use XMLReader;


class SimpleXmlDriver implements DriverInterface {
	use HasFile;

	private Export $export;

	public function initialize(array $config) : self {
		return $this;
	}

	public function parse() {
		if (!isset($this->file) || !$this->file) {
			throw new \Exception('Define the filename using filename() first.');
		}
		if (!isset($this->diskname) ||!$this->diskname) {
			$this->diskname = 'local';
		}

		return $this->parseXml($this->file);
	}

	protected function parseXml( $file ) {

		$authors    = array();
		$posts      = array();
		$categories = array();
		$tags       = array();
		$terms      = array();

		$internal_errors = libxml_use_internal_errors( true );

		// TODO REWRITE
		$storage = Storage::disk($this->diskname);

		if (!$storage->exists($this->file)) {
			throw new \Exception('File does not exist.');
		}

		/**
		 * TODO: Localize the file, if necessary.
		 */
		$filename = $storage->path($this->file);


		/**
		 * Write a normalizer.
		 */

		$this->export = new Export();

		$reader = new XMLReader();

		$reader->open($filename);

		$temp = $this->iterate($reader);

		foreach($this->export->getPostTypes() as $postType) {
			$this->export->getPostType($postType);
		}
		dd($this->export, $temp);

		return array(
			'authors'       => $authors,
			'posts'         => $posts,
			'categories'    => $categories,
			'tags'          => $tags,
			'terms'         => $terms,
			'base_url'      => $base_url,
			'base_blog_url' => $base_blog_url,
			'version'       => $wxr_version,
		);
	}


	/**
	 * @return array
	 */
	private function extractGeneric(XMLReader $reader, ?Generic $fluent)
	{
		if (!$fluent) {
			dd($reader);
		}

		$depth = $reader->depth;
		$localName = $reader->localName;

		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == $localName) {
				break;
			} elseif ($reader->depth < $depth) {
				break;
			} else if ($reader->nodeType == XMLReader::SIGNIFICANT_WHITESPACE) {
				continue;
			} else if (in_array($reader->localName, [
				'creator',
				'link',
				'pubDate',
				'title'
			])) {
					$attr = $reader->localName;
					$cd = $reader->depth;

					$temp = '';
					while ($reader->read() && $reader->depth > $cd) {
						$temp .= $reader->value;
					}
					$fluent->$attr = trim($temp);
					continue;
				}
			foreach($this->getAttributes($reader) as $k => $v) {
				$fluent->$k = $v;
			}
			dd($fluent);
			$depth = $reader->depth;

			if (!$reader->isEmptyElement) {
				dd($reader);
				dd(__LINE__);
				$childs = $this->iterate($reader);
				$node['type'] = is_array($childs) ? 'element' : 'text';
				$node['value'] = $childs;
			}

			dd($reader->localName);
			switch ($reader->nodeType) {
				case XMLReader::ELEMENT:

					$node = null;
					$node_name = false;
					if ($reader->depth == 2) {
						if ($reader->name == 'item') {
							$this->export->addPost($this->extractPost($reader));
//							return $tree;
						} else if ($reader->name == 'wp:author') {
//							return $tree;
						} else if ($reader->name == 'wp:wxr_version') {
							/**
							 * Define the generator version.
							 */
							$value = $this->iterate($reader);
							$this->export->version($value);
//return $tree;
						}
					}

					if (!$node) {
						$node = array();
						$node['tag'] = $node_name = $reader->name;
						$node['attributes'] = $this->getAttributes($reader);
						$node['depth'] = $reader->depth;

						if (!$reader->isEmptyElement) {
							$childs = $this->iterate($reader);
							$node['type'] = is_array($childs) ? 'element' : 'text';
							$node['value'] = $childs;
						}
					}

					if (array_key_exists($node_name, $tree))
					{
						if (is_object($tree[$node_name])) {

							$temp = $tree[$node_name];
							unset($tree[$node_name]);

							$tree[$node_name][] = $temp;
							// TODO: Check and fix.

						} else if (!array_key_exists(0, $tree[$node_name]))
						{
							$temp = $tree[$node_name];
							unset($tree[$node_name]);
							$tree[$node_name][] = $temp;
						}

						$tree[$node_name][] = $node;
					}
					else
					{
						$tree[$node_name] = $node;
					}

				case XMLReader::TEXT:
					if (trim($reader->value))
					{
						$tree = trim($reader->value);
					}

				default:
					break;
			}
		}
		return $fluent;
	}

	protected function extractAuthor(XMLReader $reader)
	{
		$depth = $reader->depth;
		$localName = $reader->localName;
		$fluent = new Author();
		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				$field = $reader->localName;
				$reader->read();
				if ($reader->nodeType == XMLReader::TEXT) {
					$fluent->$field = $reader->value;
				}
			} elseif ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == $localName) {
				break;
			} elseif ($reader->depth <= $depth) {
				break;
			}
		}
		return $fluent;
	}

	protected function extractItem(XMLReader $reader) {
		$depth = $reader->depth;
		$localName = $reader->localName;
		$fluent = new Post();

		$post = [
			'postmeta' => [],
			'tags' => [],
			'categories' => [],
			'attributes' => []
		];

		while ($reader->read()) {
			if ($reader->depth <= $depth) {
				break;
			} else if ($reader->nodeType == XMLReader::ELEMENT) {
				$field = $reader->localName;
				$reader->read();
				if ($reader->nodeType == XMLReader::TEXT) {
					$value = $reader->value;
					if (strpos($field, 'postmeta_') === 0) {
						dd($field);
						$post['postmeta'][substr($field, 9)] = $value;
					} else if ($field == 'meta_key') {
						$fluent->addPostMeta($this->extractPostMeta($reader));
					} elseif (strpos($field, 'tag_') === 0) {
						$post['tags'][] = $value;
					} elseif (strpos($field, 'category_') === 0) {
						$post['categories'][] = $value;
					} else {
//						echo $field.'<br />';
						$fluent->$field = $value;
//						$post['attributes'][$field] = $value;
					}
				}
			} elseif ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == $localName) {
				break;
			}
		}

		return $fluent;

		dd($post, $fluent);

		dd($this->extractGeneric($reader, $fluent));
		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				$field = $reader->localName;
				$reader->read();
				if ($reader->nodeType == XMLReader::TEXT) {
					$fluent->$field = $reader->value;
				}
			} elseif ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == $localName) {
				break;
			} elseif ($reader->depth < $depth) {
				break;
			}
		}
		dd($fluent);

		dd($reader);
		$k = 'wp:postmeta';
		$node->$k = [];

		foreach((array)$this->getAttributes($reader) as $k => $v) {
			$node->$k = $v;
		}

		if (!$reader->isEmptyElement) {
			$children = $this->iterate($reader);
			$node->type = is_array($children) ? 'element' : 'text';
			$node->value = $children;
		}

		return $node;
	}

	protected function extractCategory(XMLReader $reader) {
		$fluent = new Term();
		$fluent->type = 'category';
		return $this->extractGeneric($reader, $fluent, $reader->localName);
	}

	protected function extractPostType(XMLReader $reader)
	{
		return $this->extractGeneric($reader, new PostType(), 'wp:post_type');
	}

	private function extractPostMeta(XMLReader $reader) {
		$depth = $reader->depth;
		$localName = $reader->localName;
		$fluent = new PostMeta();

		$post = [
			'postmeta' => [],
			'tags' => [],
			'categories' => [],
			'attributes' => []
		];

		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				$field = $reader->localName;
				$reader->read();

				if ($field == 'meta_key' || $field == 'meta_value') {
					$value = $reader->value;
					$fluent->$field = $value;
				}
			} elseif ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == 'item') {
				break;
			}
		}
		return $fluent;
	}

	public function splitNamespace($string)
	{
		static $cache = array();
		if (!isset($cache[$string]))
		{
			if ($pos = strrpos($string, ':'))
			{
				$namespace = substr($string, 0, $pos);
				$local_name = substr($string, $pos + 1);
				$cache[$string] = array($namespace, $local_name);
			} else {
				$cache[$string] = array('', $string);
			}
		}
		return $cache[$string];
	}
	public function tag_open($parser, $tag, $attributes)
	{
//		dd(explode($tag, ':', 2));
//		dd(explode(':', $tag, 2));
//		list($this->namespace[], $this->element[]) = explode(':', $tag, 2);

		$attribs = array();
		foreach ($attributes as $name => $value)
		{
			list($attrib_namespace, $attribute) = $this->splitNamespace($name);
			if ($attrib_namespace) {
				$attribs[$attrib_namespace][$attribute] = $value;
			} else {
				$attribs[$attribute] = $value;
			}
		}
		if (false)
		{
			$this->datas[] =& $this->data;
			dd($this->data);
			$this->data =& $this->data['child'][end($this->namespace)][end($this->element)][];
			$this->data = array('data' => '', 'attribs' => $attribs, 'xml_base' => end($this->xml_base), 'xml_base_explicit' => end($this->xml_base_explicit), 'xml_lang' => end($this->xml_lang));
			if ((end($this->namespace) === SIMPLEPIE_NAMESPACE_ATOM_03 && in_array(end($this->element), array('title', 'tagline', 'copyright', 'info', 'summary', 'content')) && isset($attribs['']['mode']) && $attribs['']['mode'] === 'xml')
				|| (end($this->namespace) === SIMPLEPIE_NAMESPACE_ATOM_10 && in_array(end($this->element), array('rights', 'subtitle', 'summary', 'info', 'title', 'content')) && isset($attribs['']['type']) && $attribs['']['type'] === 'xhtml')
				|| (end($this->namespace) === SIMPLEPIE_NAMESPACE_RSS_20 && in_array(end($this->element), array('title')))
				|| (end($this->namespace) === SIMPLEPIE_NAMESPACE_RSS_090 && in_array(end($this->element), array('title')))
				|| (end($this->namespace) === SIMPLEPIE_NAMESPACE_RSS_10 && in_array(end($this->element), array('title'))))
			{
				$this->current_xhtml_construct = 0;
			}
		}
	}


	public function tag_close( $parser, $tag ) {
		switch ( $tag ) {
			case 'wp:comment':
				unset( $this->sub_data['key'], $this->sub_data['value'] ); // remove meta sub_data
				if ( ! empty( $this->sub_data ) ) {
					$this->data['comments'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'wp:commentmeta':
				$this->sub_data['commentmeta'][] = array(
					'key'   => $this->sub_data['key'],
					'value' => $this->sub_data['value'],
				);
				break;
			case 'category':
				if ( ! empty( $this->sub_data ) ) {
					$this->sub_data['name'] = $this->cdata;
					$this->data['terms'][]  = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'wp:postmeta':
				if ( ! empty( $this->sub_data ) ) {
					$this->data['postmeta'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'item':
				$this->posts[] = $this->data;
				$this->data    = false;
				break;
			case 'wp:category':
			case 'wp:tag':
			case 'wp:term':
				$n = substr( $tag, 3 );
				array_push( $this->$n, $this->data );
				$this->data = false;
				break;
			case 'wp:termmeta':
				if ( ! empty( $this->sub_data ) ) {
					$this->data['termmeta'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'wp:author':
				if ( ! empty( $this->data['author_login'] ) ) {
					$this->authors[ $this->data['author_login'] ] = $this->data;
				}
				$this->data = false;
				break;
			case 'wp:base_site_url':
				$this->base_url = $this->cdata;
				if ( ! isset( $this->base_blog_url ) ) {
					$this->base_blog_url = $this->cdata;
				}
				break;
			case 'wp:base_blog_url':
				$this->base_blog_url = $this->cdata;
				break;
			case 'wp:wxr_version':
				$this->wxr_version = $this->cdata;
				break;

			default:
				dd($tag);
				if (false &&  $this->in_sub_tag ) {
					if ( false === $this->sub_data ) {
						$this->sub_data = array();
					}

					$this->sub_data[ $this->in_sub_tag ] = ! empty( $this->cdata ) ? $this->cdata : '';
					$this->in_sub_tag                    = false;
				} elseif ( $this->in_tag ) {
					if ( false === $this->data ) {
						$this->data = array();
					}

					$this->data[ $this->in_tag ] = ! empty( $this->cdata ) ? $this->cdata : '';
					$this->in_tag                = false;
				}
		}

		$this->cdata = false;
	}


	/**
	 * XMLReader properties which can be accessed
	 *
	 * @var array
	 */
	private $xml_property = array(
		'attributeCount', 'baseURI', 'depth', 'hasAttributes', 'hasValue', 'isDefault',
		'isEmptyElement', 'localName', 'name', 'namespaceURI', 'nodeType', 'prefix',
		'value', 'xmlLang'
	);

	/**
	 * XML elements
	 *
	 * @var array
	 */
	private $_elements;

	/**
	 * File Path
	 *
	 * @var string
	 */

	/**
	 * Returns attributes of processing node
	 *
	 * @return type
	 */
	private function getAttributes(XMLReader $reader)
	{
		$attrib = array();
		while ($reader->moveToNextAttribute())
		{
			$attrib[$reader->name] = $reader->value;
		}

		return empty($attrib) ? FALSE : $attrib;
	}

	private function iterate(XMLReader $reader) {
		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				if ($reader->depth == 2) {
					$nodeName = $reader->localName;
					if ($nodeName == 'author') {
						$this->export->addAuthor($this->extractAuthor($reader));
					} else if ($nodeName == 'item') {
						/**
						 * TODO: In the future, we want to give the option for large xml files to be split into individual files.
						 * Some way to group by post type to find similar fields.
						 */
						$this->export->addPost($this->extractItem($reader));
					} else if (in_array($nodeName, [
						'base_blog_url',
						'base_blog_url'
					])) {
						$depth = $reader->depth;
						$value = '';
						while ($reader->read()) {
							if ($reader->nodeType == XMLReader::CDATA) {
								$value .= $reader->value;
							}
							if ($reader->depth <= $depth) {
								break;
							}
							echo $reader->localName;
							if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->name == $nodeName) {
								break;
							}
						}
						$this->export->$nodeName = $value;
						continue;
					} else if (in_array($nodeName, [
						'title',
						'link',
						'description',
						'language',
//						'base_site_url',
//						'base_blog_url',
						'generator',
						'wxr_version',
						'pubDate'
					])) {
						/**
						 * TODO: Extract and insert.
						 */
						if (!$reader->isEmptyElement) {
							$depth = $reader->depth;
							$localName = $reader->localName;
							$value = '';
							while ($reader->read()) {
								$value .= $reader->value;
								if ($reader->nodeType == XMLReader::ELEMENT) {
									$field = $reader->localName;
									$reader->read();
									if ($reader->nodeType == XMLReader::TEXT) {
										$value .= ' '.$reader->value;
									}
								} elseif ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == $localName) {
									break;
								} elseif ($reader->depth <= $depth) {
									break;
								}
							}
							$this->export->$localName = $value;
							continue;
						} else {

							$tree = trim($reader->value);
							echo $tree;
						}
						echo $reader->localName . ' aa ' . $reader->depth . '<br />';
						continue;
					} else {
						echo $reader->localName . ' ' . $reader->depth . '<br />';
						continue;
					}
				} else {
					continue;
					$nodeName = $reader->localName;
					echo $nodeName.' - '.$reader->depth.'<br />';
					continue;
				}
			}
		}
//		dd($reader->depth);
	}

	/**
	 * Process XML file to parse
	 *
	 * @deprecated
	 * @return array
	 */
	private function iterateOld(XMLReader $reader)
	{
		$tree = array();

		while ($reader->read())
		{
			switch ($reader->nodeType)
			{
				case XMLReader::END_ELEMENT:
					return $tree;
				case XMLReader::ELEMENT:

					$node = null;
					$node_name = false;
					if ($reader->depth == 2) {
						if ($reader->name == 'item') {
							$this->export->addPost($this->extractPost($reader));
//							return $tree;
						} else if ($reader->name == 'wp:author') {
//							return $tree;
						} else if ($reader->name == 'wp:wxr_version') {
							/**
							 * Define the generator version.
							 */
							$value = $this->iterate($reader);
							$this->export->version($value);
//return $tree;
						}
					}

					if (!$node) {
						$node = array();
						$node['tag'] = $node_name = $reader->name;
						$node['attributes'] = $this->getAttributes($reader);
						$node['depth'] = $reader->depth;

						if (!$reader->isEmptyElement) {
							$childs = $this->iterate($reader);
							$node['type'] = is_array($childs) ? 'element' : 'text';
							$node['value'] = $childs;
						}
					}

					if (array_key_exists($node_name, $tree))
					{
						if (is_object($tree[$node_name])) {

							$temp = $tree[$node_name];
							unset($tree[$node_name]);

							$tree[$node_name][] = $temp;
							// TODO: Check and fix.

						} else if (!array_key_exists(0, $tree[$node_name]))
						{
							$temp = $tree[$node_name];
							unset($tree[$node_name]);
							$tree[$node_name][] = $temp;
						}

						$tree[$node_name][] = $node;
					}
					else
					{
						$tree[$node_name] = $node;
					}

				case XMLReader::TEXT:
					if (trim($reader->value))
					{
						$tree = trim($reader->value);
					}

				default:
					break;
			}
		}
		return $tree;
	}

	/**
	 * XML elements as object
	 *
	 * @return object
	 */
	public function toObject()
	{
		return json_decode(json_encode($this->_elements));
	}

	/**
	 * XML elements as array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->_elements;
	}

	/**
	 *
	 * @param   string  $name
	 * @param   array   $arguments
	 * @return  mixed
	 */
	public function __call($name, $arguments)
	{
		if (method_exists($reader, $name))
		{
			return call_user_func_array(array($reader, $name), $arguments);
		}

		trigger_error($name . ' method does not exist in class ' . __CLASS__);
	}

	/**
	 *
	 * @param   string  $name
	 * @return  string
	 */
	public function __get($name)
	{
		if (in_array($name, $reader_property))
		{
			return $reader->{$name};
		}

		trigger_error($name . ' does not exist in class ' . __CLASS__);
	}
}