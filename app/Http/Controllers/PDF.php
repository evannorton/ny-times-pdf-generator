<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Fpdf;

class PDF extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request['feed']) {
            header('Content-type: application/pdf');
            $xml_url = $request['feed'];
            $xml_string = file_get_contents($xml_url);
            xml_parse_into_struct(xml_parser_create(), $xml_string, $values, $index);
            $current_page = 0;
            // for each value in xml
            foreach ($values as $value) {
                // if new item
                if ($value['tag'] === 'ITEM' && $value['type'] === "open") {
                    $current_page++;
                    Fpdf::AddPage();
                }
                // if article title
                else if ($value['tag'] === 'TITLE' && $current_page >= 1) {
                    Fpdf::SetFont('Arial', 'B', 20);
                    Fpdf::MultiCell(180, 7, iconv('UTF-8', 'windows-1252', $value['value']));
                }
                // if description
                else if ($value['tag'] === 'DESCRIPTION' && $current_page >= 1) {
                    Fpdf::Ln(10);
                    Fpdf::SetFont('Arial', 'B', 12);
                    Fpdf::MultiCell(180, 5, iconv('UTF-8', 'windows-1252', $value['value']));
                }
                // if image
                else if ($value['tag'] === 'MEDIA:CONTENT') {
                    Fpdf::Ln(10);
                    Fpdf::Image($value['attributes']['URL']);
                }
            }
            Fpdf::Output();
            exit;
        } else return view('form');
    }
}
