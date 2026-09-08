<?php

namespace RendyRobbani\Kodefikasi\Pemda\Exception;

class KegiatanExistsException extends \RuntimeException
{
	public function __construct(string $kode)
	{
		parent::__construct("Kegiatan dengan kode `$kode` sudah tersedia.");
	}
}