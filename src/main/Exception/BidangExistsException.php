<?php

namespace RendyRobbani\Kodefikasi\Pemda\Exception;

class BidangExistsException extends \RuntimeException
{
	public function __construct(string $kode)
	{
		parent::__construct("Bidang dengan kode `$kode` sudah tersedia.");
	}
}