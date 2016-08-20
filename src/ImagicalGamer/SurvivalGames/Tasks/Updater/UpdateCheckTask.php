<?php
namespace ImagicalGamer\SurvivalGames\Tasks\Updater;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;

use ImagicalGamer\SurvivalGames\Main;

/* Copyright (C) ImagicalGamer - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Jake C <imagicalgamer@outlook.com>, August 2016
 */

class UpdateCheckTask extends PluginTask{

  protected $plugin;

  protected $current_version;

  protected $new_version;

  protected $has_update = null;

  public function __construct(Main $plugin, int $version){
    parent::__construct($plugin);

    $this->plugin = $plugin;
    $this->current_version = $version;
  }

  public function onRun($tick){
    
  }

  public function onCompletion(Server $server){

  }
}
