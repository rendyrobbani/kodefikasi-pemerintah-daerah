<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Repository\LraLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\LraRepository;
use RendyRobbani\PHP\Connection\Connection;

class LraServiceImpl extends AbstractRekeningService implements LraService
{
	public function __construct(Connection       $connection,
	                            LraRepository    $repository,
	                            LraLogRepository $logRepository)
	{
		$this->connection = $connection;
		$this->repository = $repository;
		$this->logRepository = $logRepository;
		$this->type = self::LRA;
	}

	protected function mappingPermendagriTahun2019(Row $row, string $excel_file, mixed $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-01226-01525.xlsx") {
			switch ($row->getRowIndex()) {
				case 1383:
					$entity->setNomorRekening4(4);
					break;
				case 6893:
					$entity->setNomorRekening5(3);
					break;
				case 6896:
					$entity->setNomorRekening5(3);
					$entity->setNomorRekening6(2);
					break;
				case 7003:
					$entity->setNomorRekening6(2);
					break;
			}
		}

		if (pathinfo($excel_file, PATHINFO_BASENAME) === "I-01526-01763.xlsx") {
			switch ($row->getRowIndex()) {
				case 2723:
				case 2732:
					$entity->setNomorRekening1(5);
					$entity->setNomorRekening2(2);
					break;
				case 3696:
				case 3764:
					$entity->setNomorRekening6(2);
					break;
				case 3929:
					$entity->setNomorRekening4(1);
					break;
				case 4312:
					$entity->setNomorRekening4(7);
					break;
			}
		}
	}

	protected function mappingKepmendagriTahun2020(Row $row, string $excel_file, mixed $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "J-01908-02207.xlsx") {
			switch ($row->getRowIndex()) {
				case 552:
					$entity->setNomorRekening6(122);
					break;
			}
		}
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "J-02208-02338.xlsx") {
			switch ($row->getRowIndex()) {
				case 686:
					$entity->setNomorRekening1(5);
					$entity->setNomorRekening2(2);
					break;
			}
		}
	}
}