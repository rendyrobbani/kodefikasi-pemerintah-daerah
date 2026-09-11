<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Repository\LoLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\LoRepository;
use RendyRobbani\PHP\Connection\Connection;

class LoServiceImpl extends AbstractRekeningService implements LoService
{
	public function __construct(Connection      $connection,
	                            LoRepository    $repository,
	                            LoLogRepository $logRepository)
	{
		$this->connection = $connection;
		$this->repository = $repository;
		$this->logRepository = $logRepository;
		$this->type = self::LO;
	}

	protected function mappingPermendagriTahun2019(Row $row, string $excel_file, mixed $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "J-01764-02063.xlsx") {
			switch ($row->getRowIndex()) {
				case 1083:
				case 1085:
				case 1088:
					$entity->setNomorRekening4(2);
					break;
				case 3926:
					$entity->setNomorRekening6(6);
					break;
			}
		}

		if (pathinfo($excel_file, PATHINFO_BASENAME) === "J-02064-02300.xlsx") {
			switch ($row->getRowIndex()) {
				case 1347:
				case 1603:
				case 1711:
				case 1866:
				case 1874:
				case 3479:
					$entity->setNomorRekening6(2);
					break;
				case 1615:
				case 1618:
				case 1621:
					$entity->setNomorRekening5(3);
					break;
				case 4250:
					$entity->setNomorRekening3(6);
					break;
			}
		}
	}

	protected function mappingKepmendagriTahun2020(Row $row, string $excel_file, mixed $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "K-02638-02937.xlsx") {
			switch ($row->getRowIndex()) {
				case 4808:
					$entity->setNomorRekening5(3);
					break;
				case 5099:
				case 5102:
				case 5105:
				case 5107:
				case 5112:
				case 5115:
				case 5118:
				case 5120:
				case 5123:
				case 5128:
				case 5131:
				case 5133:
				case 5136:
				case 5139:
					$entity->setNomorRekening1(8);
					break;
			}
		}
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "K-02938-03143.xlsx") {
			switch ($row->getRowIndex()) {
				case 399:
				case 401:
					$entity->setNomorRekening5(3);
					break;
				case 2919:
					$entity->setNomorRekening6(2);
					break;
			}
		}
	}
}