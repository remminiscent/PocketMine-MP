<?php

declare(strict_types=1);

namespace pocketmine\data\bedrock;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\item\PotterySherdType;
use pocketmine\utils\SingletonTrait;
use function spl_object_id;

final class PotterySherdTypeIdMap{
	use SingletonTrait;

	/**
	 * @var PotterySherdType[]
	 * @phpstan-var array<string, PotterySherdType>
	 */
	private array $itemIdToEnum = [];

	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $enumToItemId = [];

	private function __construct(){
		foreach(PotterySherdType::cases() as $case){
			$this->register(match($case){
				PotterySherdType::ANGLER => ItemTypeNames::ANGLER_POTTERY_SHERD,
				PotterySherdType::ARCHER => ItemTypeNames::ARCHER_POTTERY_SHERD,
				PotterySherdType::ARMS_UP => ItemTypeNames::ARMS_UP_POTTERY_SHERD,
				PotterySherdType::BLADE => ItemTypeNames::BLADE_POTTERY_SHERD,
				PotterySherdType::BREWER => ItemTypeNames::BREWER_POTTERY_SHERD,
				PotterySherdType::BURN => ItemTypeNames::BURN_POTTERY_SHERD,
				PotterySherdType::DANGER => ItemTypeNames::DANGER_POTTERY_SHERD,
				PotterySherdType::EXPLORER => ItemTypeNames::EXPLORER_POTTERY_SHERD,
				PotterySherdType::FLOW => ItemTypeNames::FLOW_POTTERY_SHERD,
				PotterySherdType::FRIEND => ItemTypeNames::FRIEND_POTTERY_SHERD,
				PotterySherdType::GUSTER => ItemTypeNames::GUSTER_POTTERY_SHERD,
				PotterySherdType::HEART => ItemTypeNames::HEART_POTTERY_SHERD,
				PotterySherdType::HEARTBREAK => ItemTypeNames::HEARTBREAK_POTTERY_SHERD,
				PotterySherdType::HOWL => ItemTypeNames::HOWL_POTTERY_SHERD,
				PotterySherdType::MINER => ItemTypeNames::MINER_POTTERY_SHERD,
				PotterySherdType::MOURNER => ItemTypeNames::MOURNER_POTTERY_SHERD,
				PotterySherdType::PLENTY => ItemTypeNames::PLENTY_POTTERY_SHERD,
				PotterySherdType::PRIZE => ItemTypeNames::PRIZE_POTTERY_SHERD,
				PotterySherdType::SCRAPE => ItemTypeNames::SCRAPE_POTTERY_SHERD,
				PotterySherdType::SHEAF => ItemTypeNames::SHEAF_POTTERY_SHERD,
				PotterySherdType::SHELTER => ItemTypeNames::SHELTER_POTTERY_SHERD,
				PotterySherdType::SKULL => ItemTypeNames::SKULL_POTTERY_SHERD,
				PotterySherdType::SNORT => ItemTypeNames::SNORT_POTTERY_SHERD,
			}, $case);
		}
	}

	private function register(string $itemId, PotterySherdType $type) : void{
		$this->itemIdToEnum[$itemId] = $type;
		$this->enumToItemId[spl_object_id($type)] = $itemId;
	}

	public function toItemId(PotterySherdType $type) : string{
		return $this->enumToItemId[spl_object_id($type)];
	}

	public function fromItemId(string $itemId) : ?PotterySherdType{
		return $this->itemIdToEnum[$itemId] ?? null;
	}
}
