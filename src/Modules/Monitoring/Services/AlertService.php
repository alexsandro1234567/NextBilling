<?php
namespace App\Modules\Monitoring\Services;

class AlertService 
{
    private $notificationChannels = [];
    
    public function __construct() 
    {
        $this->loadNotificationChannels();
    }
    
    public function createAlert($data) 
    {
        // Salvar alerta no banco
        $alert = Alert::create([
            'server_id' => $data['server_id'],
            'type' => $data['type'],
            'level' => $data['level'],
            'message' => $data['message'],
            'status' => 'active'
        ]);
        
        // Notificar pelos canais configurados
        $this->notifyAlert($alert);
        
        return $alert;
    }
    
    private function notifyAlert($alert) 
    {
        foreach ($this->notificationChannels as $channel) {
            if ($channel->isEnabled() && $channel->shouldNotify($alert)) {
                $channel->send($alert);
            }
        }
    }
    
    private function loadNotificationChannels() 
    {
        $this->notificationChannels = [
            new EmailNotification(),
            new SlackNotification(),
            new SMSNotification(),
            new TelegramNotification(),
            new DiscordNotification()
        ];
    }
} 