<?php
namespace ImagicalGamer\SurvivalGames\Tasks;

use pocketmine\Server;
use pocketmine\Player;

use ImagicalGamer\SurvivalGames\Main;

use pocketmine\level\Level;
use pocketmine\tile\Sign;

use pocketmine\utils\Config;
use pocketmine\scheduler\PluginTask;

use pocketmine\utils\TextFormat as C;

/* Copyright (C) ImagicalGamer - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Jake C <imagicalgamer@outlook.com>, July 2016
 */

class RefreshSigns extends PluginTask{

  protected $plugin;

  public function __construct(Main $plugin)
  {
    $this->plugin = $plugin;
    parent::__construct($plugin);
  }

  public function onRun($tick){
    $allp = $this->plugin->getServer()->getOnlinePlayers();
    $lev = $this->plugin->getDefaultLevel();
    if($lev instanceof Level){
      $tiles = $lev->getTiles();
      foreach($tiles as $t){
        if($t instanceof Sign){
          $txt = $t->getText();
          if($txt[3] == $this->plugin->prefix){
            $aop = 1;
            foreach($allp as $p){
              if(in_array($p->getLevel()->getName(), $this->plugin->arenas)){
                $aop = $aop + 1;
                $game = $this->plugin->joinText;
                $cfg = new Config($this->plugin->getDataFolder() . "/arenas.yml", Config::YAML);
                if($cfg->get($text[2] . "PlayTime") != 780){
                  $game = $this->plugin->runningText;
                }
                else if($aop <= 24){
                  $game = $this->Plugin->fullText;
                }
                $t->setText($game, C::GREEN . $aop . " / 24", $text[2], $this->plugin->prefix);
              }
            }
          }
        }
      }
    }
  }
}