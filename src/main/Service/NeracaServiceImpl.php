<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Repository\NeracaLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\NeracaRepository;
use RendyRobbani\PHP\Connection\Connection;

class NeracaServiceImpl extends AbstractRekeningService implements NeracaService
{
	public function __construct(Connection          $connection,
	                            NeracaRepository    $repository,
	                            NeracaLogRepository $logRepository)
	{
		$this->connection = $connection;
		$this->repository = $repository;
		$this->logRepository = $logRepository;
		$this->type = self::NERACA;
	}

	protected function mappingPermendagriTahun2019(Row $row, string $excel_file, mixed $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "H-00393-00692.xlsx") {
			switch ($row->getRowIndex()) {
				case 672:
				case 674:
					$entity->setNomorRekening4(12);
					$entity->setNomorRekening5(1);
					break;
				case 782:
					$entity->setNomorRekening5(19);
					break;
				case 2278:
					$entity->setNomorRekening4(1);
					break;
				case 2582:
				case 2654:
				case 3176:
					$entity->setNomorRekening6(2);
					break;
				case 3132:
				case 3155:
				case 3158:
					$entity->setNomorRekening5(2);
					break;
				case 3137:
					$entity->setNomorRekening5(3);
					break;
			}
		}

		if (pathinfo($excel_file, PATHINFO_BASENAME) === "H-00693-00992.xlsx") {
			if ($row->getRowIndex() >= 1409 && $row->getRowIndex() < 1548) {
				$entity->setNomorRekening1(1);
				$entity->setNomorRekening2(3);
				$entity->setNomorRekening3(7);
				$entity->setNomorRekening4(3);
				$entity->setNomorRekening5(3);
			}
			if ($row->getRowIndex() >= 3185 && $row->getRowIndex() < 3803) {
				$entity->setNomorRekening1(2);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(2);
				$entity->setNomorRekening5(2);
			}
			if ($row->getRowIndex() >= 3803) {
				$entity->setNomorRekening1(2);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(2);
				$entity->setNomorRekening5(3);
			}

			switch ($row->getRowIndex()) {
				case 1124:
				case 1127:
				case 1130:
					$entity->setNomorRekening5(2);
					break;
				case 1429:
					$entity->setNomorRekening2(3);
					break;
				case 1696:
				case 1699:
					$entity->setNomorRekening4(1);
					break;
				case 1730:
					$entity->setNomorRekening6(3);
					break;
				case 1764:
				case 1768:
				case 1769:
				case 1771:
					$entity->setNomorRekening3(6);
					break;
			}
		}

