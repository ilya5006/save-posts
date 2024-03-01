<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet;

class PostsSaverService
{
    public static function save(array $posts)
    {
        $spreadsheet = new PhpSpreadsheet\Spreadsheet();
        $writer = new PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $spreadsheet
            ->getActiveSheet()
            ->fromArray($posts);

        $writer->save(base_path() . '\posts.xlsx');
    }
}
