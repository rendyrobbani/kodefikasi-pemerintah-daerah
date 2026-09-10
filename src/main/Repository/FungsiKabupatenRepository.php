<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\FungsiKabupatenEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(FungsiKabupatenEntity::class)]
interface FungsiKabupatenRepository
{
	/**
	 * @return FungsiKabupatenEntity[]
	 */
	function findAll(): array;

	/**
	 * @param FungsiKabupatenEntity $entity
	 * @return FungsiKabupatenEntity
	 */
	function save(FungsiKabupatenEntity $entity): FungsiKabupatenEntity;
}