		if (pathinfo($excel_file, PATHINFO_BASENAME) === "H-00993-01226.xlsx") {
			switch ($row->getRowIndex()) {
				case 465:
				case 1962:
				case 1965:
				case 1968:
				case 1971:
					$entity->setNomorRekening3(6);
					break;
				case 4028:
				case 4061:
				case 4078:
				case 4094:
				case 4102:
					$entity->setNomorRekening2(1);
					break;
				case 4179:
				case 4384:
					$entity->setNomorRekening6(2);
					break;
				case 4183:
					$entity->setNomorRekening3(7);
					break;
				case 4480:
				case 4483:
					$entity->setNomorRekening5(2);
					break;
			}
		}
	}

	protected function mappingKepmendagriTahun2020(Row $row, string $excel_file, mixed $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-00475-00774.xlsx") {
			switch ($row->getRowIndex()) {
				case 3796:
				case 3816:
					$entity->setNomorRekening5(2);
					break;
				case 3801:
					$entity->setNomorRekening5(3);
					break;
			}
		}
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-00775-01074.xlsx") {
			if ($row->getRowIndex() >= 2977 && $row->getRowIndex() < 3136) {
				$entity->setNomorRekening1(1);
				$entity->setNomorRekening2(3);
				$entity->setNomorRekening3(7);
				$entity->setNomorRekening4(3);
				$entity->setNomorRekening5(3);
			}
			switch ($row->getRowIndex()) {
				case 2652:
				case 2655:
				case 2658:
					$entity->setNomorRekening5(2);
					break;
				case 3297:
				case 3300:
					$entity->setNomorRekening4(1);
					break;
				case 3370:
				case 3376:
				case 3379:
					$entity->setNomorRekening3(6);
					break;
			}
		}
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-01075-01374.xlsx") {
			if ($row->getRowIndex() >= 5 && $row->getRowIndex() < 2945) {
				$entity->setNomorRekening1(2);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(2);
				$entity->setNomorRekening5(2);
			}
			if ($row->getRowIndex() >= 2945 && $row->getRowIndex() < 4882) {
				$entity->setNomorRekening1(2);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(2);
				$entity->setNomorRekening5(3);
			}
		}
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-01375-01608.xlsx") {
			if ($row->getRowIndex() >= 5 && $row->getRowIndex() < 884) {
				$entity->setNomorRekening1(2);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(2);
				$entity->setNomorRekening5(3);
			}
			if ($row->getRowIndex() >= 1517 && $row->getRowIndex() < 1672) {
				$entity->setNomorRekening1(2);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(7);
				$entity->setNomorRekening5(3);
			}
			switch ($row->getRowIndex()) {
				case 1068:
					$entity->setNomorRekening5(4);
					break;
				case 1258:
				case 1382:
				case 1585:
				case 1844:
				case 2097:
				case 2216:
				case 4223:
					$entity->setNomorRekening6(20);
					break;
				case 1414:
				case 1618:
				case 1879:
				case 2129:
				case 2248:
					$entity->setNomorRekening6(30);
					break;
				case 1654:
				case 1912:
				case 2284:
					$entity->setNomorRekening6(40);
					break;
				case 1723:
				case 1726:
				case 1729:
				case 1735:
					$entity->setNomorRekening3(6);
					break;
				case 2180:
				case 4112:
				case 4188:
					$entity->setNomorRekening6(10);
					break;
				case 1948:
				case 2319:
					$entity->setNomorRekening6(50);
					break;
				case 1984:
				case 2354:
					$entity->setNomorRekening6(60);
					break;
				case 2019:
				case 2387:
					$entity->setNomorRekening6(70);
					break;
				case 2423:
					$entity->setNomorRekening6(80);
					break;
				case 2459:
					$entity->setNomorRekening6(90);
					break;
				case 4154:
					$entity->setNomorRekening6(2);
					break;
				case 4440:
					$entity->setNomorRekening5(2);
					break;
			}
		}
	}

	protected function mappingKepmendagriTahun2021(Row $row, string $excel_file, mixed $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-00961-01260.xlsx") {
			if ($row->getRowIndex() >= 425 && $row->getRowIndex() < 3237) {
				$entity->setNomorRekening1(1);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(2);
				$entity->setNomorRekening5(5);
			}
			if ($row->getRowIndex() >= 5291 && $row->getRowIndex() < 8508) {
				$entity->setNomorRekening1(1);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(15);
				$entity->setNomorRekening5(16);
			}
			if ($row->getRowIndex() >= 8508) {
				$entity->setNomorRekening1(1);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(15);
				$entity->setNomorRekening5(17);
			}
		}
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-01561-01860.xlsx") {
			$entity->setNomorRekening1(1);
			$entity->setNomorRekening2(1);
			$entity->setNomorRekening3(10);
			$entity->setNomorRekening4(1);
			$entity->setNomorRekening5(4);
		}
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-03061-03360.xlsx") {
			if ($row->getRowIndex() >= 2225 && $row->getRowIndex() < 3771) {
				$entity->setNomorRekening1(2);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(5);
				$entity->setNomorRekening4(4);
				$entity->setNomorRekening5(2);
			}
			if ($row->getRowIndex() >= 5590 && $row->getRowIndex() < 8825) {
				$entity->setNomorRekening1(2);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(2);
				$entity->setNomorRekening5(2);
			}
			if ($row->getRowIndex() >= 8825) {
				$entity->setNomorRekening1(2);
				$entity->setNomorRekening2(1);
				$entity->setNomorRekening3(6);
				$entity->setNomorRekening4(2);
				$entity->setNomorRekening5(3);
			}
		}
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-03361-03660.xlsx") {
			$entity->setNomorRekening1(2);
			$entity->setNomorRekening2(1);
			$entity->setNomorRekening3($row->getRowIndex() < 4563 ? 6 : 7);
			if ($row->getRowIndex() >= 5 && $row->getRowIndex() < 1515) {
				$entity->setNomorRekening4(2);
				$entity->setNomorRekening5(3);
			}
		}
	}

	protected function arrayIdPermendagriTahun2019(mixed $entity): string
	{
		return match ($entity->id()) {
			"2-1-6-2-3-377",
			"2-1-6-2-3-724" => $entity->id() . $entity->nama(),
			default => $entity->id(),
		};
	}

	protected function arrayIdKepmendagriTahun2020(mixed $entity): string
	{
		return match ($entity->id()) {
			"2-1-6-2-3-377",
			"2-1-6-2-3-724" => $entity->id() . $entity->nama(),
			default => $entity->id(),
		};
	}
}