<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;

class AttachmentController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function store(StoreAttachmentRequest $request)
    {
        $array = $this->csvToArray($request->attachment);

        $arrAveragePrice = array_column($array, 'average_price');
        $slice = array_slice($arrAveragePrice, 0, count($arrAveragePrice));
        $avgPrice = round(array_sum($slice) / sizeof($slice));
        $totalSold = array_sum(array_column($array, 'houses_sold'));
        $avgPriceYear = [];
        $crimes = 0;

        foreach ($array as $item) {
            $date = new \DateTime($item['date']);
            $date = $date->format('Y');

            if ($date == '2011' && !empty($item['no_of_crimes'])) {
                $crimes =+ $item['no_of_crimes'];
            }

            if (!empty($avgPriceYear[$date])) {
                $avgPriceYear[$date]['sum'] = $avgPriceYear[$date]['sum'] + $item['average_price'];
                $avgPriceYear[$date]['count'] = $avgPriceYear[$date]['count']+1;
            } else {
                $avgPriceYear[$date]['sum'] = $item['average_price'];
                $avgPriceYear[$date]['count'] = 1;
            }
        }
        foreach ($avgPriceYear as $year => $value){
            $avgPriceYear[$year] = round($value['sum'] / $value['count']);
        }
        if ($request->saveDB == 'true') {
            $attachment = new Attachment;
            $attachment->path = $attachment->upload($request->attachment);
            $attachment->save();
        }

        return response()->json([
            'avgPrice' => $avgPrice,
            'totalSold' => $totalSold,
            'crimes' => $crimes,
            'avgPriceYear' => $avgPriceYear,
        ]);

    }

    function csvToArray($fname)
    {
        // open csv file
        if (!($fp = fopen($fname, 'r'))) {
            return response()->json(["message" => "Can't open file..."]);
        }

        //read csv headers
        $key = fgetcsv($fp, "1024", ",");

        // parse csv rows into array
        $array = [];

        while ($row = fgetcsv($fp, "1024", ",")) {
            $array[] = array_combine($key, $row);
        }

        fclose($fp);

        return $array;
    }
}
