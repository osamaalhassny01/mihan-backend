<?php
require_once __DIR__ . '/../core/Model.php';
class ProviderEvaluation extends Model {
 protected string $table='provider_evaluations';
 private function ensureTable(): void { $this->db->exec("CREATE TABLE IF NOT EXISTS provider_evaluations (id INT AUTO_INCREMENT PRIMARY KEY, provider_id INT NOT NULL, admin_id INT NOT NULL, score DECIMAL(5,2) NOT NULL, quality_score TINYINT NULL, reliability_score TINYINT NULL, communication_score TINYINT NULL, compliance_score TINYINT NULL, comment TEXT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_pe_provider(provider_id), INDEX idx_pe_created(created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }
 public function latest(int $providerId): ?array { $this->ensureTable(); $r=$this->query("SELECT pe.*, u.name AS admin_name FROM provider_evaluations pe JOIN users u ON u.id=pe.admin_id WHERE pe.provider_id=:p ORDER BY pe.created_at DESC, pe.id DESC LIMIT 1",['p'=>$providerId]); return $r[0]??null; }
 public function history(int $providerId): array { $this->ensureTable(); return $this->query("SELECT pe.*, u.name AS admin_name FROM provider_evaluations pe JOIN users u ON u.id=pe.admin_id WHERE pe.provider_id=:p ORDER BY pe.created_at DESC, pe.id DESC",['p'=>$providerId]); }
 public function objectiveScore(array $p): float {
   $rating=min(5,max(0,(float)($p['avg_rating']??0))); $ratingScore=$rating*20;
   $completion=min(100,max(0,(float)($p['completed_jobs']??0)>0?min(100,(float)$p['completed_jobs']*2):0));
   $response=min(100,max(0,(float)($p['response_rate']??0))); $verified=!empty($p['is_verified'])?100:50; $availability=!empty($p['is_available'])?100:60;
   return round($ratingScore*.40+$response*.20+$completion*.15+$verified*.15+$availability*.10,2);
 }
 public function combinedScore(array $p, ?array $latest): float { $obj=$this->objectiveScore($p); $manual=$latest?(float)$latest['score']*20:null; return round($manual===null?$obj:$obj*.65+$manual*.35,2); }
}
