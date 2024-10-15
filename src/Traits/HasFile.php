<?php

namespace Crumbls\Migrator\Traits;

use Illuminate\Support\Facades\Storage;

trait HasFile {
	protected $diskname;
	protected $file;

	//Storage::disk('local')->put('example.txt', 'Contents');
	public function file(string $file) {
		$this->file = $file;
		return $this;
	}


	public function disk(string $disk) : self {
		$this->diskname = $disk;
		return $this;
	}

}