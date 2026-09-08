<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\SumberEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(SumberEntity::class)]
interface SumberRepository
{
	/**
	 * @return SumberEntity[]
	 */
	function findAll(): array;

	/**
	 * @param SumberEntity $entity
	 * @return SumberEntity
	 */
	function save(SumberEntity $entity): SumberEntity;
}