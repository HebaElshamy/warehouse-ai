<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ForecastDemandRF extends Command
{
    protected $signature = 'forecast:rf {--horizon=7}';
    protected $description = 'Run RandomForest demand forecasting and store predictions';

    public function handle()
    {
        $horizon = (int) $this->option('horizon');

        $script = base_path('scripts/forecast_rf.py');
        $python = env('PYTHON_BIN', 'python3');

        $env = [
            'DB_URL' => env('PY_DB_URL'),
            'FORECAST_HORIZON_DAYS' => (string) $horizon,
            'EVAL_DAYS' => '3',
            'PYTHONUNBUFFERED' => '1',
        ];

        $process = new Process([$python, '-u', $script]);
        $process->setEnv($env);
        $process->setTimeout(600);

        $process->run(function ($type, $buffer) {
            $this->line(rtrim($buffer));
        });

        if (!$process->isSuccessful()) {
            $this->error("RF forecast failed.");
            return self::FAILURE;
        }

        $this->info("RF forecast done.");
        return self::SUCCESS;
    }
}
