<?php

namespace RendyRobbani\Kodefikasi\Pemda\Entity;

use RendyRobbani\Kodefikasi\Pemda\Comparator\StringComparator;
use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\PHP\Persistence\Check;
use RendyRobbani\PHP\Persistence\Checks;
use RendyRobbani\PHP\Persistence\Column;
use RendyRobbani\PHP\Persistence\Entity;
use RendyRobbani\PHP\Persistence\Id;

#[Entity(table: "urusan_provinsi")]
#[Checks([
	new Check("id = concat_ws('-', nomor_urusan, nomor_bidang, nomor_program, nomor_kegiatan1, nomor_kegiatan2, nomor_subkegiatan)"),
	new Check("nomor_urusan is null or nomor_urusan >= 0"),
	new Check("nomor_bidang is null or nomor_bidang >= 0"),
	new Check("nomor_program is null or nomor_program > 0"),
	new Check("nomor_kegiatan1 is null or nomor_kegiatan1 > 0"),
	new Check("nomor_kegiatan2 is null or nomor_kegiatan2 > 0"),
	new Check("nomor_subkegiatan is null or nomor_subkegiatan > 0"),
])]
class UrusanProvinsiEntity
{
	#[Id]
	#[Column]
	protected string|null $id = null;

	#[Column]
	protected int|null $nomorUrusan = null;

	#[Column]
	protected int|null $nomorBidang = null;

	#[Column]
	protected int|null $nomorProgram = null;

	#[Column]
	protected int|null $nomorKegiatan1 = null;

	#[Column]
	protected int|null $nomorKegiatan2 = null;

	#[Column]
	protected int|null $nomorSubkegiatan = null;

	#[Column]
	protected string|null $nama = null;

	#[Column]
	protected string|null $keterangan = null;

	#[Column]
	protected string|null $kinerja = null;

	#[Column]
	protected string|null $indikator = null;

	#[Column]
	protected string|null $satuan = null;

	#[Column(type: "date")]
	protected string|null $createdAt = null;

	#[Column]
	protected string|null $createdBy = null;

	protected bool|null $isUpdated = false;

	#[Column(type: "date")]
	protected string|null $updatedAt = null;

	#[Column]
	protected string|null $updatedBy = null;

	#[Column]
	protected bool|null $isDeleted = false;

	#[Column(type: "date")]
	protected string|null $deletedAt = null;

	#[Column]
	protected string|null $deletedBy = null;

	public function isEqual(UrusanProvinsiEntity|null $entity): bool
	{
		if ($entity === null) return false;

		$compare = 0;
		$isEqual = 0;

		$compare++;
		if (StringComparator::isEqual(
			$this->id(),
			$entity->id(),
		)) $isEqual++;

		$compare++;
		if (StringComparator::isEqual(
			$this->nama(),
			$entity->nama(),
		)) $isEqual++;

		$compare++;
		if (StringComparator::isEqual(
			$this->keterangan(),
			$entity->keterangan(),
		)) $isEqual++;

		$compare++;
		if (StringComparator::isEqual(
			$this->kinerja(),
			$entity->kinerja(),
		)) $isEqual++;

		$compare++;
		if (StringComparator::isEqual(
			$this->indikator(),
			$entity->indikator(),
		)) $isEqual++;

		$compare++;
		if (StringComparator::isEqual(
			$this->satuan(),
			$entity->satuan(),
		)) $isEqual++;

		return $compare === $isEqual;
	}

	public function log(): UrusanProvinsiLogEntity
	{
		$logEntity = new UrusanProvinsiLogEntity();
		$logEntity->setIdReference($this->id());
		$logEntity->setNomorUrusan($this->nomorUrusan());
		$logEntity->setNomorBidang($this->nomorBidang());
		$logEntity->setNomorProgram($this->nomorProgram());
		$logEntity->setNomorKegiatan1($this->nomorKegiatan1());
		$logEntity->setNomorKegiatan2($this->nomorKegiatan2());
		$logEntity->setNomorSubkegiatan($this->nomorSubkegiatan());
		$logEntity->setNama($this->nama());
		$logEntity->setKeterangan($this->keterangan());
		$logEntity->setKinerja($this->kinerja());
		$logEntity->setIndikator($this->indikator());
		$logEntity->setSatuan($this->satuan());
		$logEntity->setCreatedAt($this->createdAt());
		$logEntity->setCreatedBy($this->createdBy());
		$logEntity->setUpdatedAt($this->updatedAt());
		$logEntity->setUpdatedBy($this->updatedBy());
		$logEntity->setIsDeleted($this->isDeleted());
		$logEntity->setDeletedAt($this->deletedAt());
		$logEntity->setDeletedBy($this->deletedBy());
		return $logEntity;
	}

	public function kodeUrusan(): string|null
	{
		if ($this->nomorUrusan === null) return null;
		return str_replace("0", "X", $this->nomorUrusan);
	}

	public function kodeBidang(): string|null
	{
		if ($this->nomorBidang === null) return null;
		return str_replace("00", "XX", str_pad($this->nomorBidang, 2, "0", STR_PAD_LEFT));
	}

	public function kodeProgram(): string|null
	{
		if ($this->nomorProgram === null) return null;
		return str_pad($this->nomorProgram, 2, "0", STR_PAD_LEFT);
	}

