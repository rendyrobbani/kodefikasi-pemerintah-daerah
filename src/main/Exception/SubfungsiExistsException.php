<?php

namespace RendyRobbani\Kodefikasi\Pemda\Exception;

class SubfungsiExistsException extends \RuntimeException
{
	public function __construct(string $kode)
	{
		parent::__construct("Subfungsi dengan kode `$kode` sudah tersedia.");
	}
}