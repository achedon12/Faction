<?php

namespace achedon\faction\utils;

use ReflectionClass;

final class FactionPermissions
{
    const CLAIM = "faction.claim";
    const INVITE = "faction.invite";
    const KICK = "faction.kick";
    const SETHOME = "faction.sethome";
    const DELHOME = "faction.delhome";
    const TELEPORT_HOME = "faction.teleport";
    const SET_DESCRIPTION = "faction.description";
    const PROMOTE = "faction.promote";
    const DEMOTE = "faction.demote";
    const ALLY = "faction.ally";
    const ENEMY = "faction.enemy";
    const UNCLAIM = "faction.unclaim";
    const ADD_MONEY = "faction.addmoney";
    const REMOVE_MONEY = "faction.removemoney";
    const GET_MONEY = "faction.getmoney";
    const SET_PERMISSIONS = "faction.setpermissions";

    public static function getPermissions(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

}