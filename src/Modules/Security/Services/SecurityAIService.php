<?php
namespace App\Modules\Security\Services;

class SecurityAIService 
{
    private $db;
    private $alertService;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->alertService = new AlertService();
    }
    
    public function analyzeLoginAttempt($data) 
    {
        $score = 0;
        
        // Verificar localização
        $locationScore = $this->analyzeLocation($data['ip'], $data['user_id']);
        $score += $locationScore;
        
        // Verificar padrão de horário
        $timeScore = $this->analyzeLoginTime($data['user_id'], $data['timestamp']);
        $score += $timeScore;
        
        // Verificar dispositivo
        $deviceScore = $this->analyzeDevice($data['user_agent'], $data['user_id']);
        $score += $deviceScore;
        
        // Se score muito baixo, bloquear tentativa
        if ($score < 50) {
            $this->blockLoginAttempt($data);
            return false;
        }
        
        return true;
    }
    
    private function blockLoginAttempt($data) 
    {
        // Registrar bloqueio
        $this->logBlockedAttempt($data);
        
        // Notificar usuário
        $this->alertService->sendSecurityAlert($data['user_id'], 'suspicious_login');
        
        // Incrementar contador de tentativas
        $this->incrementFailedAttempts($data['ip']);
    }
} 