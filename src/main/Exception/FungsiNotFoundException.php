<?php

namespace RendyRobbani\Kodefikasi\Pemda\Exception;

class FungsiNotFoundException extends \RuntimeException
{
	public function __construct(string $kode)
	{
		parent::__construct("Fungsi dengan kode `$kode` tidak tersedia.");
	}
}