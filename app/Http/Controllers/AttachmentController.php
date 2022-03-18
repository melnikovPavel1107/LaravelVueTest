<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;

class AttachmentController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function store(StoreAttachmentRequest $request)
    {


        $result = $this->csvToJson($request->attachment);
        $array = json_decode($result, true);

        $arrAveragePrice = array_column($array, 'average_price');
        $slice = array_slice($arrAveragePrice, 0, count($arrAveragePrice));
        $avgPrice = round(array_sum($slice) / sizeof($slice));
        $totalSold = array_sum(array_column($array, 'houses_sold'));
        $avgPriceYear = [];
        foreach ($array as $item) {
            $date = new \DateTime($item['date']);
            $date = $date->format('Y');
            if ($date == '2011') {
                if ($item['no_of_crimes'] != null) {
                    $crimes = !empty($crimes) ? $crimes + $item['no_of_crimes'] : $item['no_of_crimes'];
                }
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

    function csvToJson($fname)
    {
        // open csv file
        if (!($fp = fopen($fname, 'r'))) {
            return response()->json(["message" => "Can't open file..."]);
        }

        //read csv headers
        $key = fgetcsv($fp, "1024", ",");

        // parse csv rows into array
        $json = array();
        while ($row = fgetcsv($fp, "1024", ",")) {
            $json[] = array_combine($key, $row);
        }

        // release file handle
        fclose($fp);

        // encode array to json
        return json_encode($json);
    }
}
