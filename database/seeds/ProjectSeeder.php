<?php

use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
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

        // Seed projects
        for ($i = 0; $i < 100; $i++) {
            $thumbnails = collect(array_random($availableThumbnails, 3));
            $thumbnails = $thumbnails->map(function ($index) {
                return asset('data/videos/thumbnails/' . $index->getRelativePathname());
            })->toArray();

            $project = new \App\Project();
            $project->fill([
                'title' => $faker->words(3, true),
                'owner' => 1,
                'team' => random_int(1, 2),
                'private' => random_int(0, 1),
                'archived' => random_int(0, 1),
            ]);
            $project->save();
        }
    }
}
