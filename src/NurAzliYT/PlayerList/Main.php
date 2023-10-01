<?php
namespace NurAzliYT\PlayerList;

use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Models\Messages\Embed\Field;
use JaxkDev\DiscordBot\Models\Messages\Embed\Footer;
use JaxkDev\DiscordBot\Plugin\Events\MessageSent;
use JaxkDev\DiscordBot\Plugin\Api;
use JaxkDev\DiscordBot\Plugin\Main as DiscordBot;
use JaxkDev\DiscordBot\Plugin\ApiResultion;
use JaxkDev\DiscordBot\Plugin\ApiRejection;
use JaxkDev\DiscordBot\Models\Messages\Message;

use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Main extends PluginBase implements Listener{

    public DiscordBot $discord;
  
    public function onEnable() : void{
        $discord = $this->getServer()->getPluginManager()->getPlugin("DiscordBot");
        if (!$discord instanceof DiscordBot){
          $this->getLogger()->info("Incompatible dependency 'DiscordBot' detected, see https://github.com/DiscordBot-PMMP/DiscordBot/releases for the correct plugin.");
          $this->getServer()->getPluginManager()->disablePlugin($this);
          return;
        }
        $this->discord = $discord;
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function MessageSent(MessageSent $event){
        $api = $this->discord->getApi();
        $message = $event->getMessage();
        $content = $message->getContent();
        $channel_id = $message->getChannelId();
        $args = explode(" ", $content);
        // command
        if(isset($args[0]) && $args[0] == "!list"){
          $onlines = $this->getServer()->getOnlinePlayers();
          $p = count($onlines) . "/" . $this->getServer()->getMaxPlayers();
          $players =  implode("\n", array_map(fn(Player $player) => $player->getName(), $onlines));
          $api->sendMessage(null, $channel_id, null, $message->getAuthorId(), [new Embed(
            "List Players",
            count($onlines) === 0 ? "There Are No Players In The Server" : $p,
            null, time(), null,
            new Footer("List Players v" . $this->getDescription()->getVersion()),
            null, null, null, null, null,
            count($onlines) === 0 ? [] : [new Field("Players", $players, true)]
          )])->otherwise(function(ApiRejection $rejection){
            $this->getLogger()->error("Failed to send command response: " . $rejection->getMessage());
          });
          /*$api->fetchChannel(null, $channel_id)->then(function(ApiResultion $resultion) use($api, $channel_id, $message) {
            $channel = $resultion->getData()[0];
            $server_id = $channel->getGuildId();
            
            $api->sendMessage($server_id, $channel_id, $p . $m, $message->getAuthorId());
          }, function(ApiRejection $rejection) /*use($api, $channel_id, $message){
            $this->getLogger()->error("Failed to send command response: " . $rejection->getMessage());
            $this->getServer()->getPluginManager()->disablePlugin($this);
          });*/
        }
    }
}
