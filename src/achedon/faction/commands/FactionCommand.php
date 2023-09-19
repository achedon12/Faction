<?php

namespace achedon\faction\commands;

use achedon\faction\Faction;
use achedon\faction\utils\FactionPermissions;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class FactionCommand extends Command
{

    const ARGS = [
        "create <name>" => "permet de créer une faction",
        "invite <player>" => "permet d'inviter un joueur dans votre faction",
        "kick <player>" => "permet d'expulser un joueur de votre faction",
        "sethome" => "permet de définir le home de votre faction",
        "delhome" => "permet de supprimer le home de votre faction",
        "home" => "permet de se téléporter au home de votre faction",
        "description" => "permet de définir la description de votre faction",
        "promote <player>" => "permet de promouvoir un joueur dans votre faction",
        "demote <player>" => "permet de rétrograder un joueur dans votre faction",
        "ally <faction>" => "permet de faire une demande d'alliance à une faction",
        "enemy <faction>" => "permet de faire une demande de déclaration de guerre à une faction",
        "unclaim" => "permet de supprimer un claim",
        "addmoney <amount>" => "permet d'ajouter de l'argent à votre faction",
        "removemoney <amount>" => "permet de retirer de l'argent à votre faction",
        "money" => "permet de voir l'argent de votre faction",
        "permissions" => "permet de définir les permissions de votre faction",
        "help" => "permet d'afficher l'aide",
    ];

    public function __construct(string $name = "faction", Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = ["f"])
    {
        $this->setPermission(DefaultPermissions::ROOT_USER);
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) return;

        $plugin = Faction::getInstance();
        $factionManager = $plugin->getFactionManager();

        if (empty($args[0]) || $args[0] === "help") {
            $sender->sendMessage($this->getHelpMenu());
            return;
        }

        $hasFaction = $factionManager->hasFaction($sender->getName());
        if ($hasFaction) {
            $faction = $factionManager->getPlayerFaction($sender->getName());
            $rank = $factionManager->getPlayerFactionRank($faction, $sender->getName());
            $role = $factionManager->getRole($faction, $sender->getName());
        }

        /* Create a faction */
        if ($args[0] === "create") {
            if (empty($args[1])) {
                $sender->sendMessage("§cVeuillez entrer un nom de faction");
                return;
            }
            if ($factionManager->isFaction($args[1])) {
                $sender->sendMessage("§cCette faction existe déjà");
                return;
            }
            $factionManager->createFaction($args[1], $sender->getName());
            $sender->sendMessage("§aVous avez créé la faction $args[1]");
            return;
        }

        /* Invite a player */
        if ($args[0] === "invite") {
            if (empty($args[1])) {
                $sender->sendMessage("§cVeuillez entrer un nom de joueur");
                return;
            }
            if ($args[1] === "invite") {
                if (empty($args[2]) || !$factionManager->isFaction($args[2])) {
                    $sender->sendMessage("§cVeuillez entrer le nom d'une faction valide");
                    return;
                }
                if (!$factionManager->isInvite($args[2], $sender->getName())) {
                    $sender->sendMessage("§cVous n'avez pas été invité dans cette faction");
                    return;
                }
                if (empty($args[3])) {
                    $sender->sendMessage("§c/f invite <faction> <toggle:deny|accept>");
                }
                if ($args[3] === "accept") {
                    $factionManager->addMember($args[2], $sender->getName());
                    $sender->sendMessage("§aVous avez rejoint la faction $args[2]");
                    $factionManager->removeInvite($args[2], $sender->getName());

                    return;
                }
                if ($args[3] === "deny") {
                    $sender->sendMessage("§cVous avez refusé l'invitation");
                    $factionManager->removeInvite($args[2], $sender->getName());
                    return;
                }
                return;
            }
            if (!$hasFaction) {
                $sender->sendMessage("§cVous n'avez pas de faction");
                return;
            }
            if (!$factionManager->isChef($sender->getName()) && $factionManager->rankHasPermission($faction, FactionPermissions::INVITE, $rank)) {
                $sender->sendMessage("§cVous n'avez pas la permission d'inviter un joueur");
                return;
            }
            $target = $plugin->getServer()->getPlayerByPrefix($args[1]);
            if (!$target instanceof Player) {
                $sender->sendMessage("§cCe joueur n'est pas connecté");
                return;
            }
            if ($factionManager->hasFaction($target->getName())) {
                if (!$factionManager->isSPy($factionManager->getPlayerFaction($target->getName()), $target->getName())) {
                    $sender->sendMessage("§cCe joueur a déjà une faction");
                    return;
                }
                $factionManager->addInvite($faction, $target->getName());
                $target->sendMessage("§aVous avez été invité dans la faction $faction\n§e/f invite invite <faction> accept §7: pour accepter l'invitation\n§e/f invite invite <faction> deny §7: pour refuser l'invitation\n\n§c/!\\ Rappelez-vous de votre rôle d'espion");
                $sender->sendMessage("§aVous avez invité {$target->getName()} dans votre faction");
                return;
            }
            $factionManager->addInvite($faction, $target->getName());
            $target->sendMessage("§aVous avez été invité dans la faction $faction\n§e/f invite invite <faction> accept §7: pour accepter l'invitation\n§e/f invite invite <faction> deny §7: pour refuser l'invitation");
            $sender->sendMessage("§aVous avez invité {$target->getName()} dans votre faction");
            return;
        }

        if ($args[0] === "kick") {
            if (!$hasFaction) {
                $sender->sendMessage("§cVous n'avez pas de faction");
                return;
            }
            if (!$factionManager->isChef($sender->getName()) && $factionManager->rankHasPermission($faction, FactionPermissions::KICK, $rank)) {
                $sender->sendMessage("§cVous n'avez pas la permission d'expulser un joueur");
                return;
            }
            if (empty($args[1])) {
                $sender->sendMessage("§cVeuillez entrer un nom de joueur");
                return;
            }
            $target = $plugin->getServer()->getPlayerByPrefix($args[1]);
            if (!$target instanceof Player) {
                $sender->sendMessage("§cCe joueur n'est pas connecté");
                return;
            }
            if (!$factionManager->isMember($faction, $target->getName())) {
                $sender->sendMessage("§cCe joueur n'est pas dans votre faction");
                return;
            }
            //TODO: finish kick command

        }
    }

    private function getHelpMenu(): string
    {
        $helpMenu = "";
        foreach (self::ARGS as $arg => $description) {
            $helpMenu .= "§e/f $arg §7: $description\n";
        }
        return $helpMenu;
    }
}