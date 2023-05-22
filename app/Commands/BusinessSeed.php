<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;
use Ramsey\Uuid\Uuid;

class BusinessSeed extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'business:seed';

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
        $file = storage_path('app/public/businesses.csv');
        $collection = collect();
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 1000, ',');
        while (($line = fgetcsv($handle, 1000, ',')) !== false) {
            foreach($line as $key => $value) {
                if($value === '') {
                    $line[$key] = null;
                }
            }
            $collection->push(array_combine($header, $line));
        }
        fclose($handle);

        foreach($collection as $row) {
            DB::beginTransaction();
            try {
                $ifExists = DB::table('businesses')->where('place_id', $row['place_id'])->exists();
                if($ifExists) {
                    continue;
                }

                $categories = explode(',', $row['categories']);
                $googleMapsLink = $row['google_maps_link'];
                unset($row['google_maps_link']);
                unset($row['google_maps_rating']);
                unset($row['categories']);

                DB::table('businesses')->insert($row);
                $businessId = $row['id'];

                foreach($categories as $category) {
                    $ifCategoryExists = DB::table('categories')->where('key', $category)->first();
                    $categoryId = optional($ifCategoryExists)->id;
                    if(!$ifCategoryExists) {
                        $categoryId = Uuid::uuid4()->toString();
                        DB::table('categories')->insert([
                            'id' => $categoryId,
                            'key' => $category,
                            'name' => $category,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('business_categories')->insert([
                        'id' => Uuid::uuid4()->toString(),
                        'business_id' => $businessId,
                        'category_id' => $categoryId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('links')->insert([
                    'id' => Uuid::uuid4()->toString(),
                    'business_id' => $businessId,
                    'name' => 'Google Maps',
                    'link' => $googleMapsLink,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->info('Inserted: ' . $row['name']);
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error($e->getMessage());
                continue;
            }
            
        }
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
