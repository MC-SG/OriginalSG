<?php
namespace SurvivalGamesV3;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
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
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityLevelChangeEvent ; 
use pocketmine\tile\Chest;
use pocketmine\inventory\ChestInventory;
use pocketmine\event\plugin\PluginEvent;
class SurvivalGamesV3 extends PluginBase implements Listener {
    public $prefix = TextFormat::GRAY . "[" . TextFormat::WHITE . TextFormat::BOLD . "S" . TextFormat::RED . "G" . TextFormat::RESET . TextFormat::GRAY . "] ";
	public $mode = 0;
	public $arenas = array();
	public $currentLevel = "";
	
	public function onEnable()
	{
        $this->getServer()->getPluginManager()->registerEvents($this ,$this);
		$this->getLogger()->info(TextFormat::GREEN . "SurvivalGames Loaded!");
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
		$items = array(array(261,0,1),array(262,0,2),array(262,0,3),array(267,0,1),array(268,0,1),array(272,0,1),array(276,0,1),array(283,0,1));
		if($config->get("chestitems")==null)
		{
			$config->set("chestitems",$items);
		}
		$config->save();
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new GameSender($this), 20);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new RefreshSigns($this), 10);
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
						if($args[0]=="make")
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
								$player->sendMessage($this->prefix . "Missing parameters.");
							}
						}
						else
						{
							$player->sendMessage($this->prefix . "There is no such command.");
						}
					}
					else
					{
						$player->sendMessage($this->prefix . "Missing parameters.");
					}
				}
			return true;
			case "setrank":
				if($player->isOp())
				{
				if(!empty($args[0]))
				{
					if(!empty($args[1]))
					{
					$rank = "";
					if($args[0]=="VIP+")
					{
						$rank = "§b[§aVIP§4+§b]";
					}
					else if($args[0]=="YouTuber")
					{
						$rank = "§b[§4You§7Tuber§b]";
					}
					else if($args[0]=="YouTuber+")
					{
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
			return true;
		}
	}
	
	public function onChat(PlayerChatEvent $event)
	{
		$player = $event->getPlayer();
		$message = $event->getMessage();
		$config = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
		$rank = "";
		if($config->get($player->getName()) != null)
		{
			$rank = $config->get($player->getName());
		}
		$event->setFormat($rank . TextFormat::WHITE . $player->getName() . " §d:§f " . $message);
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
				$tile->setText(TextFormat::AQUA . "[Join]",TextFormat::YELLOW  . "0 / 24",$this->currentLevel,$this->prefix);
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
					if($text[0]==TextFormat::AQUA . "[Join]")
					{
						$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
						$level = $this->getServer()->getLevelByName($text[2]);
						$aop = count($level->getPlayers());
						$thespawn = $config->get($text[2] . "Spawn" . ($aop+1));
						$spawn = new Position($thespawn[0]+0.5,$thespawn[1],$thespawn[2]+0.5,$level);
						$level->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
						$player->teleport($spawn,0,0);
						$player->setNameTag($player->getName());
						$player->getInventory()->clearAll();
						$config2 = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
						$rank = $config2->get($player->getName());
						if($rank == "§b[§aVIP§4+§b]")
						{
							$player->getInventory()->setContents(array(Item::get(0, 0, 0)));
							$player->getInventory()->setHelmet(Item::get(Item::CHAIN_HELMET));
							$player->getInventory()->setChestplate(Item::get(Item::CHAIN_CHESTPLATE));
							$player->getInventory()->setLeggings(Item::get(Item::CHAIN_LEGGINGS));
							$player->getInventory()->setBoots(Item::get(Item::CHAIN_BOOTS));
							$player->getInventory()->setItem(0, Item::get(Item::DIAMOND_AXE, 0, 1));
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
							$player->getInventory()->setHotbarSlotIndex(0, 0);
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
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+2,$block->getZ()));
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
			$level->setSpawn = (new Vector3($block->getX(),$block->getY()+2,$block->getZ()));
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
}
class RefreshSigns extends PluginTask {
    public $prefix = TextFormat::GRAY . "[" . TextFormat::WHITE . TextFormat::BOLD . "S" . TextFormat::RED . "G" . TextFormat::RESET . TextFormat::GRAY . "] ";
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
					$ingame = TextFormat::AQUA . "[Join]";
					$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
					if($config->get($text[2] . "PlayTime")!=780)
					{
						$ingame = TextFormat::DARK_PURPLE . "[Running]";
					}
					else if($aop>=24)
					{
						$ingame = TextFormat::GOLD . "[Full]";
					}
					$t->setText($ingame,TextFormat::YELLOW  . $aop . " / 24",$text[2],$this->prefix);
				}
			}
		}
	}
}
class GameSender extends PluginTask {
    public $prefix = TextFormat::GRAY . "[" . TextFormat::WHITE . TextFormat::BOLD . "S" . TextFormat::RED . "G" . TextFormat::RESET . TextFormat::GRAY . "] ";
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
									$pl->sendPopup(TextFormat::GOLD . $timeToStart . " seconds");
								}
								if($timeToStart<=0)
								{
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
										$pl->sendMessage($this->prefix . TextFormat::GREEN . "You won!");
										$pl->getInventory()->clearAll();
										$pl->removeAllEffects();
										$pl->setNameTag($pl->getName());
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
								if(is_int($minutes) && $minutes>0)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . $minutes . " minutes to deathmatch");
									}
								}
								else if($time2 == 300)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . "The chests have been refilled!");
									}
									$this->refillChests($levelArena);
								}
								else if($time2 == 30 || $time2 == 15 || $time2 == 10 || $time2 ==5 || $time2 ==4 || $time2 ==3 || $time2 ==2 || $time2 ==1)
								{
									foreach($playersArena as $pl)
									{
										$pl->sendMessage($this->prefix . $time2 . " seconds to deathmatch");
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
									$pl->getInventory()->clearAll();
									$spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
									$this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
									$pl->teleport($spawn);
								}
								$config->set($arena . "PlayTime", 780);
								$config->set($arena . "StartTime", 60);
							}
							else
							{
								foreach($playersArena as $pl)
								{
								$pl->sendPopup(TextFormat::RED . "More players needed");
								
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
	{
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
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
