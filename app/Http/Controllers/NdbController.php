<?php

namespace App\Http\Controllers;

use App\Models\AttributeType;
use Illuminate\Http\Request;

class NdbController extends Controller
{
    private $apiKey = 'Om7a9m8XAvN0NzN8TpcuMXXcHZCvQlg4MvRL3BJJ';
    private $apiUrl = 'https://api.nal.usda.gov';

    public function search($term)
    {

        // TODO: Add caching

        $params = [
            'format' => 'json',
            'q' => $term,
            'sort' => 'r', // n
            'max' => 25,
            'offset' => 0,
            'api_key' => $this->apiKey,
            'ds' => 'Standard Reference',
        ];
        $url = $this->apiUrl . '/ndb/search/?' . http_build_query($params);

        $output = $this->curl($url);

        $data = json_decode($output);

        $return['groups'] = [];

        foreach($data->list->item as $item) {
            if($item->ds == 'SR') {
                $return['groups'][$item->group][] = [
                    'name' => $item->name,
                    'id' => $item->ndbno,
                ];
            }
        }

        return response()->json($return);

    }

    public function view($ndbno) {

        $params = [
            'ndbno' => $ndbno,
            'type' => 'b', // b: basic, f: full, s: stats
            'format' => 'json',
            'api_key' => $this->apiKey,
        ];
        $url = $this->apiUrl . '/ndb/V2/reports?' . http_build_query($params);

        $output = $this->curl($url);

        $data = json_decode($output);

        $attributes = AttributeType::all();

        $results = [];

        foreach($data->foods as $food) {
            foreach($food->food->nutrients as $nutrient) {
                foreach($attributes as $attribute) {
                    if($attribute->ndb_nutrient_id == $nutrient->nutrient_id) {

                        $results[] = [
                            'attribute_id' => $attribute->id,
                            'attribute_safe_name' => $attribute->safe_name,
                            'unit' => $nutrient->unit,
                            'value' => $nutrient->value,
                        ];
                        break;
                    }
                }
            }
        }

        return response()->json($results);

    }

    private function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
