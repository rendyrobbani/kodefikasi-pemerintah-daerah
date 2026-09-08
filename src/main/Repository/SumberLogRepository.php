<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\SumberLogEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(SumberLogEntity::class)]
interface SumberLogRepository
{
	/**
	 * @return SumberLogEntity[]
	 */
	function findAll(): array;

	/**
	 * @param SumberLogEntity $entity
	 * @return SumberLogEntity
	 */
	function save(SumberLogEntity $entity): SumberLogEntity;
}