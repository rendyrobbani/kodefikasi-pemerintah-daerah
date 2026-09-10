<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiProvinsiEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(FungsiProvinsiEntity::class)]
interface FungsiProvinsiRepository
{
	/**
	 * @return FungsiProvinsiEntity[]
	 */
	function findAll(): array;

	/**
	 * @param FungsiProvinsiEntity $entity
	 * @return FungsiProvinsiEntity
	 */
	function save(FungsiProvinsiEntity $entity): FungsiProvinsiEntity;
}