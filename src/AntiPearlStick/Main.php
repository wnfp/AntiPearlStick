<?php

declare(strict_types=1);

namespace AntiPearlStick;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use AntiPearlStick\task\CooldownTask;

class Main extends PluginBase {

    /** @var array<string, int> */
    private array $cooldowns = [];
    
    /** @var array<string, int> */
    private array $stickCooldowns = [];

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new CooldownTask($this), 20);
    }

    public function setCooldown(Player $player): void {
        $time = $this->getConfig()->get("cooldown_seconds", 15);
        $this->cooldowns[$player->getName()] = time() + $time;
    }

    public function hasCooldown(Player $player): bool {
        $name = $player->getName();
        if (!isset($this->cooldowns[$name])) {
            return false;
        }
        if (time() >= $this->cooldowns[$name]) {
            unset($this->cooldowns[$name]);
            return false;
        }
        return true;
    }

    public function getRemainingCooldown(Player $player): int {
        return max(0, ($this->cooldowns[$player->getName()] ?? time()) - time());
    }

    public function cleanCooldown(string $playerName): void {
        unset($this->cooldowns[$playerName]);
    }

    public function getCooldowns(): array {
        return $this->cooldowns;
    }

    public function setStickCooldown(Player $player): void {
        $time = $this->getConfig()->get("stick_cooldown_seconds", 120);
        $this->stickCooldowns[$player->getName()] = time() + $time;
    }

    public function hasStickCooldown(Player $player): bool {
        $name = $player->getName();
        if (!isset($this->stickCooldowns[$name])) {
            return false;
        }
        if (time() >= $this->stickCooldowns[$name]) {
            unset($this->stickCooldowns[$name]);
            return false;
        }
        return true;
    }

    public function getRemainingStickCooldown(Player $player): int {
        return max(0, ($this->stickCooldowns[$player->getName()] ?? time()) - time());
    }
}
