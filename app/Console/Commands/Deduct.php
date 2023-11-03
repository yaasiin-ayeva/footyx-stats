<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Deduction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class Deduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deduct';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply deductions on active facilities every month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Current period

        $period = Carbon::now()->format('Y-m-t');

        // Get all active facilities that are not deducted for the current period

        $apps = Application::selectRaw('applications.*')
        ->leftJoin('deductions AS ded', function($join) use($period) {
            $join->on('applications.id', 'ded.application_id');
            $join->where('period', $period);
        })
        ->where('state', 'active')
        ->whereNull('ded.id')
        ->get();

        // Deduct the right amount from the facilities

        foreach ($apps as $app) {
            $deduction = min($app->monthly_deduction, $app->remaining);
            $balance = $app->remaining - $deduction;

            Deduction::create([
                'application_id' => $app->id,
                'period' => $period,
                'deduction' => $deduction,
                'balance' => $balance
            ]);

            $app->update([
                'deducted' => $app->deducted + $deduction,
                'remaining' => $balance,
                'state' => $balance == 0 ? 'completed' : $app->state
            ]);
        }

        return 0;
    }
}
