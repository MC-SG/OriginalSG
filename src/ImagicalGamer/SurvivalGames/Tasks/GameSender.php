<?php
namespace ImagicalGamer\SurvivalGames\Tasks;

use pocketmine\Server;
use pocketmine\Player;

use ImagicalGamer\SurvivalGames\Main;

use pocketmine\level\Level;
use pocketmine\level\Position;

use pocketmine\utils\Config;
use pocketmine\scheduler\PluginTask;

use pocketmine\utils\TextFormat as C;

/* Copyright (C) ImagicalGamer - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Jake C <imagicalgamer@outlook.com>, July 2016
 */

class GameSender extends PluginTask{

  protected $plugin;

  protected $name;

  public function __construct(Main $plugin)
  {
    $this->plugin = $plugin;
    parent::__construct($plugin);
  }

  public function onRun($tick){
    $cfg = new Config($this->getDataFolder() . "/arenas.json", Config::JSON);
    $arenas = $this->plugin->arenas;
    foreach($arenas as $a){
        $lev = $this->plugin->getServer()->getLevelByName($a);
        if($lev instanceof Level){
            $cfg->reload();
            $tts = $cfg->get($a . "StartTime");
            $tm = $cfg->get($a . "PlayTime");
            if(count($lev->getPlayers()) < 2 && $tts == 60){
                foreach($lev->getPlayers() as $p){
                    $p->sendPopup(C::RED . "More Players Needed!");
                }
                $this->plugin->refreshArena($a);
            }
            else if(count($lev->getPlayers()) >= 2 && $tts > 0){
                $tts--;
                $cfg->set($a . "StartTime", $tts);
                $cfg->save();
                $cfg->reload();
                foreach($lev->getPlayers() as $p){
                    $p->sendPopup(C::GREEN . "Starting in " . C::GRAY . $tts . C::GREEN . " Seconds...");
                }
                if($tts == 15 || $tts == 10 || $tts == 5 || $tts == 4 || $tts == 3 || $tts == 2 || $tts == 1){
                    foreach($lev->getPlayers() as $p){
                        $p->sendMessage($this->plugin->format . "Starting in " . $tts . "...");
                    }
                }
            }
            else if($tts <= 0 && $tm > 180){
                $cfg->reload();
                $tm--;
                $cfg->set($a . "PlayTime", $tm);
                $cfg->save();
                $cfg->reload();
                if(count($lev->getPlayers()) == 1){
                    foreach($lev->getPlayers() as $p){
                        $p->sendMessage($this->plugin->format . "You have won a SurvivalGames match!");
                        $this->name = $p->getName();
                        $nm = $this->plugin->getDefaultLevel()->getSafeSpawn();
                        $pos = new Position($nm->getX(), $nm->getY(), $nm->getX(), $this->plugin->getDefaultLevel());
                        $p->teleport($pos);
                    }
                    foreach($this->plugin->getDefaultLevel()->getPlayers() as $p){
                        $p->sendMessage($this->plugin->format . $this->name . " has won a SurvivalGames match!");
                    }
                    $this->plugin->refreshArena($a);
                }
                if($tm == 779){
                    foreach($lev->getPlayers() as $p){
                        $p->sendMessage($this->plugin->format . "Match has Started!\n You have 30 seconds of Invincibility!");
                    }
                }
                else if($tm > 749){
                    if($tm == 764){
                        foreach($lev->getPlayers() as $p){
                            $p->sendMessage($this->plugin->format . "You have 15 seconds of Invincibility left!");
                        }
                    }
                }
                $min = round($tm / 60);
                if($min == 11 || $min == 10 || $min == 9 || $min == 8 || $min == 7 || $min == 6 || $min == 5 || $min == 4 || $min == 3 || $min == 2){
                    foreach($lev->getPlayers() as $p){
                        $p->sendMessage($this->plugin->format . $min . " min remaining!");
                    }
                    if($min == 4 || $min == 6 || $min == 8 || $min == 10){
                        $this->plugin->refillChests($lev);
                        foreach($lev->getPlayers() as $p){
                            $p->sendMessage($this->plugin->format . "All chests have been refilled!");
                        }
                    }
                }
                $tm2 = $tm - 180;
                if($tm2 == 0){
                    foreach($lev->getPlayers() as $p){
                        $sp = $cfg->get($a . "DeathMatch");
                        $pos = new Position($sp[0], $sp[1], $sp[2], $lev);
                        $p->teleport($pos);
                        $p->sendMessage($this->plugin->format . "The deathmatch has started!");
                    }
                }
                else if($tm == 0){
                    foreach($lev->getPlayers() as $p){
                        $p->sendMessage($this->plugin->format . "No winner this time!");
                        $nm = $this->plugin->getDefaultLevel()->getSafeSpawn();
                        $pos = new Position($nm->getX(), $nm->getY(), $nm->getX(), $this->plugin->getDefaultLevel());
                    }
                    $this->plugin->refreshArena($a);
                } 
            }
        }
    }
  }
}
