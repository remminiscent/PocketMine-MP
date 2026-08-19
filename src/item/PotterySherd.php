<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\data\runtime\RuntimeDataDescriber;

class PotterySherd extends Item{
	private PotterySherdType $type = PotterySherdType::ANGLER;

	protected function describeState(RuntimeDataDescriber $w) : void{
		$w->enum($this->type);
	}

	public function getType() : PotterySherdType{
		return $this->type;
	}

	/** @return $this */
	public function setType(PotterySherdType $type) : self{
		$this->type = $type;
		return $this;
	}
}
