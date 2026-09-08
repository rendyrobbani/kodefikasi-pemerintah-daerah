<?php

namespace RendyRobbani\Kodefikasi\Pemda\Model;

class SplitPdfRange
{
	/**
	 * @param string $name
	 * @param int $from_page
	 * @param int $into_page
	 */
	public function __construct(public string $name,
	                            public int    $from_page,
	                            public int    $into_page)
	{
	}

	public function totalPages(): int
	{
		return $this->into_page - $this->from_page + 1;
	}

	public function fileName(int $pad_length = 4): string
	{
		return implode("-", [
				$this->name,
				str_pad($this->from_page, $pad_length, "0", STR_PAD_LEFT),
				str_pad($this->into_page, $pad_length, "0", STR_PAD_LEFT),
			]) . ".pdf";
	}
}