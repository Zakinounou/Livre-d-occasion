<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Livre;
use App\Models\Promotion;
use Carbon\Carbon;

class TrackPromotions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotions:track';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Track and apply/remove promotions based on their start and end dates';

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
        $today = Carbon::today()->toDateString();

        // Apply promotions
        $promotionsToStart = Promotion::where('dat_debut', $today)->get();
        foreach ($promotionsToStart as $promotion) {
            // Find the books associated with this promotion and update them
            Livre::where('id_pro', null)->update(['id_pro' => $promotion->id]);
        }

        // Remove promotions
        $promotionsToEnd = Promotion::where('dat_fin', $today)->get();
        foreach ($promotionsToEnd as $promotion) {
            // Find the books associated with this promotion and update them
            Livre::where('id_pro', $promotion->id)->update(['id_pro' => null]);
        }

        $this->info('Promotions tracked and updated successfully.');

        return 0;
    }
}
