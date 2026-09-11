<?php

namespace RendyRobbani\Kodefikasi\Pemda\Service;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiKabupatenLogRepository;
use RendyRobbani\Kodefikasi\Pemda\Repository\FungsiKabupatenRepository;
use RendyRobbani\PHP\Connection\Connection;

class FungsiKabupatenServiceImpl extends AbstractFungsiService implements FungsiKabupatenService
{
	public function __construct(Connection                   $connection,
	                            FungsiKabupatenRepository    $repository,
	                            FungsiKabupatenLogRepository $logRepository)
	{
		$this->connection = $connection;
		$this->repository = $repository;
		$this->logRepository = $logRepository;
	}

	protected function mappingKepmendagriTahun2020(Row $row, string $excel_file, mixed $entity): void
	{
		if (pathinfo($excel_file, PATHINFO_BASENAME) === "E-00370-00383.xlsx") {
			switch ($row->getRowIndex()) {
				case 204:
					$entity->setNomorFungsi(4);
					$entity->setNomorSubfungsi(8);
					break;
				case 306:
					$entity->setNomorFungsi(6);
					$entity->setNomorSubfungsi(1);
					break;
			}
		}
	}
}