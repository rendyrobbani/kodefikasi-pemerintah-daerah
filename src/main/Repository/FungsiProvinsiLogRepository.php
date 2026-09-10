<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiProvinsiLogEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(FungsiProvinsiLogEntity::class)]
interface FungsiProvinsiLogRepository
{
	/**
	 * @return FungsiProvinsiLogEntity[]
	 */
	function findAll(): array;

	/**
	 * @param FungsiProvinsiLogEntity $entity
	 * @return FungsiProvinsiLogEntity
	 */
	function save(FungsiProvinsiLogEntity $entity): FungsiProvinsiLogEntity;
}