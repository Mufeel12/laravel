<?php

use App\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddUniqueIdToProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $projects = Project::get();

        try {
            foreach ($projects as $project) {
                $project->project_id = generate_project_unique_id();
                $project->save();
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}
