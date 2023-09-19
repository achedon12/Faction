<?php

namespace achedon\faction\managers;

use achedon\faction\Faction;
use achedon\faction\utils\FactionPermissions;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;

class FactionManager
{
    const RANK_MEMBER = "membre";
    const RANK_OFFICER = "officier";
    const RANK_RECRUIT = "recrue";
    const RANK_CHEF = "chef";
    const RANK_ESPION = "espion";
    private Config $config;
    private Faction $plugin;

    public function __construct()
    {
        $this->plugin = Faction::getInstance();
        $this->config = new Config($this->plugin->getDataFolder() . "factions.json", Config::JSON);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function createFaction(string $name, string $chef): bool
    {
        if ($this->isFaction($name)) {
            return false;
        }
        $config = $this->config;
        $config->set($name, [
            "members" => [
                "chef" => $chef,
                "officers" => [],
                "members" => [
                    $chef => ""
                ],
                "recruits" => []
            ],
            "power" => 0,
            "home" => null,
            "claims" => [],
            "allies" => [],
            "enemies" => [],
            "invites" => [],
            "permissions" => [
                "officers" => $this->initPermissions(),
                "members" => $this->initPermissions(),
                "recruits" => $this->initPermissions()
            ],
            "description" => "Aucune description",
            "level" => 0,
            "money" => 0
        ]);
        $config->save();
        return true;
    }

    public function isFaction(string $name): bool
    {
        return $this->config->exists($name);
    }

    private function initPermissions(): array
    {
        $permissions = [];
        foreach (FactionPermissions::getPermissions() as $permission) {
            $permissions[$permission] = false;
        }
        return $permissions;
    }

    public function addPower(string $name, int $power): bool
    {
        if (!$this->isFaction($name)) return false;
        $config = $this->config;
        $config->setNested($name . ".power", $this->getPower($name) + $power);
        $config->save();
        return true;
    }

    public function getPower(string $name): ?int
    {
        if (!$this->isFaction($name)) return null;
        return $this->config->getNested($name . ".power");
    }

    public function setDescription(string $name, string $description): bool
    {
        if (!$this->isFaction($name)) return false;
        $config = $this->config;
        $config->setNested($name . ".description", $description);
        $config->save();
        return true;
    }

    public function getDescription(string $name): ?string
    {
        if (!$this->isFaction($name)) return null;
        return $this->config->getNested($name . ".description");
    }

    public function addMoney(string $name, int $money): bool
    {
        if (!$this->isFaction($name)) return false;
        $config = $this->config;
        $config->setNested($name . ".money", $this->getMoney($name) + $money);
        $config->save();
        return true;
    }

    public function getMoney(string $name): ?int
    {
        if (!$this->isFaction($name)) return null;
        return $this->config->getNested($name . ".money");
    }

    public function removeMoney(string $name, int $money): bool
    {
        if (!$this->isFaction($name)) return false;
        $config = $this->config;
        $config->setNested($name . ".money", $this->getMoney($name) - $money);
        $config->save();
        return true;
    }

    public function getLevel(string $name): ?int
    {
        if (!$this->isFaction($name)) return null;
        return $this->config->getNested($name . ".level");
    }

    public function setLevel(string $name, int $level): bool
    {
        if (!$this->isFaction($name)) return false;
        $config = $this->config;
        $config->setNested($name . ".level", $level);
        $config->save();
        return true;
    }

    public function getAllies(string $name): ?array
    {
        if (!$this->isFaction($name)) return null;
        return $this->config->getNested($name . ".allies");
    }

    public function getEnemies(string $name): ?array
    {
        if (!$this->isFaction($name)) return null;
        return $this->config->getNested($name . ".enemies");
    }

    public function getClaim(string $name): ?array
    {
        if (!$this->isFaction($name)) return null;
        return $this->config->getNested($name . ".claims");
    }

    public function hasFaction(string $player): bool
    {
        foreach ($this->config->getAll() as $faction) {
            if (isset($faction["members"][$player])) return true;
        }
        return false;
    }

    public function isMember(string $name, string $player): bool
    {
        if (!$this->isFaction($name)) return false;
        if ($this->isRecruit($name, $player)) return true;
        return isset($this->config->getNested($name . ".members")[$player]);
    }

    public function isRecruit(string $name, string $player): bool
    {
        if (!$this->isFaction($name)) return false;
        return in_array($player, $this->config->getNested($name . ".members")["recruits"]);
    }

    public function setRole(string $name, string $role): bool
    {
        if (!$this->isFaction($name)) return false;
        $faction = $this->getFaction($name);
        $faction["members"][$name] = $role;
        $this->config->set($name, $faction);
        $this->config->save();
        return true;
    }

    public function getFaction(string $name): ?array
    {
        if (!$this->isFaction($name)) return null;
        return $this->config->get($name);
    }

    public function getRole(string $name, string $player): ?string
    {
        if (!$this->isFaction($name)) return null;
        $faction = $this->getFaction($name);
        return $faction["members"][$player];
    }

    public function hasHome(string $name): bool
    {
        return !is_null($this->getHome($name));
    }

    public function getHome(string $name): ?Position
    {
        $home = $this->config->getNested($name . ".home");
        if (is_null($home)) return null;
        return new Position((int)$home["x"], (int)$home["y"], (int)$home["z"], $this->plugin->getServer()->getWorldManager()->getWorldByName($home["world"]));
    }

    public function setHome(string $name, Position $position): bool
    {
        if (!$this->isFaction($name)) return false;
        $config = $this->config;
        $config->setNested($name . ".home", [
            "x" => $position->getX(),
            "y" => $position->getY(),
            "z" => $position->getZ(),
            "world" => $position->getWorld()->getFolderName()
        ]);
        $config->save();
        return true;
    }

    //TODO: Claim

    public function addInvite(string $name, string $player): bool
    {
        if (!$this->isFaction($name)) return false;
        $invites = $this->getInvites($name);
        $invites[] = $player;
        $config = $this->config;
        $config->setNested($name . ".invites", $invites);
        $config->save();
        return true;
    }

    public function getInvites(string $name): ?array
    {
        if (!$this->isFaction($name)) return null;
        return $this->config->getNested($name . ".invites");
    }

    public function removeInvite(string $name, string $player): bool
    {
        if (!$this->isFaction($name)) return false;
        $invites = $this->getInvites($name);
        if (!in_array($player, $invites)) return false;
        unset($invites[array_search($player, $invites)]);
        $config = $this->config;
        $config->setNested($name . ".invites", $invites);
        $config->save();
        return true;
    }

    public function hasInvite(string $player): bool
    {
        foreach ($this->config->getAll() as $faction) {
            if (in_array($player, $faction["invites"])) return true;
        }
        return false;
    }

    public function getPlayerFaction(string $player): ?string
    {
        foreach ($this->config->getAll() as $name => $faction) {
            if (isset($faction["members"][$player])) return $name;
        }
        return null;
    }

    public function getFactionInvites(string $player): array
    {
        $invites = [];
        foreach ($this->config->getAll() as $name => $faction) {
            if (in_array($player, $faction["invites"])) $invites[] = $name;
        }
        return $invites;
    }

    public function hasPermission(string $name, string $player, string $permission): bool
    {
        if (!$this->isFaction($name)) return false;
        if ($this->isChef($player)) return true;
        $rank = $this->getPlayerFactionRank($name, $player);
        return $this->rankHasPermission($name, $permission, $rank);
    }

    public function isChef(string $player): bool
    {
        foreach ($this->config->getAll() as $faction) {
            if ($faction["members"]["chef"] == $player) {
                return true;
            }
        }
        return false;
    }

    public function getPlayerFactionRank(string $name, string $player): ?string
    {
        if (!$this->isFaction($name)) return null;
        $faction = $this->getFaction($name);
        if (in_array($player, $faction["members"]["officers"])) {
            return self::RANK_OFFICER;
        } elseif (in_array($player, $faction["members"]["recruits"])) {
            return self::RANK_RECRUIT;
        } else {
            return self::RANK_MEMBER;
        }
    }

    public function rankHasPermission(string $name, string $permission, string $rank): bool
    {
        if (!$this->isFaction($name)) return false;
        $faction = $this->getFaction($name);
        return $faction["permissions"][$rank][$permission];
    }

    public function isSpy(string $name, string $player): bool
    {
        if (!$this->isFaction($name)) return false;
        $faction = $this->getFaction($name);
        return $faction["members"][$player] == self::RANK_ESPION;
    }

    public function isInvite(string $name, string $player): bool
    {
        if (!$this->isFaction($name)) return false;
        $faction = $this->getFaction($name);
        return in_array($player, $faction["invites"]);
    }

    public function addMember(string $name, string $player): bool
    {
        if (!$this->isFaction($name)) return false;
        $faction = $this->getFaction($name);
        $faction["members"]["recruits"][] = $player;
        $this->config->set($name, $faction);
        $this->config->save();
        return true;
    }

    public function getPlayerFactionInvites(string $player): array
    {
        $invites = [];
        foreach ($this->config->getAll() as $name => $faction) {
            if (in_array($player, $faction["invites"])) $invites[] = $name;
        }
        return $invites;
    }


}