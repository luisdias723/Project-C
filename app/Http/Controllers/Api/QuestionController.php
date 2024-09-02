<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Answer;
use App\Models\AnswerBoard;
use Validator;
use Illuminate\Support\Facades\DB;

class QuestionController extends BaseController
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
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $role = Arr::get($searchParams, 'role', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        $rating_filter = Arr::get($searchParams, 'rating', '');
        $active = Arr::get($searchParams, 'active', '');

        $queryData = Question::where('active', 1)->get();

        foreach ($queryData as &$query) {
            // If query is special, means that have to select boards
            // If query is specialBoards, means that users have to select trajes that belongs to selected board
            $query->isTraje = $query->isTraje == 1 ? true : false;
            $query->isSpecial = $query->isSpecial == 1 ? true : false;
            $query->isSpecialBoards = $query->isSpecialBoards == 1 ? true : false;
            if ($query->isSpecial) {
                $answerId = Answer::where('question_id', $query->id)->pluck('id')->first();
                $answer_values = DB::table('answer_boards')
                                ->leftJoin('quadros', 'answer_boards.board_id', 'quadros.id')
                                ->where('answer_boards.answer_id', $answerId);
                $query->questions = $answer_values->select('quadros.*')->get();
                $answer_values = $answer_values->pluck('quadros.description')->toArray();
                if(count($answer_values) > 0) {
                    $answer_values = implode(', ', $answer_values);
                    $query->answers_string = $answer_values;
                }
            }
            if ($query->isSpecialBoards) {

            }
            if (!$query->isSpecial && !$query->isSpecialBoards) {
                $answers = Answer::where('question_id', $query->id)->pluck('description')->toArray();
                $answers_values = Answer::where('question_id', $query->id)->select('description')->get();
                if(count($answers) > 0) {
                    $answers = implode(', ', $answers);
                    $query->answers_string = $answers;
                } else {
                    $query->answers_string = null;
                }
                $query->answers = $answers_values;
            }
        }

        return $queryData;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'description' => 'required',
                    'type_question' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $question = Question::create(
                [
                    'description' => $params['description'],
                    'type_question' => $params['type_question'],
                    'isMultiple' => $params['isMultiple'] ?? 0,
                    'isSpecial' => $params['isSpecial'] ?? 0,
                    'isSpecialBoards' => $params['isSpecialBoards'] ?? 0,
                    'isTraje' => $params['isTraje'] ?? 0,
                ]
            );

            if (isset($params['answers']) && (!isset($params['isSpecialBoards']) || !$params['isSpecialBoards'])) {
                foreach ($params['answers'] as $answer) {
                    if (!empty($answer['description'])) {
                        $answers = Answer::create(
                            [
                                'description' => $answer['description'],
                                'question_id' => $question->id,
                            ]
                        );
                    }
                }
            }

            if (isset($params['isSpecial']) && $params['isSpecial']) {
                // Indica que a resposta deve ser um dos quadros, ou seja está armazenada na variavel questions
                $answer_special = Answer::create(
                    [
                        'description' => '',
                        'question_id' => $question->id,
                        'isBoard' => 1
                    ]
                );

                foreach($params['questions'] as $boards_selected) {
                    $answer_board = DB::table('answer_boards')->insert([
                        [
                            'board_id' => $boards_selected['id'],
                            'answer_id' => $answer_special->id,
                        ]
                    ]);
                }
            }

            return new QuestionResource($question);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Question $question)
    {
        if ($question === null) {
            return response()->json(['error' => 'Não foi possível atualizar o registo solicitado'], 404);
        }

        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'description' => 'required',
                    'type_question' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();

            $question->description = $request->get('description');
            $question->type_question = $request->get('type_question');
            $question->num_digits = $request->get('num_digits');
            $question->isMultiple = $request->get('isMultiple') ?? 0;
            $question->isSpecial = $request->get('isSpecial') ?? 0;
            $question->isSpecialBoards = $request->get('isSpecialBoards') ?? 0;
            $question->isTraje = $request->get('isTraje') ?? 0;

            Answer::where('question_id', $question->id)->delete();

            if (isset($params['isSpecial']) && $params['isSpecial']) {
                unset($params['answers']);
            }

            if (isset($params['answers']) && (!isset($params['isSpecialBoards']) || !$params['isSpecialBoards'])) {
                foreach ($params['answers'] as $answer) {
                    if (!empty($answer['description'])) {
                        $answers = Answer::create(
                            [
                                'description' => $answer['description'],
                                'question_id' => $question->id,
                            ]
                        );
                    }
                }
            }

            if (isset($params['isSpecial']) && $params['isSpecial']) {
                // Indica que a resposta deve ser um dos quadros, ou seja está armazenada na variavel questions
                $answer_special = Answer::create(
                    [
                        'description' => '',
                        'question_id' => $question->id,
                        'isBoard' => 1
                    ]
                );

                foreach($params['questions'] as $boards_selected) {
                    $answer_board = DB::table('answer_boards')->insert([
                        [
                            'board_id' => $boards_selected['id'],
                            'answer_id' => $answer_special->id,
                        ]
                    ]);
                }
            }

            $question->update();
            return new QuestionResource($question);
        }
    }


    /**
     * Update the active value of questions
     *
     * @param Request $request
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function changeVisibility(Request $request)
    {
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
            $question = Question::find($request->id);

            if (!isset($question) || empty($question)) {
                return response()->json(['errors' => 'Não foi possível atualizar o registo'], 403);
            }
            $active = !$question->active;

            $updated = Question::where('id', $question->id)
            ->update([
                'active' => $active
            ]);

            return $updated;
        }
    }
}
