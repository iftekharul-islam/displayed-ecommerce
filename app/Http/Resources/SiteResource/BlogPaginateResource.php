<?php

namespace App\Http\Resources\SiteResource;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BlogPaginateResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $short_description = nullCheck($data->getTranslation('short_description',languageCheck()));
                if (strlen($short_description) > 100) {
                    $short_description = substr($short_description, 0, 100) . '...';
                }
                return [
                    'id'                    => $data->id,
                    'slug'                  => $data->slug,
                    'title'                 => $data->getTranslation('title',languageCheck()),
                    'short_description'     => $short_description,
                    'thumbnail'             => $data->thumbnail,
                ];
            }),

            'total'         => $this->total(),
            'count'         => $this->count(),
            'per_page'      => $this->perPage(),
            'current_page'  => $this->currentPage(),
            'total_pages'   => $this->lastPage(),
            'last_page'     => $this->lastPage(),
            'next_page_url' => $this->nextPageUrl(),
            'has_more_data' => $this->hasMorePages(),

        ];
    }
}
