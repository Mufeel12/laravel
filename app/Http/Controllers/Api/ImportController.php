<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Adapters\LookupVideo;
use App\Video;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * Search youtube / vimeo, return results
     *
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'order' => 'in:relevance,date,rating,title,viewCount',
            'source' => 'in:vimeo,youtube,any',
            'q' => 'string'
        ]);

        $q = $request->input('q');
        $source = $request->input('source', 'any');

        // Source var is exists, but empty
        if (empty($source)) {
            $source = 'any';
        }

        $order = $request->input('order', 'relevance');

        $pageToken = [
            'youtube' => $request->input('yt_nextPageToken', ''),
            'vimeo' => $request->input('vm_nextPageToken', '')
        ];

        if ($source === 'any') {
            $params['sources'] = [
                'youtube'
                // Temporarily disable vimeo from standard because it's slow
                #'vimeo', 'youtube'
            ];
        } else {
            $params['sources'] = [$source];
        }

        $params['order'] = $order;
        $params['max_results'] = 50;
        $params['page'] = $pageToken;

        if (empty($q)) {
            // Send error to view
            $results = [
                'error' => 'You must provide a search query.',
                'data' => []
            ];
        } else {
            try {
                $importVideo = new LookupVideo();
                $results = $importVideo->search($q, $params);
            } catch (\Exception $e) {
                #    echo $e->getMessage(); die();
            }
        }

        $results['query'] = $q;

        return $results;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return Response
     * @internal param int $id
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $project_id = $request->input('project_id');
        return Video::where('video_id', $id)
            ->where('filename', 'imported')
            ->where('project_id', $project_id)
            ->delete();
    }
}
