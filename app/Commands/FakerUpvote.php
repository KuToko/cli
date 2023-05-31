<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;
use Ramsey\Uuid\Uuid;

class FakerUpvote extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'faker:upvote';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('get all dummy users');
        $dummyUsers = DB::table('users')->where('is_dummy', TRUE)->get();

        $this->info('start seeding upvotes');
        foreach($dummyUsers as $dummyUser) {
            $randomNumber = rand(12, 30);
            $businesses = DB::table('businesses')->inRandomOrder()->limit($randomNumber)->get();

            $upVotes = [];
            foreach($businesses as $business) {
                $upVotes[] = [
                    'id' => Uuid::uuid4()->toString(),
                    'user_id' => $dummyUser->id,
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('upvotes')->insert($upVotes);

            $this->info("{$dummyUser->username} upvoted {$randomNumber} businesses");
        }

        $this->info('seeding upvotes done!');
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
