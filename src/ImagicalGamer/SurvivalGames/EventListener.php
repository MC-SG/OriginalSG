<?php
namespace ImagicalGamer\SurvivalGames;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

use pocketmine\Server;
use pocketmine\Player;

use ImagicalGamer\SurvivalGames\Main;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\math\Vector3;
use pocketmine\level\Position;

use pocketmine\utils\Config;

use pocketmine\tile\Sign;

use pocketmine\utils\TextFormat as C;

use pocketmine\level\Level;

/* Copyright (C) ImagicalGamer - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Jake C <imagicalgamer@outlook.com>, July 2016
 */

class EventListener extends PluginBase implements Listener{

  protected $plugin;

  public function __construct(Main $plugin)
  {
    $this->plugin = $plugin;
  }

  public function onMove(PlayerMoveEvent $event)
  {
    $player = $event->getPlayer();
    $level = $player->getLevel()->getName();
    $cfg = new Config($this->plugin->getDataFolder() . "/arenas.yml", Config::YAML);
    if(in_array($level, $this->plugin->arenas))
    {
      $time = $cfg->get($level . "StartTime");
      if($time > 0)
      {
        $event->setCancelled(true);
      }
    }
  }

  public function onChat(PlayerChatEvent $event){
    $player = $event->getPlayer();
    $lvl = $player->getLevel()->getName();
    if($this->plugin->chatFormat() == true){
      if($this->plugin->getRank($player) == null){
        $event->setFormat($this->plugin->formatMessage($event->getMessage(), $player));
      }
      else{
      $event->setFormat($this->plugin->formatMessage($event->getMessage(), $player));
    }
    }
    return;
  }

  public function onInteract(PlayerInteractEvent $event){
  	$player = $event->getPlayer();
  	$blk = $event->getBlock();
  	$tile = $player->getLevel()->getTile($blk);
  	if($tile instanceof Sign){
  		if($this->plugin->mode == 26){
  			$tile->setText($this->plugin->joinText, C::AQUA . "0 / 24", $this->plugin->current_lev, $this->plugin->prefix);
			  $this->plugin->mode = 0;
			  $player->sendMessage($this->plugin->prefix . "Arena Registered!");
        $this->plugin->addArena($this->plugin->current_lev);
        $this->plugin->current_lev = "";
  		}
  		else{
  			$txt = $tile->getText();
  			if($txt[0] == $this->plugin->joinText){
  				if($txt[3] == $this->plugin->prefix){
  				$cfg = new Config($this->getDataFolder() . "/arenas.yml", Config::YAML);
  				$level = $this->plugin->getServer()->getLevelByName($txt[2]);
					$aop = count($level->getPlayers());
					$thespawn = $cfg->get($txt[2] . "Spawn" . ($aop+1));
					$spawn = new Position($thespawn[0]+0.5,$thespawn[1],$thespawn[2]+0.5,$level);
					$level->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
					$player->teleport($spawn,0,0);
					$player->setGamemode(0);
					$player->getInventory()->clearAll();
          $player->setHealth($player->getMaxHealth());
          $player->sendMessage($this->plugin->prefix . "You have Successfully Joined a Match!");
  				}
  			}
      }
    }
  			else if($this->plugin->mode >= 1 && $this->plugin->mode <= 24){
  				$cfg = new Config($this->getDataFolder() . "/arenas.yml", Config::YAML);
			    $cfg->set($this->plugin->current_lev . "Spawn" . $this->plugin->mode, array($blk->getX(),$blk->getY()+1,$blk->getZ()));
			    $player->sendMessage($this->plugin->prefix . "Spawn " . $this->plugin->mode . " has been registered!");
			    $this->plugin->mode++;
			    if($this->plugin->mode == 25){
                   $player->sendMessage($this->plugin->prefix . "Now tap on a deathmatch spawn.");
			    }
			    $cfg->save();
  			}
  			else if($this->plugin->mode == 25){
  				$cfg = new Config($this->getDataFolder() . "/arenas.yml", Config::YAML);
			    $cfg->set($this->plugin->current_lev . "DeathMatch", array($blk->getX(),$blk->getY()+1,$blk->getZ()));
			    $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
			    $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
			    $player->teleport($spawn,0,0);
			    $player->sendMessage($this->plugin->prefix . "You've been teleported back. Tap a sign to register it for the arena!");
			    $this->plugin->mode = 26;
			    $cfg->save();
  			}
  		}
  }