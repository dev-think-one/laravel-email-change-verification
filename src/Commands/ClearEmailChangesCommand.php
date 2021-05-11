<?php

namespace EmailChangeVerification\Commands;

use Illuminate\Console\Command;

class ClearEmailChangesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:clear-email-changes {name? : The name of the email change broker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush expired email change tokens';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->laravel['auth.email_changes']->broker($this->argument('name'))->getRepository()->deleteExpired();

        $this->info('Expired email change tokens cleared!');
    }
}
