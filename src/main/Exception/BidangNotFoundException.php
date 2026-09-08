<?php

namespace RendyRobbani\Kodefikasi\Pemda\Exception;

class BidangNotFoundException extends \RuntimeException
{
	public function __construct(string $kode)
	{
		parent::__construct("Bidang dengan kode `$kode` tidak tersedia.");
	}
}