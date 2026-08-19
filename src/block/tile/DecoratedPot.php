<?php

declare(strict_types=1);

namespace pocketmine\block\tile;

use pocketmine\block\inventory\DecoratedPotInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\world\World;
use function count;

class DecoratedPot extends Spawnable implements Container{
	public const TAG_SHERDS = "sherds";
	public const TAG_ITEM = "item";
	public const DEFAULT_SHERD = "minecraft:brick";

	private string $backSherd = self::DEFAULT_SHERD;
	private string $leftSherd = self::DEFAULT_SHERD;
	private string $rightSherd = self::DEFAULT_SHERD;
	private string $frontSherd = self::DEFAULT_SHERD;

	private DecoratedPotInventory $inventory;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new DecoratedPotInventory($this->position);
		$this->inventory->getListeners()->add(CallbackInventoryListener::onAnyChange(
			static function(Inventory $unused) use ($world, $pos) : void{
				$block = $world->getBlock($pos);
				if($block instanceof \pocketmine\block\DecoratedPot){
					$world->setBlock($pos, $block);
				}
			}
		));
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->backSherd = self::DEFAULT_SHERD;
		$this->leftSherd = self::DEFAULT_SHERD;
		$this->rightSherd = self::DEFAULT_SHERD;
		$this->frontSherd = self::DEFAULT_SHERD;

		if(($sherdsTag = $nbt->getTag(self::TAG_SHERDS)) instanceof ListTag){
			$sherds = [];
			foreach($sherdsTag as $sherd){
				if(!$sherd instanceof StringTag){
					$sherds = [];
					break;
				}
				$sherds[] = $sherd->getValue();
			}
			if(count($sherds) === 4){
				$this->setSherds(...$sherds);
			}
		}

		$listeners = $this->inventory->getListeners()->toArray();
		$this->inventory->getListeners()->remove(...$listeners);
		$this->setItem(null);
		if(($itemTag = $nbt->getCompoundTag(self::TAG_ITEM)) !== null){
			$this->setItem(Item::safeNbtDeserialize($itemTag, "DecoratedPot ($this->position) item"));
		}
		$this->inventory->getListeners()->add(...$listeners);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$sherds = [];
		foreach($this->getSherds() as $sherd){
			$sherds[] = new StringTag($sherd);
		}
		$nbt->setTag(self::TAG_SHERDS, new ListTag($sherds, NBT::TAG_String));

		$item = $this->getItem();
		if(!$item->isNull()){
			$nbt->setTag(self::TAG_ITEM, $item->nbtSerialize());
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$sherds = [];
		foreach($this->getSherds() as $sherd){
			$sherds[] = new StringTag($sherd);
		}
		$nbt->setTag(self::TAG_SHERDS, new ListTag($sherds, NBT::TAG_String));

		$item = $this->getItem();
		if(!$item->isNull()){
			$nbt->setTag(self::TAG_ITEM, TypeConverter::getInstance()->getItemTranslator()->toNetworkNbt($item));
		}
	}

	public function copyDataFromItem(Item $item) : void{
		$this->readSaveData($item->getNamedTag());
	}

	/** @return string[] */
	public function getSherds() : array{
		return [$this->backSherd, $this->leftSherd, $this->rightSherd, $this->frontSherd];
	}

	public function setSherds(string ...$sherds) : void{
		if(count($sherds) !== 4){
			throw new \InvalidArgumentException("Decorated pots must have exactly 4 sherds");
		}
		$this->backSherd = $sherds[0];
		$this->leftSherd = $sherds[1];
		$this->rightSherd = $sherds[2];
		$this->frontSherd = $sherds[3];
		$this->clearSpawnCompoundCache();
	}

	public function getItem() : Item{
		return $this->inventory->getItem(0);
	}

	public function setItem(?Item $item) : void{
		$this->inventory->setItem(0, $item ?? VanillaItems::AIR());
		$this->clearSpawnCompoundCache();
	}

	public function getInventory() : DecoratedPotInventory{
		return $this->inventory;
	}

	public function getRealInventory() : DecoratedPotInventory{
		return $this->inventory;
	}

	public function canOpenWith(string $key) : bool{
		return true;
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();
			parent::close();
		}
	}

	protected function onBlockDestroyedHook() : void{

	}
}
