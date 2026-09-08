<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiLogEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(FungsiLogEntity::class)]
interface FungsiLogRepository
{
	/**
	 * @return FungsiLogEntity[]
	 */
	function findAll(): array;

	/**
	 * @param FungsiLogEntity $entity
	 * @return FungsiLogEntity
	 */
	function save(FungsiLogEntity $entity): FungsiLogEntity;
}