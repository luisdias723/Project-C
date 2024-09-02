<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\Models\Question;
use App\Models\Condition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConditionController extends BaseController
{
    const ITEM_PER_PAGE = 25;

    /**
    * Display a listing of the user resource.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response|ResourceCollection
    */
    public function index(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  Condition $condition
     * @return ConditionResource|\Illuminate\Http\JsonResponse
     */
    public function show(Condition $condition)
    {
    }

    /**
        * Store a newly created resource in storage.
        *
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        */
    public function store(Request $request)
    {
       
    }

    /**
        * Update the specified resource in storage.
        *
        * @param Request $request
        * @return ConditionResource|\Illuminate\Http\JsonResponse
        */
    public function update(Request $request, Condition $condition)
    {
    }

    /**
        * Get all conditions to the selected question.
        *
        * @param Request $request
        */
    public function getConditions(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'id' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $qt_final = ['conditions' => []];
            $as_final = ['conditions' => []];
            $results = ['questions' => null, 'answers' => null];


            $answers = DB::table('answer_conditions')->where('question_id', $params['id'])->select('*')->get();
            $questions = DB::table('question_conditions')->where('question_id', $params['id'])->select('*')->get();

            foreach($questions as $question) {
                $qt_final['status'] = $question->status;
                $data = Condition::where('id', '=', $question->condition_id)
                    ->where('formulario_id', '=', $params['form'])
                    ->select('*')
                    ->get();

                foreach($data as $dt) {
                    $result = [];
                    $result['rule'] = $dt->rule;
                    $result['answer'] = $dt->answer_id;
                    $result['select_condition'] = $dt->select_condition == '&&' ? 'E' : 'OU';

                    $question = Question::find($dt->question_id);
                    $result['selectedQuestion'] = $question;
                    array_push($qt_final['conditions'], $result);
                }
            }
            $results['questions'] = $qt_final;


            foreach($answers as $answer) {
                $data = Condition::where('id', '=', $answer->condition_id)
                    ->where('formulario_id', '=', $params['form'])
                    ->select('*')->get();
                foreach($data as $dt) {
                    $result = [];
                    $result['status'] = $answer->status;
                    $result['answer'] = isset($answer->answer_id) ? intval($answer->answer_id) : '';
                    $result['rule'] = $dt->rule;
                    $result['selectedAnswer'] = isset($dt->answer_id) ? intval($dt->answer_id) : '';

                    $question = Question::find($dt->question_id);
                    $result['selectedQuestion'] = $question;
                    array_push($as_final['conditions'], $result);
                }
            }
            $results['answers'] = $as_final;

            return $results;
            // echo "<pre>", print_r($answers);
            // dd('teste');
        }
    }
}
