<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Command;

class CreatePersonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-person';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create person';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Name');
        Person::create([
            'name' => $name,
        ]);
    }
}
