<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\tile\DecoratedPot as TileDecoratedPot;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\sound\DecoratedPotBreakSound;
use pocketmine\world\sound\DecoratedPotInsertFailSound;
use pocketmine\world\sound\DecoratedPotInsertSound;
use pocketmine\world\sound\DecoratedPotShatterSound;
use function array_fill;
use function min;

class DecoratedPot extends Transparent implements HorizontalFacing{
	use FacesOppositePlacingPlayerTrait;
	use StaticSupportTrait;

	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->contract(1 / 16, 0, 1 / 16)];
	}

	private function canBeSupportedAt(Block $block) : bool{
		return $block->getAdjacentSupportType(Facing::DOWN)->hasCenterSupport();
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		$world = $this->position->getWorld();
		$tile = $world->getTile($this->position);
		if($tile instanceof TileDecoratedPot && !$item->isNull()){
			$storedItem = $tile->getItem();
			if($storedItem->isNull()){
				$tile->setItem($item->pop());
				$world->addSound($this->position, new DecoratedPotInsertSound());
				return true;
			}elseif($storedItem->canStackWith($item) && $storedItem->getCount() < min($tile->getInventory()->getMaxStackSize(), $storedItem->getMaxStackSize(), $item->getMaxStackSize())){
				$item->pop();
				$storedItem->setCount($storedItem->getCount() + 1);
				$tile->setItem($storedItem);
				$world->addSound($this->position, new DecoratedPotInsertSound());
				return true;
			}
		}

		$world->addSound($this->position, new DecoratedPotInsertFailSound());
		return true;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		$item = VanillaItems::WOODEN_PICKAXE();
		$this->position->getWorld()->useBreakOn($this->position, $item);
	}

	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool{
		$this->position->getWorld()->addSound($this->position, $this->isShatteringTool($item) && !$item->hasEnchantment(VanillaEnchantments::SILK_TOUCH()) ? new DecoratedPotShatterSound() : new DecoratedPotBreakSound());

		return parent::onBreak($item, $player, $returnedItems);
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	/** @return Item[] */
	public function getSilkTouchDrops(Item $item) : array{
		return [$this->getItemWithData()];
	}

	/** @return Item[] */
	public function getDropsForCompatibleTool(Item $item) : array{
		if(!$this->isShatteringTool($item)){
			return [$this->getItemWithData()];
		}

		$tile = $this->position->getWorld()->getTile($this->position);
		$sherds = $tile instanceof TileDecoratedPot ? $tile->getSherds() : array_fill(0, 4, TileDecoratedPot::DEFAULT_SHERD);
		$drops = [];
		foreach($sherds as $sherd){
			try{
				$drops[] = GlobalItemDataHandlers::getDeserializer()->deserializeType(new SavedItemData($sherd));
			}catch(ItemTypeDeserializeException){
				$drops[] = VanillaItems::BRICK();
			}
		}
		if($tile instanceof TileDecoratedPot){
			$item = $tile->getItem();
			if(!$item->isNull()){
				$drops[] = $item;
			}
		}

		return $drops;
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return $addUserData ? $this->getItemWithData() : $this->asItem();
	}

	private function getItemWithData() : Item{
		$item = $this->asItem();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileDecoratedPot){
			$nbt = $tile->getCleanedNBT();
			if($nbt instanceof CompoundTag){
				$item->setNamedTag($nbt);
			}
		}
		return $item;
	}

	private function isShatteringTool(Item $item) : bool{
		return ($item->getBlockToolType() & (BlockToolType::PICKAXE | BlockToolType::AXE | BlockToolType::SHOVEL | BlockToolType::HOE | BlockToolType::SWORD)) !== 0;
	}
}
