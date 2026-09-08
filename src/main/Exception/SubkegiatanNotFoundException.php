<?php

namespace RendyRobbani\Kodefikasi\Pemda\Exception;

class SubkegiatanNotFoundException extends \RuntimeException
{
	public function __construct(string $kode)
	{
		parent::__construct("Subkegiatan dengan kode `$kode` tidak tersedia.");
	}
}