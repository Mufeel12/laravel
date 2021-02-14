<?php

use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        $directory = public_path('data/videos/thumbnails');
        $availableThumbnails = File::allFiles($directory);

        // Seed videos
        for ($i = 0; $i < 100; $i++) {
            $thumbnails = collect(array_random($availableThumbnails, 1));
            $thumbnail = $thumbnails->map(function ($index) {
                return asset('data/videos/thumbnails/' . $index->getRelativePathname());
            })->shift();

            $video = new \App\Video();
            $video->fill([
                'video_id' => str_random(8),
                'title' => $faker->words(3, true),
                'owner' => 1,
                'project' => random_int(1, 118),
                'team' => random_int(1, 2),
                'filename' => $faker->name . '.' . $faker->fileExtension,
                'thumbnail' => json_encode($thumbnail),
                'path' => url($faker->name . '.' . $faker->fileExtension),
                'duration' => random_int(5, 600),
                'duration_formatted' => random_int(1, 59) . ':' . random_int(1, 59)
            ]);
            $video->save();
        }
    }
}
