<?php

# @Author: XuanDo
# @Date:   2018-02-08T20:49:12+07:00
# @Email:  ngocxuan2255@gmail.com
# @Last modified by:   Xuan Do
# @Last modified time: 2018-03-05T18:25:38+07:00
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    public $primaryKey = 'id';

    public $table = 'setting';

    public $guarded = [];

    protected $hidden = [];

    public static function getAll()
    {
        $setting = Setting::select('key', 'value')->get();
        $config = [];

        if (!empty($setting)) {
            foreach ($setting as $item) {
                $config[$item->key] = $item->value;
            }
        }
        return $config;
    }

    public function getValueByKey($key)
    {
        return Setting::where('key', $key)
            ->pluck('value')
            ->first();
    }
}
