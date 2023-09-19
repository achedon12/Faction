<?php

namespace achedon\faction\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class FactionCommand extends Command
{

    public function __construct(string $name = "faction", Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = ["f"])
    {
        $this->setPermission(DefaultPermissions::ROOT_USER);
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) return;

    }
}