<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const ML_IN_CUP = 240;

    /**
     * @param $inArray
     * @param $pathToKey
     * @return array
     */
    protected function keyArray($inArray, $pathToKey) {
        $outArray = [];
        $pathToKey = is_array($pathToKey) ? $pathToKey : [$pathToKey];
        foreach($inArray as $value) {
            $ref = $value;
            foreach($pathToKey as $pathElement) {
                if(!isset($ref[$pathElement])) {
                    print_r(debug_backtrace());
                    trigger_error('Key ' . $pathElement . ' is not set in this array: ' . print_r($ref, true));
                } else {
                    $ref = $ref[$pathElement];
                }
            }
            $outArray[$ref] = $value;
        }
        return $outArray;
    }
}
