<?php

namespace RendyRobbani\Kodefikasi\Pemda\Peraturan;

enum Peraturan
{
	case PERMENDAGRI_TAHUN_2019_NOMOR_90;
	case KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708;
	case KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889;
	case KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317;
	case KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406;
	case KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850;
	case KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861;

	public function referensi(): string
	{
		return match ($this) {
			self::PERMENDAGRI_TAHUN_2019_NOMOR_90 => "Peraturan Menteri Dalam Negeri Nomor 90 Tahun 2019",
			self::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708 => "Keputusan Menteri Dalam Negeri Nomor 050-3708 Tahun 2020",
			self::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889 => "Keputusan Menteri Dalam Negeri Nomor 050-5889 Tahun 2021",
			self::KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317 => "Keputusan Menteri Dalam Negeri Nomor 900.1.15.5-1317 Tahun 2023",
			self::KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406 => "Keputusan Menteri Dalam Negeri Nomor 900.1.15.5-3406 Tahun 2024",
			self::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850 => "Keputusan Menteri Dalam Negeri Nomor 900.1-2850 Tahun 2025",
			self::KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861 => "Keputusan Menteri Dalam Negeri Nomor 900.1-861 Tahun 2026",
		};
	}

	public function penetapan(): string
	{
		return match ($this) {
			self::PERMENDAGRI_TAHUN_2019_NOMOR_90 => "2019-10-18",
			self::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708 => "2020-10-05",
			self::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889 => "2021-12-27",
			self::KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317 => "2023-06-23",
			self::KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406 => "2024-08-27",
			self::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850 => "2025-07-31",
			self::KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861 => "2026-05-07",
		};
	}
}