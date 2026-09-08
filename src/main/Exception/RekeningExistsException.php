<?php

namespace RendyRobbani\Kodefikasi\Pemda\Exception;

class RekeningExistsException extends \RuntimeException
{
	const string SUMBER = "Sumber Dana";
	const string NERACA = "Neraca";
	const string LRA = "LRA";
	const string LO = "LO";
	const string LEVEL_1 = "Akun";
	const string LEVEL_2 = "Kelompok";
	const string LEVEL_3 = "Jenis";
	const string LEVEL_4 = "Objek";
	const string LEVEL_5 = "Rincian Objek";
	const string LEVEL_6 = "Subrincian Objek";

	public function __construct(string $nama, string $level, string $kode)
	{
		if ($nama !== self::SUMBER && strlen($kode) > 0) {
			$nama = match (substr($kode, 0, 1)) {
				"1" => "Aset",
				"2" => "Kewajiban",
				"3" => "Ekuitas",
				"4" => "Pendapatan",
				"5" => "Belanja",
				"6" => "Pembiayaan",
				"7" => "Pendapatan-LO",
				"8" => "Beban",
			};
		}
		parent::__construct("$level $nama dengan kode `$kode` sudah tersedia.");
	}
}