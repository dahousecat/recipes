<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    public static function deleteMany($ids) {
        foreach($ids as $id) {
            $attribute = static::find($id);
            if($attribute) {
                $attribute->delete();
            }
        }
    }
}
