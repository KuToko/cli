<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LaravelZero\Framework\Commands\Command;

class UserFaker extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'faker:user';

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
        // remove all dummy user
        $this->info("Removing all dummy user...");
        DB::table('users')->where('is_dummy', TRUE)->delete();
        $this->info("All dummy user removed!");
        // create faker for user, use fakerphp
        $this->info("Creating dummy user...");
        for($i = 0; $i < 50; $i++) {
            $faker = \Faker\Factory::create();
            $user = [
                'id' => $faker->uuid,
                'username' => $faker->userName,
                'name' => $faker->name,
                'email' => $faker->email,
                'password' => '$2y$10$vPF/dw3oKzn5m/M2giGozu4ZIohKQKXyyQ9KdOjfKh41CmFMrZ8jG',
                'is_super_admin' => FALSE,
                'created_at' => $faker->dateTime,
                'updated_at' => $faker->dateTime,
                'is_dummy' => TRUE,
            ];
            DB::table('users')->insert($user);
            $this->info("User {$user['username']} created!");
        }
        $this->info("All dummy user created!");
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
