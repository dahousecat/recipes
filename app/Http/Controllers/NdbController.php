<?php

namespace App\Http\Controllers;

use App\Models\AttributeType;
use Illuminate\Http\Request;

class NdbController extends Controller
{
    private $apiKey = 'Om7a9m8XAvN0NzN8TpcuMXXcHZCvQlg4MvRL3BJJ';
    private $apiUrl = 'https://api.nal.usda.gov';
    private $term;

    public function search($term)
    {
        $this->term = $term;
        // TODO: Add caching

        $max = empty($_GET['group']) ? 25 : 50;

        $params = [
            'format' => 'json',
            'q' => $term,
            'sort' => 'r', // [r]elevance or [n]ame
            'max' => $max,
            'offset' => 0,
            'api_key' => $this->apiKey,
            'ds' => 'Standard Reference',
        ];
        $url = $this->apiUrl . '/ndb/search/?' . http_build_query($params);

        $output = $this->curl($url);

        $data = json_decode($output);

        $return['groups'] = [];

        if(!empty($data->list->item)) {
            foreach($data->list->item as $item) {
                if($item->ds == 'SR') {
                    if(empty($_GET['group']) || $_GET['group'] == $item->group) {
                        $return['groups'][$item->group][] = [
                            'name' => $item->name,
                            'id' => $item->ndbno,
                        ];
                    }
                }
            }
        }

        $this->sortGroups($return['groups']);

        return response()->json($return);

    }

    /**
     * Sort results by relevance to term
     *
     * @param $groups
     * @param $term
     */
    private function sortGroups(&$groups) {
        foreach($groups as $group => &$results) {
            foreach($results as &$result) {
                $this->scoreResult($result);
            }
            usort($results, [$this, 'sortResults']);
        }

        // Sort groups by their max score
        uasort($groups, [$this, 'sortGroupsByItemScores']);

        // Now remove scores
        foreach($groups as $group => &$results) {
            foreach($results as &$result) {
                unset($result['score']);
            }
        }
    }

    private static function sortGroupsByItemScores($a, $b) {
        $a_max = 0;
        foreach($a as $item) {
            $a_max = $item['score'] > $a_max ? $item['score'] : $a_max;
        }
        $b_max = 0;
        foreach($b as $item) {
            $b_max = $item['score'] > $b_max ? $item['score'] : $b_max;
        }
        return $a_max < $b_max;
    }

    private static function sortResults($a, $b) {
        return $a['score'] < $b['score'];
    }

    private function scoreResult(&$result) {
        $term = $this->term;
        $termLength = strlen($term);

        // Exact match?
        if($result['name'] == $term) {
            $result['score'] = 1;

        // Start exact then comma
        } elseif(substr($result['name'], 0, $termLength +1 ) == "$term,") {

            // Score decreases with longer length strings
            $max_score = 0.9;
            $min_score = 0.7;
            $max_chars = 50;
            $score_gap = $max_score - $min_score;
            $charsAfter = strlen($result['name']) - $termLength;

            if($charsAfter > $max_chars) {
                $result['score'] = $min_score;
            } else {
                $result['score'] = $max_score - (($charsAfter / $max_chars) * $score_gap);
            }

        // Start exact no comma
        } elseif(substr($result['name'], 0, $termLength) == $term) {

            // Score decreases with longer length strings
            $max_score = 0.6;
            $min_score = 0.4;
            $max_chars = 50;
            $score_gap = $max_score - $min_score;
            $charsAfter = strlen($result['name']) - $termLength;

            if($charsAfter > $max_chars) {
                $result['score'] = $min_score;
            } else {
                $result['score'] = $max_score - (($charsAfter / $max_chars) * $score_gap);
            }

        } else {

            // Score based on how far into string term appears
            $max_score = 0.3;
            $min_score = 0;
            $max_dist = 50;
            $score_gap = $max_score - $min_score;
            $distance = stripos($result['name'], $term);

            $result['distance'] = $distance;

            if($distance > $max_dist) {
                $result['score'] = $min_score;
            } else {
                $result['score'] = $max_score - (($distance / $max_dist) * $score_gap);
            }

        }

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
