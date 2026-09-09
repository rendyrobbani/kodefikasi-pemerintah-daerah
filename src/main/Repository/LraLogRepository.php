<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\LraLogEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(LraLogEntity::class)]
interface LraLogRepository
{
	/**
	 * @return LraLogEntity[]
	 */
	function findAll(): array;

	/**
	 * @param LraLogEntity $entity
	 * @return LraLogEntity
	 */
	function save(LraLogEntity $entity): LraLogEntity;
}