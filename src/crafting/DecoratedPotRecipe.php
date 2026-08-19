<?php

declare(strict_types=1);

namespace pocketmine\crafting;

use pocketmine\block\tile\DecoratedPot as TileDecoratedPot;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use function count;

class DecoratedPotRecipe extends ShapedRecipe{
	/** @return list<Item> */
	public function getResultsFor(CraftingGrid $grid) : array{
		$results = $this->getResults();
		if(count($results) !== 1){
			throw new \LogicException("Decorated pot recipes must have exactly one result");
		}

		$sherds = [];
		foreach([
			$grid->getIngredient(1, 0),
			$grid->getIngredient(0, 1),
			$grid->getIngredient(2, 1),
			$grid->getIngredient(1, 2)
		] as $item){
			$sherds[] = new StringTag(GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName());
		}

		$results[0]->setNamedTag(CompoundTag::create()->setTag(
			TileDecoratedPot::TAG_SHERDS,
			new ListTag($sherds, NBT::TAG_String)
		));

		return $results;
	}
}
