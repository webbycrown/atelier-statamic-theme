<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Statamic\Facades\Form;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function search(Request $request){   
        // Get the search keyword from the request
        $query = $request->get('s');

                    // Query blog entries from the 'blog' collection matching the title
        $entries = Entry::query()
        ->where('collection', 'blogs')
        ->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('title', 'like', "%{$query}%")
                ->orWhere('slug', 'like', "%{$query}%");
            });
        })
            ->orderBy('updated_at', 'desc') // Sort results by last updated
            ->get()
            ->map(function ($entry) {

                $image = $entry->get('image'); // Get image field (can be array of asset IDs)
                return [
                    'title' => $entry->get('title'),
                    'short_description' => $entry->get('short_description'),
                    'slug' => $entry->slug(),
                    'url' => $entry->url(),
                    'tag' => $entry->get('tag'),
                    'button' => [
                        'label' => $entry->get('button')['label'] ?? null,
                        'url'   => $entry->get('button')['url'] ?? null,
                    ],
                    // Convert asset paths to public URLs
                    'image' =>  collect($image)->map(function ($asset) {
                        return url('assets/'.$asset);
                    })->toArray(),
                ];
            });

        return response()->json($entries);  // Return as JSON response
    }


    public function headerSearch(Request $request)
    {   
        // Get query parameters from the request
        $query = $request->get('q', '');
        // 'blog', 'our_events', or 'our_services'
        $type = $request->get('type');
        // Whether to return full JSON results or view
        $full = $request->boolean('full');

         // Search in 'blogs' collection by title or slug, then remove duplicates by title
        $blogs = Entry::query()
        ->where('collection', 'blogs')
        ->where(fn($q) => $q->where('title', 'like', "%$query%")
            ->orWhere('slug', 'like', "%$query%"))
        ->get()
        ->unique('title')
        ->values();

        // Search in 'classes' collection by title or slug, then remove duplicates by title
        $classes = Entry::query()
        ->where('collection', 'classes')
        ->where(fn($q) => $q->where('title', 'like', "%$query%")
            ->orWhere('slug', 'like', "%$query%"))
        ->get()
        ->unique('title')
        ->values();

        // If full flag is true, return JSON with results only for the specified type
        if ($full) {
            return response()->json([
                'blogs' => $type === 'blogs' ? $blogs : [],
                'classes' => $type === 'classes' ? $classes : [],
                
            ]);
        }

        // Otherwise, return a partial view with all result types
        return view('partials.search-results', [
            'blogs' => $blogs,
            'classes' => $classes,
            'query' => $query,
        ]);
    }

   public function classesSearch(Request $request)
{
    $query = $request->get('s');

    $entries = Entry::query()
        ->where('collection', 'classes')
        ->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('title', 'like', "%{$query}%")
                         ->orWhere('slug', 'like', "%{$query}%");
            });
        })
        ->orderBy('updated_at', 'desc')
        ->get()
        ->map(function ($entry) {
            // return $entry;
            /* main image */
            $image = $entry->get('main_image');

            // make sure image is always an array
            $image = is_array($image) ? $image : [$image];

            /* team relation */
            $teamIds = $entry->get('team');
            $teamData = null;

            if (!empty($teamIds)) {
                $teamData = Entry::query()
                ->where('collection', 'teams')
                ->where('id', $teamIds ?? [])
                ->get()
                ->map(function ($team) {

                    $teamImage = $team->get('image');

                    return [
                        'title' => $team->get('title'),
                        'image' => collect($teamImage)
                    ->filter()
                    ->map(function ($asset) {
                        return url('assets/' . $asset);
                    })
                    ->values()
                    ->toArray()
                    ];
                })
                ->first();
            }

            return [
                'title' => $entry->get('title'),
                'short_description' => $entry->get('short_description'),
                'slug' => $entry->slug(),
                'url' => $entry->url(),
                'category' => $entry->get('category'),

                'intensity_level' => [
                    'sub_title' => $entry->get('intensity_level')['sub_title'] ?? null,
                    'intensity_label' => $entry->get('intensity_level')['intensity_label'] ?? null,
                    'replace_value' => str_replace('_', '-', $entry->get('intensity_level')['value'] ?? ''),
                    'value' =>  ucfirst(
                        str_replace(['_', '-'], ' ', $entry->get('intensity_level')['value'] ?? '')
                    ),
                ],

                'button' => [
                    'label' => $entry->get('button')['label'] ?? null,
                    'url'   => $entry->get('button')['url'] ?? null,
                ],

                'team' => $teamData,

                'image' => collect($image)
                    ->filter()
                    ->map(function ($asset) {
                        return url('assets/' . $asset);
                    })
                    ->values()
                    ->toArray(),
            ];
        });

    return response()->json($entries);
}
}
