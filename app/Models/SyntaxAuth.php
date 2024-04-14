<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyntaxAuth extends Model
{
    protected $table = 'syntax_auth';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getSyntax($syntax){
        return SyntaxAuth::where('syntax', $syntax)->first();
    }
}
