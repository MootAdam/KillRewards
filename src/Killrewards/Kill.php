<?php

namespace KillRewards;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;

class kill extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveConfig();
    }
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        switch ($command->getName()) {
            case "mypoints":
                $this->Money($sender);
                return true;
        }
    }

    public function Money(Player $player){
        $points =  EconomyAPI::getInstance()->myMoney($player);
        $player->sendMessage("You Have §e$points §fPoints");
    }
    public function onDeath(PlayerDeathEvent $ev)
    {
        $player = $ev->getPlayer();
        $cause = $player->getLastDamageCause();

        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if ($damager instanceof Player) {
                $inv = $damager->getInventory();
                $head = Item::get(397, 3, 1);
                $name = $player->getName();
                $head->setCustomName("§e$name's Head");
                $inv->addItem($head);
                $killer = $damager->getName();
                EconomyAPI::getInstance()->reduceMoney($name, $this->getConfig()->getNested("points.remove"));
                EconomyAPI::getInstance()->addMoney($killer, $this->getConfig()->getNested("points.add"));
                $message = str_replace(["{killer}", "{dead}", "{add}", "{subtract}", "&"], [$killer, $name, $this->getConfig()->getNested("points.add"), $this->getConfig()->getNested("points.remove"), "§"], $this->getConfig()->getNested("Broadcast.BroadcastMessage"));
                if ($this->getConfig()->getNested("Broadcast.Broadcast") === true){
                    Server::getInstance()->broadcastMessage($message);
                }
            }
        }
    }

    public function onDisable()
    {
        $this->getLogger()->info("§cOffline");
    }
}