	public function kodeKegiatan(): string|null
	{
		if ($this->nomorKegiatan1 === null) return null;
		if ($this->nomorKegiatan2 === null) return null;
		return $this->nomorKegiatan1 . "." . str_pad($this->nomorKegiatan2, 2, "0", STR_PAD_LEFT);
	}

	public function kodeSubkegiatan(Peraturan $peraturan): string|null
	{
		if ($this->nomorSubkegiatan === null) return null;
		return str_pad($this->nomorSubkegiatan, match ($peraturan) {
			Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90,
			Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708,
			Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889 => 2,
			default => 4,
		}, "0", STR_PAD_LEFT);
	}

	public function kode(Peraturan $peraturan): string|null
	{
		$kode = [];
		for ($level = 1; $level <= 5; $level++) {
			$value = match ($level) {
				1 => $this->kodeUrusan(),
				2 => $this->kodeBidang(),
				3 => $this->kodeProgram(),
				4 => $this->kodeKegiatan(),
				5 => $this->kodeSubkegiatan($peraturan),
			};
			if ($value === null) break;
			$kode[] = $value;
		}
		return implode(".", $kode);
	}

	public function id(): string|null
	{
		$id = [];
		for ($level = 1; $level <= 6; $level++) {
			$value = match ($level) {
				1 => $this->nomorUrusan,
				2 => $this->nomorBidang,
				3 => $this->nomorProgram,
				4 => $this->nomorKegiatan1,
				5 => $this->nomorKegiatan2,
				6 => $this->nomorSubkegiatan,
			};
			if ($value === null) break;
			$id[] = $value;
		}
		return $this->id = implode("-", $id);
	}

	public function nomorUrusan(): int|null
	{
		return $this->nomorUrusan;
	}

	public function nomorBidang(): int|null
	{
		return $this->nomorBidang;
	}

	public function nomorProgram(): int|null
	{
		return $this->nomorProgram;
	}

	public function nomorKegiatan1(): int|null
	{
		return $this->nomorKegiatan1;
	}

	public function nomorKegiatan2(): int|null
	{
		return $this->nomorKegiatan2;
	}

	public function nomorSubkegiatan(): int|null
	{
		return $this->nomorSubkegiatan;
	}

	public function nama(): string|null
	{
		return $this->nama;
	}

	public function keterangan(): string|null
	{
		return $this->keterangan;
	}

	public function kinerja(): string|null
	{
		return $this->kinerja;
	}

	public function indikator(): string|null
	{
		return $this->indikator;
	}

	public function satuan(): string|null
	{
		return $this->satuan;
	}

	public function createdAt(): string|null
	{
		return $this->createdAt;
	}

	public function createdBy(): string|null
	{
		return $this->createdBy;
	}

	public function isUpdated(): bool|null
	{
		return $this->isUpdated === true;
	}

	public function updatedAt(): string|null
	{
		return $this->updatedAt;
	}

	public function updatedBy(): string|null
	{
		return $this->updatedBy;
	}

	public function isDeleted(): bool|null
	{
		return $this->isDeleted === true;
	}

	public function deletedAt(): string|null
	{
		return $this->deletedAt;
	}

	public function deletedBy(): string|null
	{
		return $this->deletedBy;
	}

	public function setNomorUrusan(int|null $nomorUrusan): void
	{
		$this->nomorUrusan = $nomorUrusan;
	}

	public function setNomorBidang(int|null $nomorBidang): void
	{
		$this->nomorBidang = $nomorBidang;
	}

	public function setNomorProgram(int|null $nomorProgram): void
	{
		$this->nomorProgram = $nomorProgram;
	}

	public function setNomorKegiatan1(int|null $nomorKegiatan1): void
	{
		$this->nomorKegiatan1 = $nomorKegiatan1;
	}

	public function setNomorKegiatan2(int|null $nomorKegiatan2): void
	{
		$this->nomorKegiatan2 = $nomorKegiatan2;
	}

	public function setNomorSubkegiatan(int|null $nomorSubkegiatan): void
	{
		$this->nomorSubkegiatan = $nomorSubkegiatan;
	}

	public function setNama(string|null $nama): void
	{
		$this->nama = $nama;
	}

	public function setKeterangan(string|null $keterangan): void
	{
		$this->keterangan = $keterangan;
	}

	public function setKinerja(string|null $kinerja): void
	{
		$this->kinerja = $kinerja;
	}

	public function setIndikator(string|null $indikator): void
	{
		$this->indikator = $indikator;
	}

	public function setSatuan(string|null $satuan): void
	{
		$this->satuan = $satuan;
	}

	public function setCreatedAt(string|null $createdAt): void
	{
		$this->createdAt = $createdAt;
	}

	public function setCreatedBy(string|null $createdBy): void
	{
		$this->createdBy = $createdBy;
	}

	public function setIsUpdated(bool|null $isUpdated): void
	{
		$this->isUpdated = $isUpdated === true;
	}

	public function setUpdatedAt(string|null $updatedAt): void
	{
		$this->updatedAt = $updatedAt;
	}

	public function setUpdatedBy(string|null $updatedBy): void
	{
		$this->updatedBy = $updatedBy;
	}

	public function setIsDeleted(bool|null $isDeleted): void
	{
		$this->isDeleted = $isDeleted === true;
	}

	public function setDeletedAt(string|null $deletedAt): void
	{
		$this->deletedAt = $deletedAt;
	}

	public function setDeletedBy(string|null $deletedBy): void
	{
		$this->deletedBy = $deletedBy;
	}
}