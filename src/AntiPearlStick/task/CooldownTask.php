<?php

declare(strict_types=1);

namespace AntiPearlStick\task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use AntiPearlStick\Main;

class CooldownTask extends Task {

    public function __construct(
        private Main $plugin
    ) {}

    public function onRun(): void {
        $currentTime = time();
        $server = Server::getInstance();

        foreach ($this->plugin->getCooldowns() as $playerName => $expireTime) {
            if ($currentTime >= $expireTime) {
                $this->plugin->cleanCooldown($playerName);
                
                $player = $server->getPlayerExact($playerName);
                if ($player !== null) {
                    $msg = $this->plugin->getConfig()->getNested("messages.victim_end", "");
                    $player->sendActionBarMessage($msg);
                }
            }
        }
    }
}
