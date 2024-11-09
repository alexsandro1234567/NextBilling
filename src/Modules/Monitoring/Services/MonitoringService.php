<?php
namespace App\Modules\Monitoring;

class MonitoringService 
{
    private $db;
    private $alertService;
    private $metrics = [];
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->alertService = new AlertService();
    }
    
    public function monitorServer($serverId) 
    {
        $metrics = [
            'cpu' => $this->getCPUUsage($serverId),
            'memory' => $this->getMemoryUsage($serverId),
            'disk' => $this->getDiskUsage($serverId),
            'network' => $this->getNetworkMetrics($serverId),
            'processes' => $this->getProcessList($serverId)
        ];
        
        // Salvar métricas
        $this->saveMetrics($serverId, $metrics);
        
        // Verificar anomalias
        $this->checkAnomalies($serverId, $metrics);
        
        return $metrics;
    }
    
    private function checkAnomalies($serverId, $metrics) 
    {
        $thresholds = $this->getServerThresholds($serverId);
        
        // Verificar CPU
        if ($metrics['cpu']['usage'] > $thresholds['cpu_warning']) {
            $this->alertService->createAlert([
                'server_id' => $serverId,
                'type' => 'cpu_high',
                'level' => $metrics['cpu']['usage'] > $thresholds['cpu_critical'] ? 'critical' : 'warning',
                'message' => "CPU usage at {$metrics['cpu']['usage']}%"
            ]);
        }
        
        // Verificar Memória
        if ($metrics['memory']['used_percent'] > $thresholds['memory_warning']) {
            $this->alertService->createAlert([
                'server_id' => $serverId,
                'type' => 'memory_high',
                'level' => $metrics['memory']['used_percent'] > $thresholds['memory_critical'] ? 'critical' : 'warning',
                'message' => "Memory usage at {$metrics['memory']['used_percent']}%"
            ]);
        }
    }
} 