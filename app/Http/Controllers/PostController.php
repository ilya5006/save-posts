<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

use PhpOffice\PhpSpreadsheet;

class PostController extends Controller
{
    private const PER_PAGE = 70;

    private const POSTS_AMOUNT = 140;

    private const API_URL = 'https://techcrunch.com/wp-json/wp/v2/posts';

    public function savePosts(Request $request)
    {
        $request->validate([
            'file' => 'nullable|mimes:xlsx,xls,csv',
        ]);

        $posts = [];

        if (! isset($request->file)) {
            $pagesAmount = floor(self::POSTS_AMOUNT / self::PER_PAGE);

            for ($i = 1; $i <= $pagesAmount; $i++) {
                $response = Http::get(self::API_URL . '?per_page=' . self::PER_PAGE . '&page=' . $i)->json();

                foreach ($response as $postInfo) {
                    $posts[] = [
                        'title' => $postInfo['title']['rendered'], 
                        'body' => $postInfo['content']['rendered'], 
                        'created_at' => $postInfo['date']
                    ];
                }
            }

        } else {
            $file = $request->file('file');
 
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
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $posts = $this->formatPosts($posts);

        $spreadsheet
            ->getActiveSheet()
            ->fromArray($posts);

        $writer->save(base_path() . '\posts.xlsx');
    }

    private function formatPosts(array $posts): array
    {
        $formattedPosts = [];

        foreach ($posts as $post) {
            $formattedPosts[0][] = $post['title'];
            $formattedPosts[1][] = $post['body'];
        }

        return $formattedPosts;
    }
}
