<?php

namespace RendyRobbani\Kodefikasi\Pemda\Exception;

class FungsiExistsException extends \RuntimeException
{
	public function __construct(string $kode)
	{
		parent::__construct("Fungsi dengan kode `$kode` sudah tersedia.");
	}
}