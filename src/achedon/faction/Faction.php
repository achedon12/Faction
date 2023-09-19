<?php

namespace achedon\faction;

use achedon\faction\commands\FactionCommand;
use achedon\faction\events\PlayerEvents;
use achedon\faction\managers\FactionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class Faction extends PluginBase
{

    use SingletonTrait;

    private FactionManager $factionManager;

    /**
     * @return FactionManager
     */
    public function getFactionManager(): FactionManager
    {
        return $this->factionManager;
    }

    public function getConfig(): Config
    {
        return new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    protected function onEnable(): void
    {
        $this->factionManager = new FactionManager();

        $this->getServer()->getCommandMap()->register('faction',new FactionCommand());
        $this->getServer()->getPluginManager()->registerEvents(new PlayerEvents(), $this);

        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
    }

    protected function onLoad(): void
    {
        self::setInstance($this);
    }
}