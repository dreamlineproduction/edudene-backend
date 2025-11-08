<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateRemberToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-rember-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update rember token every 10 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {        
        User::whereNotNull('remember_token')->update([
            'remember_token' => Null,
        ]);
    }
}
