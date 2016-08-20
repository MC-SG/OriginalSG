<?php
namespace ImagicalGamer\SurvivalGames\Tasks\Updater;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;
use pocketmine\utils\TextFormat as C;

use ImagicalGamer\SurvivalGames\Main;

/* Copyright (C) ImagicalGamer - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Jake C <imagicalgamer@outlook.com>, August 2016
 */

class UpdateCheckTask extends AsyncTask{

  private $plugin;

  private $current_version;

  private $new_version;

  private $has_update;

  public function __construct(int $version){
    $this->current_version = $version;
    $this->has_update = null;
  }

  public function onRun(){
    $nversion = Utils::getUrl("https://raw.githubusercontent.com/ImagicalGamer/SurvivalGames/master/resources/version");
    if($nversion > $this->version){
      $this->has_update = true;
    }
    else if($nversion == $this->version){
      $this->has_update = false;
    }
    else if($nversion < $this->version){
      $this->has_update = null;
    }
  }

  public function onCompletion(Server $server){
    if($this->has_update == true){
      $server->getPluginManager()->getPlugin("SurvivalGames")->getLogger()->info(C::YELLOW . "A SurvivalGames Update has been found!");
    }
    else if($this->plugin->has_update == false){
      $server->getPluginManager()->getPlugin("SurvivalGames")->getLogger()->info(C::AQUA . "No updates found! Your using the latest version of SurvivalGames!");
    }
    else{
      $server->getPluginManager()->getPlugin("SurvivalGames")->getLogger()->warning("Invalid SurvivalGames Version!");
    }
  }
}
