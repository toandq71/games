<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    protected $table = 'question_answer';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getQuestionAnswer($options , $isFirst = false){
        $query = QuestionAnswer::select('*');

        if(isset($options['question_id']) && !empty($options['question_id'])){
            $query->where('question_id', $options['question_id']);
        }
        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('campaign_id', $options['campaign_id']);
        }
        if($isFirst){
            if(isset($options['id']) && !empty($options['id'])){
                $query->where('id', $options['id']);
            }
            $query = $query->first();
        }else{
            if(isset($options['ids']) && !empty($options['ids'])){
                $query->whereIn('id', $options['ids']);
            }
            $query = $query->orderBy('id')->get();
        }
        return $query;
    }

    public static function getQuestionAnswerCorrect(){
        return QuestionAnswer::select('*')->where('correct', '=', 1)->orderBy('question_id')->get();
    }

    public static function getAnswerCorrect($campaignId, $questionId, $isFirst = false){
        $query = QuestionAnswer::where('campaign_id', $campaignId)
            ->where('question_id', $questionId)
            ->where('correct', 1);
        if($isFirst){
            $query = $query->first();
        }else{
            $query = $query->get();
        }
        return $query;
    }
}
