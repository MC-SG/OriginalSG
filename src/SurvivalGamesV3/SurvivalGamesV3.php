<?php
namespace SurvivalGamesV3;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\Listener;
use pocketmine\level\sound\TNTPrimeSound;
use pocketmine\level\sound\PopSound;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityLevelChangeEvent ; 
use pocketmine\tile\Chest;
use pocketmine\inventory\ChestInventory;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\entity\Entity;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class SurvivalGamesV3 extends PluginBase implements Listener {
	
    public $prefix = C::GRAY . "[" . C::WHITE . C::BOLD . "S" . C::RED . "G" . C::RESET . C::GRAY . "] ";
	public $mode = 0;
	public $arenas = array();
	public $currentLevel = "";
	
	public function onEnable()
	{
        $this->getServer()->getPluginManager()->registerEvents($this ,$this);
		$this->getLogger()->info(C::GREEN . "SurvivalGames Loaded!");
		$this->saveResource("rank.yml");
		$this->saveResource("config.yml");
		@mkdir($this->getDataFolder());
		$config2 = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
		$config2->save();
		$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
		if($config->get("arenas")!=null)
		{
			$this->arenas = $config->get("arenas");
		}
		foreach($this->arenas as $lev)
		{
			$this->getServer()->loadLevel($lev);
		}
		$items = array(array(261,0,1),array(262,0,2),array(262,0,3),array(267,0,1),array(268,0,1),array(272,0,1),array(276,0,1),array(283,0,1),array(283,0,1),array(283,0,1),array(283,0,1),array(283,0,1),array(283,0,1),array(283,0,1),array(283,0,1),array(283,0,1),array(283,0,1),array(283,0,1));
		if($config->get("chestitems")==null)
		{
			$config->set("chestitems",$items);
		}
                if($config->get("lightning_effect")==null){
                $config->set("lightning_effect","ON");
                }
		$config->save();
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new GameSender($this), 20);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new RefreshSigns($this), 10);
	}
	
	public function giveRandomKit(PlayerJoinEvent $e){
	if($this->getConfig()->get("RandomKit") === true){	
		$p = $e->getPlayer();
		$kit = rand(1,6);
		switch($kit){
			case 1:
				$p->getInventory()->addItem(Item::get(302,0,1));
				$p->getInventory()->addItem(Item::get(303,0,1));
				$p->getInventory()->addItem(Item::get(304,0,1));
				$p->getInventory()->addItem(Item::get(305,0,1));
				$p->getInventory()->addItem(Item::get(279,0,1));
				
				$p->sendMessage(C::DARK_AQUA."You Randomly Got The ".C::YELLOW."VIP+".C::DARK_AQUA." Kit!");
			break;
			
			case 2:
				$p->getInventory()->addItem(Item::get(298,0,1));
				$p->getInventory()->addItem(Item::get(299,0,1));
				$p->getInventory()->addItem(Item::get(300,0,1));
				$p->getInventory()->addItem(Item::get(301,0,1));
				$p->getInventory()->addItem(Item::get(268,0,1));
				
				$p->sendMessage(C::DARK_AQUA."You Randomly Got The ".C::YELLOW."Beginnerz".C::DARK_AQUA." Kit!");
			break;
			
			case 3:
				$effect = Effect::getEffect(1);
				$effect->setDuration(2184728365782365723642365723652); 
				$effect->setVisible(true);
				$effect->setAmplifier(2);
				$p->addEffect($effect);
				
				$effect2 = Effect::getEffect(8);
				$effect2->setDuration(2184728365782365723642365723652); 
				$effect2->setVisible(true);
				$effect2->setAmplifier(3);
				$p->addEffect($effect2);
				
				$p->getInventory()->addItem(Item::get(311,0,1));
				
				$p->sendMessage(C::DARK_AQUA."You Randomly Got The ".C::YELLOW."Athlete".C::DARK_AQUA." Kit!");
			break;
			
			case 4:
				$ef = Effect::getEffect(8);
				$ef->setDuration(2184728365782365723642365723652); 
				$ef->setVisible(true);
				$ef->setAmplifier(4);
				$p->addEffect($ef);
				
				$p->getInventory()->addItem(Item::get(293,0,1));
				
				$p->sendMessage(C::DARK_AQUA."You Randomly Got The ".C::YELLOW."Rabbit".C::DARK_AQUA." Kit!");
			break;
			
			case 5:
				$p->getInventory()->addItem(Item::get(314,0,1));
				$p->getInventory()->addItem(Item::get(315,0,1));
				$p->getInventory()->addItem(Item::get(316,0,1));
				$p->getInventory()->addItem(Item::get(317,0,1));
				$p->getInventory()->addItem(Item::get(283,0,2));
				$p->getInventory()->addItem(Item::get(322,0,5));
				
				$p->sendMessage(C::DARK_AQUA."You Randomly Got The ".C::YELLOW."Midas".C::DARK_AQUA." Kit!");
			break;
			
			case 6:
				$p->getInventory()->addItem(Item::get(261,0,1));
				$p->getInventory()->addItem(Item::get(262,0,64));
				$p->getInventory()->addItem(Item::get(354,0,5));
				
				$p->sendMessage(C::DARK_AQUA."You Randomly Got The ".C::YELLOW."Sugary Archers".C::DARK_AQUA." Kit!");
			break;
		}
		}
	}
 	public function PlayerDeath(PlayerDeathEvent $event){
          foreach($this->getServer()->getOnlinePlayers() as $pl){
	  $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                 if($config->get("lightning_effect")=="ON"){
              $p = $event->getEntity();
          $light = new AddEntityPacket();
          $light->type = 93;
          $light->eid = Entity::$entityCount++;
          $light->metadata = array();
          $light->speedX = 0;
          $light->speedY = 0;
          $light->speedZ = 0;
          $light->x = $p->x;
          $light->y = $p->y;
          $light->z = $p->z;
          $pl->dataPacket($light);
          $event->setDeathMessage("§7" . $event->getEntity()->getName() . " was demolished ");
          }
          
 		}
 	}
    public function playerJoin($spawn){
	$player->teleport(new Vector3($x, $y, $z, $level));	
	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn(); 
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), 
        $spawn->getFloorZ()); $player->teleport($spawn,0,0);
	}
	public function onMove(PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$sofar = $config->get($level . "StartTime");
			if($sofar > 0)
			{
				$to = clone $event->getFrom();
				$to->yaw = $event->getTo()->yaw;
				$to->pitch = $event->getTo()->pitch;
				$event->setTo($to);
			}
		}
	}
	
	public function onDamage(EntityDamageEvent $event) 
	{
		if ($event->getEntity() instanceof Player) {
			$level = $event->getEntity()->getLevel()->getFolderName();
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			if ($config->get($level . "PlayTime") != null) {
				if ($config->get($level . "PlayTime") > 750 && $config->get($level . "PlayTime") <= 780) {
					$event->setCancelled(true);
				}
			}
		}
	}
	
	public function onBlockBreak(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
			$event->setCancelled(true);
		}
	}
	
	public function onBlockPlace(BlockPlaceEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if(in_array($level,$this->arenas))
		{
			$event->setCancelled(true);
		}
	}
	
	public function onCommand(CommandSender $player, Command $cmd, $label, array $args) {
        switch($cmd->getName()){
			case "sg":
				if($player->isOp())
				{
					if(!empty($args[0]))
                                       
					{
						if($args[0]=="create")
						{
							if(!empty($args[1]))
							{
								if(file_exists($this->getServer()->getDataPath() . "/worlds/" . $args[1]))
								{
									$this->getServer()->loadLevel($args[1]);
									$this->getServer()->getLevelByName($args[1])->loadChunk($this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorX(), $this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorZ());
									array_push($this->arenas,$args[1]);
									$this->currentLevel = $args[1];
									$this->mode = 1;
									$player->sendMessage($this->prefix . "You are about to register an arena. Tap a block to set a spawn point there!");
									$player->setGamemode(1);
									$player->teleport($this->getServer()->getLevelByName($args[1])->getSafeSpawn(),0,0);
								}
								else
								{
									$player->sendMessage($this->prefix . "There is no world with this name.");
								}
							}
							else
							{
							                                             $player->sendMessage($this->prefix . "SurvivalGames Commands!");
                                             $player->sendMessage($this->prefix . "/sg create [world] Creates an arena in the specified world!");
                                             $player->sendMessage($this->prefix . "/setrank [rank] [player] sets a players rank!");
                                             $player->sendMessage($this->prefix . "/ranks shows a list of ranks! <- In Dev");	
							}
						}
						else
						{
							$player->sendMessage($this->prefix . "There is no such command.");
						}
					}
					else
					{
                                             $player->sendMessage($this->prefix . "SurvivalGames Commands!");
                                             $player->sendMessage($this->prefix . "/sg create [world] Creates an arena in the specified world!");
                                             $player->sendMessage($this->prefix . "/setrank [rank] [player] sets a players rank!");
                                             $player->sendMessage($this->prefix . "/ranks shows a list of ranks! <- In Dev");
					}
				}
			return true;
			case "setrank":
				if($this->getConfig()->get("Ranks") === true){
				if($player->isOp()){
				if(!empty($args[0]))	{
					if(!empty($args[1])){
					$rank = "";
					if($args[0]=="VIP+"){
						$rank = "§b[§aVIP§4+§b]";
					}
					else if($args[0]=="YouTuber"){
						$rank = "§b[§4You§7Tuber§b]";
					}
					else if($args[0]=="YouTuber+"){
						$rank = "§b[§4You§7Tuber§4+§b]";
					}
					else
					{
						$rank = "§b[§a" . $args[0] . "§b]";
					}
					$config = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
					$config->set($args[1],$rank);
					$config->save();
					$player->sendMessage($args[1] . " got this rank: " . $rank);
					}
					else
					{
						$player->sendMessage("Missing parameter(s)");
					}
				}
				else
				{
					$player->sendMessage("Missing parameter(s)");
				}
				}
				}
			return true;
		}
	}
	
	public function onChat(PlayerChatEvent $event)
	{
		$player = $event->getPlayer();
		$message = $event->getMessage();
		$config = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
		$rank = "";
		if($config->get($player->getName()) != null){
			$rank = $config->get($player->getName());
		}
		
		$level = $player->getLevel()->getFolderName();
		
		if(in_array($level,$this->arenas)){
			$event->setRecipients($player->getLevel()->getPlayers());
		}
		
		if($config->get("EnableChatFormat") === true){
 			$event->setFormat($rank . C::WHITE . $player->getName() . " §d:§f " . $message);
		}
		
 		if(in_array($level,$this->arenas) === false){
 			$event->setRecipients($player->getLevel()->getPlayers());
 		}
	}
	
	public function onInteract(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$tile = $player->getLevel()->getTile($block);
		
		if($tile instanceof Sign) 
		{
			if($this->mode==26)
			{
				$tile->setText(C::GRAY . "[§2Join§7]",C::BLUE  . "0 / 24",$this->currentLevel,$this->prefix);
				$this->refreshArenas();
				$this->currentLevel = "";
				$this->mode = 0;
				$player->sendMessage($this->prefix . "The arena has been registered successfully!");
			}
			else
			{
				$text = $tile->getText();
				if($text[3] == $this->prefix)
				{
					if($text[0]==C::WHITE . "[§bJoin§f]")
					{
						$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
						$level = $this->getServer()->getLevelByName($text[2]);
						$aop = count($level->getPlayers());
						$thespawn = $config->get($text[2] . "Spawn" . ($aop+1));
						$spawn = new Position($thespawn[0]+0.5,$thespawn[1],$thespawn[2]+0.5,$level);
						$level->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
						$player->teleport($spawn,0,0);
						$player->setGamemode(0);
						$player->setNameTag(C::BOLD . C::RED . $player->getName());
						$player->getInventory()->clearAll();
                                                $player->sendMessage($this->prefix . C::GRAY . "You have Successfully Joined a Match!");
						$config2 = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
						$rank = $config2->get($player->getName());
						if($this->getConfig()->get("Ranks") === true){
						if($rank == "§b[§aVIP§4+§b]")
						{
							$player->getInventory()->setContents(array(Item::get(0, 0, 0)));
							$player->getInventory()->setHelmet(Item::get(Item::CHAIN_HELMET));
							$player->getInventory()->setChestplate(Item::get(Item::CHAIN_CHESTPLATE));
							$player->getInventory()->setLeggings(Item::get(Item::CHAIN_LEGGINGS));
							$player->getInventory()->setBoots(Item::get(Item::CHAIN_BOOTS));
							$player->getInventory()->setItem(0, Item::get(Item::DIAMOND_AXE, 0, 1));
							$player->getInventory()->sendArmorContents($player);
							$player->getInventory()->setHotbarSlotIndex(0, 0);
						}
						else if($rank == "§b[§aVIP§b]")
						{
							$player->getInventory()->setContents(array(Item::get(0, 0, 0)));
							$player->getInventory()->setHelmet(Item::get(Item::GOLD_HELMET));
							$player->getInventory()->setChestplate(Item::get(Item::GOLD_CHESTPLATE));
							$player->getInventory()->setLeggings(Item::get(Item::LEATHER_PANTS));
							$player->getInventory()->setBoots(Item::get(Item::LEATHER_BOOTS));
							$player->getInventory()->setItem(0, Item::get(Item::IRON_AXE, 0, 1));
								$player->getInventory()->sendArmorContents($player);
							$player->getInventory()->setHotbarSlotIndex(0, 0);
						}
						else if($rank == "§b[§4You§7Tuber§b]")
						{
							$player->getInventory()->setContents(array(Item::get(0, 0, 0)));
							$player->getInventory()->setHelmet(Item::get(Item::GOLD_HELMET));
							$player->getInventory()->setChestplate(Item::get(Item::GOLD_CHESTPLATE));
							$player->getInventory()->setLeggings(Item::get(Item::GOLD_LEGGINGS));
							$player->getInventory()->setBoots(Item::get(Item::GOLD_BOOTS));
							$player->getInventory()->setItem(0, Item::get(Item::IRON_AXE, 0, 1));
								$player->getInventory()->sendArmorContents($player);
							$player->getInventory()->setHotbarSlotIndex(0, 0);
						}
						else if($rank == "§b[§aVIP§b]")
						{
							$player->getInventory()->setContents(array(Item::get(0, 0, 0)));
							$player->getInventory()->setHelmet(Item::get(Item::DIAMOND_HELMET));
							$player->getInventory()->setChestplate(Item::get(Item::CHAIN_CHESTPLATE));
							$player->getInventory()->setLeggings(Item::get(Item::CHAIN_LEGGINGS));
							$player->getInventory()->setBoots(Item::get(Item::DIAMOND_BOOTS));
							$player->getInventory()->setItem(0, Item::get(Item::DIAMOND_AXE, 0, 1));
								$player->getInventory()->sendArmorContents($player);
							$player->getInventory()->setHotbarSlotIndex(0, 0);
						}
						}
					}
					else
					{
						$player->sendMessage($this->prefix . "You can not join this match.");
					}
				}
			}
		}
		else if($this->mode>=1&&$this->mode<=24)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn " . $this->mode . " has been registered!");
			$this->mode++;
			if($this->mode==25)
			{
				$player->sendMessage($this->prefix . "Now tap on a deathmatch spawn.");
			}
			$config->save();
		}
		else if($this->mode==25)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$level = $this->getServer()->getLevelByName($this->currentLevel);
			$level->setSpawn = (new Vector3($block->getX(),$block->getY()+1,$block->getZ()));
			$config->set("arenas",$this->arenas);
			$player->sendMessage($this->prefix . "You've been teleported back. Tap a sign to register it for the arena!");
			$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
			$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
			$player->teleport($spawn,0,0);
			$config->save();
			$this->mode=26;
		}
	}
	
	public function refreshArenas()
	{
		$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
		$config->set("arenas",$this->arenas);
		foreach($this->arenas as $arena)
		{
			$config->set($arena . "PlayTime", 780);
			$config->set($arena . "StartTime", 60);
		}
		$config->save();
	}
	
	public function onDisable()
	{
		$this->saveResource("config.yml");
		$this->saveResource("rank.yml");
	}
}
class RefreshSigns extends PluginTask {
    public $prefix = C::GRAY . "[" . C::WHITE . C::BOLD . "S" . C::RED . "G" . C::RESET . C::GRAY . "] ";
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
  
