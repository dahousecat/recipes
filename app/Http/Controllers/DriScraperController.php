<?php

namespace App\Http\Controllers;

class DriScraperController extends Controller
{
    private $url = 'https://www.nal.usda.gov/fnic/interactiveDRI/';

    public function test()
    {
        $inputs = $this->inputs();
        d($inputs);
        die(count($inputs));

        $data = $this->get_data($this->url);

        $token = $this->extractToken($data);

        die($token);

        return $data;
    }

    private function inputs() {

        $sexes = ['MALE', 'FEMALE'];

        $ages = [
            'YRS' => range(2, 50, 5),
            'MOS' => range(1, 12),
        ];

        $heights = range(150, 190, 5);
        $weights = range(45, 90, 5);

        $activities = ['Sedentary', 'Low Active', 'Active', 'Very Active'];

        $inputs = [];

        foreach($sexes as $sex) {
            foreach($ages as $ageType) {
                foreach($ageType as $age) {
                    foreach($heights as $height) {
                        foreach($weights as $weight) {
                            foreach($activities as $active) {
                                $inputs[] = [
                                    'sex' => $sex,
                                    'age' => $age,
                                    'ageType' => $ageType,
                                    'height' => $height,
                                    'weight' => $weight,
                                    'active' => $active,
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $inputs;

    }

    private function extractToken($data) {
        $parts = explode('<input type="hidden" name="token" value="', $data);
        $remaining = $parts[1];
        $parts = explode('" />', $remaining);
        $token = $parts[0];
        return $token;
    }

    private function get_data($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

