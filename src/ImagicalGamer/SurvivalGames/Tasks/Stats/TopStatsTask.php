<?php
namespace ImagicalGamer\SurvivalGames\Tasks\Stats;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;

use EconomyPlus\Main;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use pocketmine\item\Item;

/* Copyright (C) ImagicalGamer - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Jake C <imagicalgamer@outlook.com>, July 2016
 */

class TopStatsTask extends AsyncTask{

  public function __construct(String $player){
    $this->player = $player;
  }

  public function onRun(){
  }


  public function onCompletion(Server $server){
    $sts = new Config($server->getPluginPath() . "/SurvivalGames/stats.json", Config::JSON);
    $stats = $sts->getAll();
    arsort($stats);
    $ret = array();
    $n = 0;
    $server->getPlayer($this->player)->sendMessage(C::RED . "SurvivalGames Stats!");
    $server->getPlayer($this->player)->sendMessage(C::GRAY . "---------------------");
    foreach($stats as $p => $m){
      $n++;
      $ret[$n] = [$p, $m];
      $message = json_encode($ret[$n]);
      $message = str_replace(["[", "]", '"'], "", $message);
      $message = str_replace(",", "§c: ", $message);
      if($p == strtolower($server->getPlayer($this->player)->getName())) {
      $server->getPlayer($this->player)->sendMessage(C::RED . C::BOLD . "* Your Rank: " . C::RESET . C::GRAY . $n . "\n  ");
      }
    }
    $n = 0;
    foreach($stats as $p => $m){
      $n++;
      $ret[$n] = [$p, $m];
      $message = json_encode($ret[$n]);
      $message = str_replace(["[", "]", '"'], "", $message);
      $message = str_replace(",", "§c: ", $message);
      if($n > 10){
        return $n = $n - 10;
      }
      $server->getPlayer($this->player)->sendMessage(C::RED . C::BOLD . "* " . C::RESET . C::GRAY . $n . ". " . C::GRAY . $message . " wins!");
    }
  }
}
