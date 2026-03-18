<?php

namespace App\Notifications;

use App\Models\KpiAlert;
use App\Models\KpiDefinition;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KpiAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly KpiAlert $alert,
        public readonly ?KpiDefinition $kpi
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $kpiName = $this->kpi?->name ?? 'KPI';
        $pct     = number_format($this->alert->achievement_pct, 1);

        return [
            'type'            => 'kpi_alert',
            'kpi_alert_id'    => $this->alert->id,
            'kpi_name'        => $kpiName,
            'severity'        => $this->alert->severity,
            'achievement_pct' => $this->alert->achievement_pct,
            'period'          => $this->alert->period_value,
            'message'         => "{$kpiName} is at {$pct}% of target for {$this->alert->period_value}.",
        ];
    }
}
