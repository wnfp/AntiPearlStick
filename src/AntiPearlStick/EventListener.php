<?php

declare(strict_types=1);

namespace AntiPearlStick;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;

class EventListener implements Listener {

    public function __construct(
        private Main $plugin
    ) {}

    public function onDamage(EntityDamageByEntityEvent $event): void {
        $attacker = $event->getDamager();
        $victim = $event->getEntity();

        if ($attacker instanceof Player && $victim instanceof Player) {
            $item = $attacker->getInventory()->getItemInHand();
            
            $stickItemString = $this->plugin->getConfig()->get("stick_item", "minecraft:blaze_rod");
            $stickItem = StringToItemParser::getInstance()->parse($stickItemString);
            
            if ($stickItem !== null && $item->isSameType($stickItem)) {
                
                if ($this->plugin->hasStickCooldown($attacker)) {
                    $time = $this->plugin->getRemainingStickCooldown($attacker);
                    $msg = str_replace(
                        "{time}",
                        (string)$time,
                        $this->plugin->getConfig()->getNested("messages.attacker_cooldown", "")
                    );
                    $attacker->sendActionBarMessage($msg);
                    return;
                }

                $this->plugin->setStickCooldown($attacker);
                $this->plugin->setCooldown($victim);
                
                $antiPearlTime = $this->plugin->getConfig()->get("cooldown_seconds", 15);
                
                $msgAttacker = str_replace(
                    ["{player}", "{time}"],
                    [$victim->getName(), (string)$antiPearlTime],
                    $this->plugin->getConfig()->getNested("messages.attacker", "")
                );
                $attacker->sendActionBarMessage($msgAttacker);
                
                $msgVictim = str_replace(
                    "{time}",
                    (string)$antiPearlTime,
                    $this->plugin->getConfig()->getNested("messages.victim_hit", "")
                );
                $victim->sendActionBarMessage($msgVictim);
            }
        }
    }

    public function onUse(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->getTypeId() === VanillaItems::ENDER_PEARL()->getTypeId()) {
            if ($this->plugin->hasCooldown($player)) {
                $event->cancel();
                
                $time = $this->plugin->getRemainingCooldown($player);
                $msg = str_replace(
                    "{time}",
                    (string)$time,
                    $this->plugin->getConfig()->getNested("messages.victim_try", "")
                );
                $player->sendActionBarMessage($msg);
            }
        }
    }
}
