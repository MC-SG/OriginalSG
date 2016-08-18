<?php
namespace ImagicalGamer\SurvivalGames\Commands;

use ImagicalGamer\SurvivalGames\BaseFiles\BaseCommand;
use ImagicalGamer\SurvivalGames\Main;
use pocketmine\Player;
use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat as C;

class SurvivalGamesCommand extends BaseCommand{

    private $plugin;

    public function __construct($name, Main $plugin){
        parent::__construct("sg", $plugin);
        $this->plugin = $plugin;
        $this->setUsage(C::RED . "/sg <argument>");
        $this->setDescription("SurvivalGames Commands!");
    }

    public function execute(CommandSender $sender, $commandLabel, array $args) {
        if(!$sender->hasPermission("sg.command.create")){
            $sender->sendMessage(C::RED . "You dont have permission to run this command!");
            return false;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(C::RED . "Please run in-game!");
            return false;
        }
        if(count($args) < 2){
            $sender->sendMessage(C::RED . "Usage: /sg <argument>");
            return false;
        }
        if(count($args) == 2){
            $this->plugin->newArena($sender, $args[1]);
        }
    }
}
