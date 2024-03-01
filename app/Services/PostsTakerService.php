<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

use PhpOffice\PhpSpreadsheet;

class PostsTakerService
{
    private const PER_PAGE = 70;

    private const POSTS_AMOUNT = 140;

    private const API_URL = 'https://techcrunch.com/wp-json/wp/v2/posts';

    public static function takeFromApi(): array
    {
        $pagesAmount = floor(self::POSTS_AMOUNT / self::PER_PAGE);

        $posts = [];

        for ($i = 1; $i <= $pagesAmount; $i++) {
            $response = Http::get(self::API_URL . '?per_page=' . self::PER_PAGE . '&page=' . $i);

            if (!$response->ok()) {
                break;
            }

            $responsePosts = $response->json();

            foreach ($responsePosts as $postInfo) {
                $posts[] = [
                    'title' => $postInfo['title']['rendered'], 
                    'body' => $postInfo['content']['rendered'], 
                    'created_at' => $postInfo['date']
                ];
            }
        }

        return self::formatPosts($posts);
    }

    public static function takeFromFile($file): array
    {
        $posts = [];
        
        $reader = new PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getActiveSheet();
    
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            $charToColumn = [
                'A' => 'title',
                'B' => 'body',
                'C' => 'created_at'
            ];

            foreach ($cellIterator as $column => $cell) {
                $post[$charToColumn[$column]] = $cell->getValue();
            }

            $posts[] = $post;
        }
        
        $posts = collect($posts)->sortByDesc('created_at')->toArray();

        return self::formatPosts($posts);
    }

    private static function formatPosts(array $posts): array
    {
        $formattedPosts = [];

        foreach ($posts as $post) {
            $formattedPosts[0][] = $post['title'];
            $formattedPosts[1][] = $post['body'];
        }

        return $formattedPosts;
    }
}
