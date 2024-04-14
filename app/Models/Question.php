<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Question extends Model
{
    protected $table = 'question';
    protected $primaryKey = 'id';

    protected $guarded = [];


    public static function getQuestion($options = [], $isFirst = false){
        if(empty($options)){
            return Question::select('*')->orderBy('id')->get();
        }else{
            $query = Question::where('campaign_id', $options['campaign_id'])->orderBy('position');
            if($isFirst){
                $query = $query->first();
            }else{
                $query = $query->get();
            }
            return $query;
        }
    }

    public static function getQuestionNotAnswer($ids, $campaignId = '', $type = ''){
        $query = Question::select('*')->whereNotIn('id', $ids);

        if(!empty($type)){
            $query->where('type', $type);
        }
        if(!empty($campaignId)){
            $query->where('campaign_id', $campaignId);
            $query->orderBy('position');
        }

        $query = $query->get();
        return $query;
    }

    public static function getQuestionAnswer($campaignId, $type = ''){
        $query = DB::table('question AS q')->select('q.id', 'qa.question_id', 'qa.id as answer_id', 'qa.correct', 'qa.answer', 'q.name', 'q.position', 'q.point', 'q.msg', 'q.image')
            ->join('question_answer AS qa', 'qa.question_id', '=', 'q.id')
            ->where('q.campaign_id', $campaignId)
            ->where('qa.campaign_id', $campaignId)
            ->where('qa.correct', 1)
            ->orderBy('q.position')
            ->groupBy('qa.question_id');

        if(!empty($type)){
            $query->where('q.type', $type);
        }
        $query = $query->get();
        return $query;
    }

    public function answers()
    {
        return $this->hasMany('App\Models\QuestionAnswer', 'question_id', 'id');
    }

    public static function getQuestionByCondition($filters, $isFirst = false){
        $query = Question::where('campaign_id', $filters['campaign_id']);

        if(isset($filters['position']) && !empty($filters['position'])){
            $query->where('position', $filters['position']);
        }

        if(isset($filters['type']) && !empty($filters['type'])){
            $query->where('type', $filters['type']);
        }

        if(isset($filters['question_id']) && !empty($filters['question_id'])){
            $query->where('id', $filters['question_id']);
        }

        if($isFirst){
            $query = $query->first();
        }else{
            $query = $query->get();
        }
        return $query;
    }
}
