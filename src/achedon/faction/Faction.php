<?php

namespace achedon\faction;

use achedon\faction\managers\FactionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Faction extends PluginBase
{

    use SingletonTrait;

    private FactionManager $factionManager;

    protected function onEnable(): void
    {
        $this->factionManager = new FactionManager();
    }

    protected function onLoad(): void
    {
        self::setInstance($this);
    }
}