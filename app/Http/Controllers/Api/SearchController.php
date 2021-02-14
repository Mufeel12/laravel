<?php

namespace App\Http\Controllers\Api;

use App\ContactAutoTag;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocationListRequest;
use App\Http\Requests\SearchTagsRequest;
use App\Project;
use App\Slate;
use App\User;
use App\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');

        $results = [];

        // Todo: search where current user in team.

        $projects = Project::where('title', 'LIKE', "%{$query}%")
            ->get();

        $videos = Video::where('title', 'LIKE', "%{$query}%")
            ->get();

        $slates = Slate::where('title', 'LIKE', "%{$query}%")
            ->get();

        $results = array_merge($slates->map(function ($index) {
            return [
                'value' => $index->title,
                'video' => $index->id,
                'type' => 'slate',
                'title' => $index->title,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="24" viewBox="0 0 24 24" width="24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-4h11v4zm0-5H4V9h11v4zm5 5h-4V9h4v9z"/><path d="M0 0h24v24H0z" fill="none"/></svg>',
                'link' => url('/slates/' . $index->id . '/edit/'),
            ];
        })->toArray(), $results);

        $results = array_merge($videos->map(function ($index) {
            return [
                'value' => $index->title,
                'type' => 'video',
                'title' => $index->title,
                'icon' => '<svg fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none"/><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8 12.5v-9l6 4.5-6 4.5z"/></svg>',
                'link' => url('/projects/' . $index->project . '/edit/' . $index->id),
            ];
        })->toArray(), $results);

        $results = array_merge($projects->map(function ($index) {
            return [
                'value' => $index->title,
                'type' => 'project',
                'title' => $index->title,
                'icon' => '<svg fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none"/><path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z"/></svg>',
                'link' => url('/projects/' . $index->id),
            ];
        })->toArray(), $results);

        return $results;
    }

    public function searchTags(SearchTagsRequest $request, $statusUser = 1)
    {
        $validatedData = $request->validated();
        return ContactAutoTag::searchTags($validatedData, $statusUser);
    }

    public function searchPlanStatus(SearchTagsRequest $request, $status = false)
    {
        $validatedData = $request->validated();
        return User::searchPlanStatus($validatedData, $status);
    }


    public function getListLocation($form)
    {
        return User::getListLocation($form);
    }
}
