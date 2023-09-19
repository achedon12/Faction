<?php

namespace achedon\faction\events;

use achedon\faction\player\CustomPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;

class PlayerEvents implements Listener{

    public function playerCreation(PlayerCreationEvent $event): void{
        $event->setPlayerClass(CustomPlayer::class);
        $event->setBaseClass(CustomPlayer::class);
    }
}