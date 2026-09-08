<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(FungsiEntity::class)]
interface FungsiRepository
{
	/**
	 * @return FungsiEntity[]
	 */
	function findAll(): array;

	/**
	 * @param FungsiEntity $entity
	 * @return FungsiEntity
	 */
	function save(FungsiEntity $entity): FungsiEntity;
}