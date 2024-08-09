<?php

namespace App\Helpers;

use Illuminate\Support\Str;

/**
 * Categories:
 * 1 - skill
 * 2 - pvp
 * 3 - clue
 * 4 - minigame
 * 5 - boss
 * 6 - raid
 * 7 - npc
 */
class HiscoreHelper
{
    public static function all(): array
    {
        return [
            'skill' => self::skill(),
            'pvp' => self::pvp(),
            'clue' => self::clue(),
            'minigame' => self::minigame(),
            'boss' => self::boss(),
        ];
    }

    public static function skill(): array
    {
        return [
            Str::slug('Attack') => 'Attack',
            Str::slug('Defence') => 'Defence',
            Str::slug('Strength') => 'Strength',
            Str::slug('Hitpoints') => 'Hitpoints',
            Str::slug('Ranged') => 'Ranged',
            Str::slug('Prayer') => 'Prayer',
            Str::slug('Magic') => 'Magic',
            Str::slug('Cooking') => 'Cooking',
            Str::slug('Woodcutting') => 'Woodcutting',
            Str::slug('Fletching') => 'Fletching',
            Str::slug('Fishing') => 'Fishing',
            Str::slug('Firemaking') => 'Firemaking',
            Str::slug('Crafting') => 'Crafting',
            Str::slug('Smithing') => 'Smithing',
            Str::slug('Mining') => 'Mining',
            Str::slug('Herblore') => 'Herblore',
            Str::slug('Agility') => 'Agility',
            Str::slug('Thieving') => 'Thieving',
            Str::slug('Slayer') => 'Slayer',
            Str::slug('Farming') => 'Farming',
            Str::slug('Runecraft') => 'Runecraft',
            Str::slug('Hunter') => 'Hunter',
            Str::slug('Construction') => 'Construction',
        ];
    }

    public static function pvp(): array
    {
        return [
            Str::slug('Bounty Hunter - Hunter') => 'Bounty Hunter - Hunter',
            Str::slug('Bounty Hunter - Rogue') => 'Bounty Hunter - Rogue',
            Str::slug('Bounty Hunter (Legacy) - Hunter') => 'Bounty Hunter (Legacy) - Hunter',
            Str::slug('Bounty Hunter (Legacy) - Rogue') => 'Bounty Hunter (Legacy) - Rogue',
        ];
    }

    public static function clue(): array
    {
        return [
            Str::slug('Shared Treasure Trail Rewards') => 'Shared Treasure Trail Rewards', // Clue Scrolls (all)
            Str::slug('Beginner Treasure Trails') => 'Beginner Treasure Trails', // Clue Scrolls (beginner)
            Str::slug('Easy Treasure Trails') => 'Easy Treasure Trails', // Clue Scrolls (easy)
            Str::slug('Medium Treasure Trails') => 'Medium Treasure Trails', // Clue Scrolls (medium)
            Str::slug('Hard Treasure Trails') => 'Hard Treasure Trails', // Clue Scrolls (hard)
            Str::slug('Elite Treasure Trails') => 'Elite Treasure Trails', // Clue Scrolls (elite)
            Str::slug('Master Treasure Trails') => 'Master Treasure Trails', // Clue Scrolls (master)
        ];
    }

    public static function minigame(): array
    {
        return [
            Str::slug('LMS - Rank') => 'LMS - Rank',
            Str::slug('PvP Arena - Rank') => 'PvP Arena - Rank',
            Str::slug('Soul Wars') => 'Soul Wars', // Soul Wars Zeal
            Str::slug('Guardians of the Rift') => 'Guardians of the Rift', // Rifts closed
            Str::slug('Fortis Colosseum') => 'Fortis Colosseum', // Colosseum Glory
        ];
    }

