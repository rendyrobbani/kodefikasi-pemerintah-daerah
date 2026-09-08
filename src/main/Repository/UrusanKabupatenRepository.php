<?php

namespace RendyRobbani\Kodefikasi\Pemda\Repository;

use RendyRobbani\Kodefikasi\Pemda\Entity\UrusanKabupatenEntity;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
#[Repository(UrusanKabupatenEntity::class)]
interface UrusanKabupatenRepository
{
	/**
	 * @return UrusanKabupatenEntity[]
	 */
	function findAll(): array;

	/**
	 * @param UrusanKabupatenEntity $entity
	 * @return UrusanKabupatenEntity
	 */
	function save(UrusanKabupatenEntity $entity): UrusanKabupatenEntity;
}