	public function onRun($tick)
	{
		$allplayers = $this->plugin->getServer()->getOnlinePlayers();
		$level = $this->plugin->getServer()->getDefaultLevel();
		$tiles = $level->getTiles();
		foreach($tiles as $t) {
			if($t instanceof Sign) {	
				$text = $t->getText();
				if($text[3]==$this->prefix)
				{
					$aop = 0;
					foreach($allplayers as $player){if($player->getLevel()->getFolderName()==$text[2]){$aop=$aop+1;}}
					$ingame = C::WHITE . "[§bJoin§f]";
					$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
					if($config->get($text[2] . "PlayTime")!=780)
					{
						$ingame = C::GRAY . "[§cRunning§7]";
					}
					else if($aop>=24)
					{
						$ingame = C::GRAY . "[§4Full§7]";
					}
					$t->setText($ingame,C::BLUE  . $aop . " / 24",$text[2],$this->prefix);
				}
			}
		}
	}
}
class GameSender extends PluginTask {
    public $prefix = C::GRAY . "[" . C::WHITE . C::BOLD . "S" . C::RED . "G" . C::RESET . C::GRAY . "] ";
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
  
	public function onRun($tick)
	{
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$arenas = $config->get("arenas");
		if(!empty($arenas))
		{
			foreach($arenas as $arena)
			{
				$time = $config->get($arena . "PlayTime");
				$timeToStart = $config->get($arena . "StartTime");
				$levelArena = $this->plugin->getServer()->getLevelByName($arena);
				if($levelArena instanceof Level)
				{
					$playersArena = $levelArena->getPlayers();
					if(count($playersArena)==0)
					{
						$config->set($arena . "PlayTime", 780);
						$config->set($arena . "StartTime", 60);
					}
					else
					{
						if(count($playersArena)>=2)
						{
							if($timeToStart>0)
							{
								$timeToStart--;
								foreach($playersArena as $pl)
								{
									$pl->sendPopup(C::GRAY . "Starting in " . $timeToStart . " Seconds");
								}
								if($timeToStart == 30 || $timeToStart == 25 || $timeToStart == 15 || $timeToStart == 10 || $timeToStart ==5 || $timeToStart ==4 || $timeToStart ==3 || $timeToStart ==2 || $timeToStart ==1)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . $timeToStart . " Seconds until Start");
										$level=$pl->getLevel();
										$level->addSound(new PopSound($pl));
									}
								        $config->set($arena . "StartTime", $timeToStart);
							        }
								if($timeToStart<=0)
								{

									foreach($playersArena as $pl)
									{
									$level=$pl->getLevel();
									$level->addSound(new TNTPrimeSound($pl));
                                                                        $pl->sendMessage("§b-------------------------------§r");
                                                                        $pl->sendMessage($this->prefix . C::GRAY . "Let the Games" . C::RED . C::BOLD . " Begin!");
                                                                        $pl->sendMessage($this->prefix . C::GRAY . "You have 30 seconds of §bGrace");
                                                                        $pl->sendMessage("§b-------------------------------§r");}
                                                                        $this->refillChests($levelArena);
								}
								$config->set($arena . "StartTime", $timeToStart);
							}
							else
							{
								$aop = count($levelArena->getPlayers());
								if($aop==1)
								{
									foreach($playersArena as $pl)
									{
										$spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
										$this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
										$pl->teleport($spawn,0,0);
									}
									$config->set($arena . "PlayTime", 780);
									$config->set($arena . "StartTime", 60);
								}
								$time--;
								if($time>=180)
								{
								$time2 = $time - 180;
								$minutes = $time2 / 60;
									foreach($playersArena as $pl)
									{
										$pl->sendPopup($this->prefix . $time2 . " left in the match!");
									}
								if(is_int($minutes) && $minutes>0)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . $minutes . " minutes to deathmatch");
										$level=$pl->getLevel();
										$level->addSound(new PopSound($pl));
									}
								}
								else if($time2 == 300)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . "The chests have been refilled!");
                                                                                $level=$pl->getLevel();
                                                                                $level->addSound(new PopSound($pl));
									}
									$this->refillChests($levelArena);
								}
								else if($time2 == 570){
									
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . "§cYou Are no longer invincible!");
                                                                                $level=$pl->getLevel();
                                                                                $level->addSound(new PopSound($pl));
									}	
									
								}
								else if($time2 == 30 || $time2 == 15 || $time2 == 10 || $time2 ==5 || $time2 ==4 || $time2 ==3 || $time2 ==2 || $time2 ==1)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . $time2 . " seconds to deathmatch");
										$level=$pl->getLevel();
										$level->addSound(new PopSound($pl));
									}
								}
								if($time2 <= 0)
								{
									$spawn = $levelArena->getSafeSpawn();
									$levelArena->loadChunk($spawn->getX(), $spawn->getZ());
									foreach($playersArena as $pl)
									{
										$pl->teleport($spawn,0,0);
									}
								}
								}
								else
								{
									$minutes = $time / 60;
									if(is_int($minutes) && $minutes>0)
									{
										foreach($playersArena as $pl)
										{
											$pl->sendMessage($this->prefix . $minutes . " minutes remaining");
										}
									}
									else if($time == 30 || $time == 15 || $time == 10 || $time ==5 || $time ==4 || $time ==3 || $time ==2 || $time ==1)
									{
										foreach($playersArena as $pl)
										{
											$pl->sendMessage($this->prefix . $time . " seconds remaining");
										}
									}
									if($time <= 780)
									{
									}
	
									if($time <= 0)
									{
										$spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
										$this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
										foreach($playersArena as $pl)
										{
											$pl->teleport($spawn,0,0);
											$pl->sendMessage($this->prefix . "No winner this time!");
											$pl->getInventory()->clearAll();
										}
										$time = 780;
									}
								}
								$config->set($arena . "PlayTime", $time);
							}
						}
						else
						{
							if($timeToStart<=0)
							{
								foreach($playersArena as $pl)
								{
								        $name = $pl->getName();
									$pl->getInventory()->clearAll();
                                                                        $pl->sendTip($this->prefix . C::GRAY . "You won the match!");
									$spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
									$this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
									$pl->teleport($spawn,0,0);
									foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
										$p->sendMessage($this->prefix . C::GRAY . $name . " Has won a SurvivalGames match!");
									}
								}
								$config->set($arena . "PlayTime", 780);
								$config->set($arena . "StartTime", 60);
							}
							else
							{
								foreach($playersArena as $pl)
								{
								$pl->sendPopup(C::RED . "A game requires 2 players!");
								
								}
								$config->set($arena . "PlayTime", 780);
								$config->set($arena . "StartTime", 60);
							}
						}
					}
				}
			}
		}
		$config->save();
	}
	
	public function refillChests(Level $level)
	{	$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$tiles = $level->getTiles();
		foreach($tiles as $t) {
 			if($t instanceof Chest) 
 			{
 				$chest = $t;
 				$chest->getInventory()->clearAll();
 				if($chest->getInventory() instanceof ChestInventory)
 				{
 					for($i=0;$i<=26;$i++)
 					{
 						$rand = rand(1,3);
 						if($rand==1)
 						{
 							$k = array_rand($config->get("chestitems"));
 							$v = $config->get("chestitems")[$k];
 							$chest->getInventory()->setItem($i, Item::get($v[0],$v[1],$v[2]));
 						}
 					}
 				}
 			}
		}
	}
}
