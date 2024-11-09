<?php
namespace App\Modules\Security\Services;

use Google\Authenticator\GoogleAuthenticator;

class MFAService 
{
    private $authenticator;
    private $db;
    
    public function __construct() 
    {
        $this->authenticator = new GoogleAuthenticator();
        $this->db = Database::getInstance();
    }
    
    public function enableMFA($userId) 
    {
        // Gerar chave secreta
        $secret = $this->authenticator->generateSecret();
        
        // Salvar no banco
        $stmt = $this->db->prepare("
            INSERT INTO user_mfa (user_id, secret, status) 
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$userId, $secret]);
        
        return [
            'secret' => $secret,
            'qr_code' => $this->generateQRCode($secret)
        ];
    }
    
    public function verifyCode($userId, $code) 
    {
        $mfa = $this->getUserMFA($userId);
        
        if ($this->authenticator->verifyCode($mfa['secret'], $code, 2)) {
            // Ativar MFA se estiver pendente
            if ($mfa['status'] === 'pending') {
                $this->activateMFA($userId);
            }
            return true;
        }
        
        return false;
    }
    
    public function generateBackupCodes($userId) 
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = bin2hex(random_bytes(4));
        }
        
        // Salvar códigos hasheados
        foreach ($codes as $code) {
            $this->saveBackupCode($userId, password_hash($code, PASSWORD_DEFAULT));
        }
        
        return $codes;
    }
} 