    public static function boss(): array
    {
        return [
            Str::slug('Abyssal Sire') => 'Abyssal Sire',
            Str::slug('Alchemical Hydra') => 'Alchemical Hydra',
            Str::slug('Artio') => 'Artio',
            Str::slug('Barrows Chests') => 'Barrows Chests',
            Str::slug('Bryophyta') => 'Bryophyta',
            Str::slug('Callisto') => 'Callisto',
            Str::slug("Calvar'ion") => "Calvar'ion",
            Str::slug('Cerberus') => 'Cerberus',
//            Str::slug('Chambers of Xeric') => 'Chambers of Xeric',
//            Str::slug('Chambers of Xeric: Challenge Mode') => 'Chambers of Xeric: Challenge Mode',
            Str::slug('Chaos Elemental') => 'Chaos Elemental',
            Str::slug('Chaos Fanatic') => 'Chaos Fanatic',
            Str::slug('Commander Zilyana') => 'Commander Zilyana',
            Str::slug('Corporeal Beast') => 'Corporeal Beast',
            Str::slug('Crazy Archaeologist') => 'Crazy Archaeologist',
            Str::slug('Dagannoth Prime') => 'Dagannoth Prime',
            Str::slug('Dagannoth Rex') => 'Dagannoth Rex',
            Str::slug('Dagannoth Supreme') => 'Dagannoth Supreme',
            Str::slug('Deranged Archaeologist') => 'Deranged Archaeologist',
            Str::slug('Duke Sucellus') => 'Duke Sucellus',
            Str::slug('General Graardor') => 'General Graardor',
            Str::slug('Giant Mole') => 'Giant Mole',
            Str::slug('Grotesque Guardians') => 'Grotesque Guardians',
            Str::slug('Hespori') => 'Hespori',
            Str::slug('Kalphite Queen') => 'Kalphite Queen',
            Str::slug('King Black Dragon') => 'King Black Dragon',
            Str::slug('Kraken') => 'Kraken',
            Str::slug("Kree'Arra") => "Kree'Arra",
            Str::slug("K'ril Tsutsaroth") => "K'ril Tsutsaroth",
            Str::slug('Lunar Chests') => 'Lunar Chests',
            Str::slug('Mimic') => 'Mimic',
            Str::slug('Nex') => 'Nex',
            Str::slug('Nightmare') => 'Nightmare',
            Str::slug("Phosani's Nightmare") => "Phosani's Nightmare",
            Str::slug('Obor') => 'Obor',
            Str::slug('Phantom Muspah') => 'Phantom Muspah',
            Str::slug('Sarachnis') => 'Sarachnis',
            Str::slug('Scorpia') => 'Scorpia',
            Str::slug('Scurrius') => 'Scurrius',
            Str::slug('Skotizo') => 'Skotizo',
            Str::slug('Sol Heredit') => 'Sol Heredit',
            Str::slug('Spindel') => 'Spindel',
            Str::slug('Tempoross') => 'Tempoross',
            Str::slug('The Gauntlet') => 'The Gauntlet',
            Str::slug('The Corrupted Gauntlet') => 'The Corrupted Gauntlet',
            Str::slug('The Leviathan') => 'The Leviathan',
            Str::slug('The Whisperer') => 'The Whisperer',
//            Str::slug('Theatre of Blood') => 'Theatre of Blood',
//            Str::slug('Theatre of Blood: Hard Mode') => 'Theatre of Blood: Hard Mode',
            Str::slug('Thermonuclear Smoke Devil') => 'Thermonuclear Smoke Devil',
//            Str::slug('Tombs of Amascut') => 'Tombs of Amascut',
//            Str::slug('Tombs of Amascut: Expert Mode') => 'Tombs of Amascut: Expert Mode',
            Str::slug('TzKal-Zuk') => 'TzKal-Zuk',
            Str::slug('TzTok-Jad') => 'TzTok-Jad',
            Str::slug('Vardorvis') => 'Vardorvis',
            Str::slug('Venenatis') => 'Venenatis',
            Str::slug("Vet'ion") => "Vet'ion",
            Str::slug('Vorkath') => 'Vorkath',
            Str::slug('Wintertodt') => 'Wintertodt',
            Str::slug('Zalcano') => 'Zalcano',
            Str::slug('Zulrah') => 'Zulrah',
        ];
    }
}
