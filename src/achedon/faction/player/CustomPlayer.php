<?php

namespace achedon\faction\player;

use achedon\faction\Faction;
use pocketmine\player\Player;

class CustomPlayer extends Player
{

    private int $time = 20;

    public function onUpdate(int $currentTick): bool
    {
        if ($this->time > 0) {
            $this->time--;
            return true;
        }
        $this->time = 20;

        $plugin = Faction::getInstance();

        $format = $plugin->getConfig()->get("nameTagFormat");
        $faction = $plugin->getFactionManager()->getPlayerFaction($this);

        $this->setNameTag(str_replace(["{faction}", "{player}"], [$faction, $this->getName()], $format));
        return parent::onUpdate($currentTick);
    }
}