<?php
namespace ImagicalGamer\SurvivalGames\Commands;

use ImagicalGamer\SurvivalGames\BaseFiles\BaseCommand;
use ImagicalGamer\SurvivalGames\Main;
use ImagicalGamer\SurvivalGames\Tasks\Stats\TopStatsTask;
use pocketmine\Player;
use pocketmine\command\CommandSender;

class StatsCommand extends BaseCommand{

    private $plugin;

    public function __construct($name, Main $plugin){
        parent::__construct("sgstats", $plugin);
        $this->plugin = $plugin;
        $this->setDescription("SurvivalGames Stats!");
    }

    public function execute(CommandSender $sender, $commandLabel, array $args) {
        if(count($args) == 1 && $sender instanceof Player){
            if($args[0] == "top"){
                $this->plugin->getServer()->getScheduler()->scheduleAsyncTask($task = new TopStatsTask($sender->getName(), 10));
                return;
            }
            if($args[0] == "mystats"){
                $sender->sendMessage($this->plugin->getPlayerStats($sender->getName()));
                return;
            }
            $sender->sendMessage("Usage: /sgstats <mystats|top>");
            return;
        }
        $sender->sendMessage("Usage: /sgstats <mystats|top>");
        return;
    }
}
