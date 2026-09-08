<?php

namespace RendyRobbani\Kodefikasi\Pemda\Exception;

class ProgramNotFoundException extends \RuntimeException
{
	public function __construct(string $kode)
	{
		parent::__construct("Program dengan kode `$kode` tidak tersedia.");
	}
}