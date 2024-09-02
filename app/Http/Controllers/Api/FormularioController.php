<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Resources\FormularioResource;
use App\Mail\editedRegistry;
use App\Models\Answer;
use App\Models\Formulario;
use App\Models\FormularioQuestion;
use App\Models\Condition;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Country;
use App\Models\District;
use App\Models\Concelho;
use App\Models\Freguesia;
use App\Models\PhoneZones;
use App\Models\Question;
use App\Models\Registration;
use App\Models\Traje;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\newRegistry;
use App\Mail\holdRegistry;
use App\Mail\newRegistryCortejo;
use App\Mail\holdCortejoRegistry;
use App\Mail\updatedRegistry;
use App\Models\Quadro;
use App\Models\Rancho;

class FormularioController extends BaseController
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
        $keyword = Arr::get($searchParams, 'keyword', '');
        $active = Arr::get($searchParams, 'active', '');

        $queryData = Formulario::get();

        foreach ($queryData as &$query) {
            $newData = Formulario::leftJoin('formulario_questions', 'formularios.id', 'formulario_questions.formulario_id')
                    ->leftJoin('questions', 'formulario_questions.question_id', 'questions.id')->where('formularios.id', '=', $query->id);
            $query->questions_array = $newData->pluck('questions.description');
            $query->questions = $newData->select('questions.*', 'formulario_questions.mandatory')->get();
            
            $query->questions = $this->getAllAnswers($query->questions);
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
                    'name' => 'required',
                    'questions' => 'required|array'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $form = Formulario::create(
                [
                    'name' => $params['name'],
                ]
            );

            foreach ($params['questions'] as $question) {
                $form_questions = DB::table('formulario_questions')
                    ->insert(
                    [
                        'formulario_id' => $form['id'],
                        'question_id' => $question['id'],
                    ]
                );
            }

            return new FormularioResource($form);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return FormularioResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Formulario $formulario)
    {
        if ($formulario === null) {
            return response()->json(['error' => 'Não foi possível atualizar o registo solicitado'], 404);
        }

        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'name' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();

            $formulario->name = $request->get('name');

            $formulario->update();

            DB::table('formulario_questions')->where('formulario_id', $formulario->id)->delete();

            foreach ($params['questions'] as $question) {
                $form_questions = DB::table('formulario_questions')
                    ->insert(
                    [
                        'formulario_id' => $formulario->id,
                        'question_id' => $question['id'],
                    ]
                );
            }

            return new FormularioResource($formulario);
        }
    }

     /**
     * Display the specified resource.
     *
     * @param  Formulario $formulario
     * @return FormularioResource|\Illuminate\Http\JsonResponse
     */
    public function show(Formulario $formulario)
    {
        $result = [];

        $newData = Formulario::leftJoin('formulario_questions', 'formularios.id', 'formulario_questions.formulario_id')
                    ->leftJoin('questions', 'formulario_questions.question_id', 'questions.id')->where('formularios.id', '=', $formulario->id)->where('questions.active', 1);
        $result = $newData->select('questions.*', 'formulario_questions.mandatory')->get();
            
        $result = $this->getFormAnswers($result);

        $result = $this->getFormConditions($result, $formulario->id);

        return $result;
    }

    /**
     * Display the specified resource, by id
     *
     * @param  $id
     * @return FormularioResource|\Illuminate\Http\JsonResponse
     */
    public function getForm($id)
    {
        if (!isset($id) || empty($id)){
            return response()->json(['errors' => 'Não foi possível obter o formulário. Deve contactar a organização'], 403);
        }
        $form = Formulario::find($id);

        if (!isset($form) || empty($form)){
            return response()->json(['errors' => 'Não foi possível obter o formulário. Deve contactar a organização'], 403);
        }

        $result = [];

        $newData = Formulario::leftJoin('formulario_questions', 'formularios.id', 'formulario_questions.formulario_id')
                    ->leftJoin('questions', 'formulario_questions.question_id', 'questions.id')->where('formularios.id', '=', $id)->where('questions.active', 1);
        $result = $newData->select('questions.*', 'formulario_questions.mandatory')->get();
            
        $result = $this->getFormAnswers($result);

        $result = $this->getFormConditions($result, $id);

        return $result;
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
            $form = Formulario::find($request->id);

            if (!isset($form) || empty($form)) {
                return response()->json(['errors' => 'Não foi possível atualizar o registo'], 403);
            }
            $active = !$form->active;

            $updated = Formulario::where('id', $form->id)
            ->update([
                'active' => $active
            ]);

            return $updated;
        }
    }

    private function getAllAnswers(&$questions) {
        foreach ($questions as $question) {
            $question->mandatory = $question->mandatory === 1 ? true : false;
            $answers = Answer::where('question_id', $question->id)->get();
            $question->answers = $answers;
            foreach ($answers as &$answer) {
                if (isset($answer->isBoard) && $answer->isBoard) {
                    $answer_boards = DB::table('answer_boards')
                        ->leftJoin('trajes_quadros', 'answer_boards.board_id', '=', 'trajes_quadros.board_id')
                        ->leftJoin('quadros', 'answer_boards.board_id', '=', 'quadros.id') // Certifique-se de que a tabela de quadros está incluída
                        ->where('answer_boards.answer_id', $answer->id)
                        ->select('quadros.*')
                        ->get();

                    $question->answers = $answer_boards;
                }
            }
            // if my question is country or district this means that my answers will be one of that selected from tables
            if ($question->type_question == 'country') {
                $country = Country::all();
                $question->answers = $country;
            }
            if ($question->type_question == 'district') {
                $district = District::all();
                $question->answers = $district;
            }
            if ($question->type_question == 'concelho') {
                $concelhos = Concelho::all();
                $question->answers = $concelhos;
            }
            if ($question->type_question == 'freguesia') {
                $freguesias = Freguesia::all();
                $question->answers = $freguesias;
            }
            if ($question->type_question == 'rancho') {
                $rancho = Rancho::all();
                $question->answers = $rancho;
            }
            if ($question->isTraje) {
                $trajes = Traje::where('active', 1)->get();
                $question->answers = $trajes;
            }
        }
        return $questions;
    }

    /**
     * Function to get all questions with their conditions
     *
     * @param Request $request
     */
    private function getFormAnswers(&$questions) {
        foreach ($questions as $question) {
            $question->mandatory = $question->mandatory === 1 ? true : false;
            $answers = Answer::where('question_id', $question->id)->get();
            $question->answers = $answers;
            foreach ($answers as &$answer) {
                if (isset($answer->isBoard) && $answer->isBoard) {
                    $answer_boards = DB::table('answer_boards')
                        ->leftJoin('trajes_quadros', 'answer_boards.board_id', '=', 'trajes_quadros.board_id')
                        ->leftJoin('quadros', 'answer_boards.board_id', '=', 'quadros.id') // Certifique-se de que a tabela de quadros está incluída
                        ->where('answer_boards.answer_id', $answer->id)
                        ->select('quadros.*')
                        ->distinct()
                        ->get();

                    $question->answers = $answer_boards;
                }
            }
            if (isset($question->isSpecialBoards) && $question->isSpecialBoards) {
                $quadros = Quadro::all();
                $values = array();
                foreach ($quadros as $quadro) {
                    $results = DB::table('trajes')
                        ->join('trajes_quadros', 'trajes.id', '=', 'trajes_quadros.traje_id')
                        ->where('trajes_quadros.board_id', $quadro->id)
                        ->select('trajes.*')
                        ->get();
                    $values[$quadro->id] = $results;
                }
                $question->answers = $values;
            }
            // if my question is country or district this means that my answers will be one of that selected from tables
            if ($question->type_question == 'country') {
                $country = Country::all();
                $question->answers = $country;
            }
            if ($question->type_question == 'district') {
                $district = District::all();
                $question->answers = $district;
            }
            if ($question->type_question == 'freguesia') {
                $freguesia = Freguesia::all();
                $question->answers = $freguesia;
            }
            if ($question->type_question == 'concelho') {
                $concelho = Concelho::all();
                $question->answers = $concelho;
            }
            if ($question->type_question == 'rancho') {
                $rancho = Rancho::all()->map(function($item) {
                    return ['value' => $item->description];
                });
                $question->answers = $rancho;
            }
            if ($question->isTraje) {
                $trajes = Traje::where('active', 1)->get();
                $question->answers = $trajes;
            }
        }
        return $questions;
    }

    /**
     * Function to get all questions with their conditions
     *
     * @param Request $request
     */
    private function getFormConditions(&$questions, $form_id) {
        foreach ($questions as $question) {
            $conditions = array();
            $q_conds = DB::table('question_conditions')
                ->leftJoin('conditions', 'question_conditions.condition_id', 'conditions.id')
                ->where('question_conditions.question_id', $question->id)
                ->where('conditions.formulario_id', $form_id)
                ->select('question_conditions.*')->get();
            if (count($q_conds) > 0) {
                foreach ($q_conds as $index => $q_cond) {
                    $question->condition_status = $q_cond->status;
                    $condition = Condition::find($q_cond->condition_id);
                    $value = array();
                    if (isset($condition) && isset($condition->id)) {
                        $value['question_id'] = $condition->question_id;
                        $value['rule'] = $condition->rule;
                        $value['answer_id'] = $condition->answer_id;
                        $value['status'] = $q_cond->status;
                        if ($index > 0) $value['concat_condition'] = $condition->select_condition;
    
                        array_push($conditions, $value);
                    }
                }
            }
            $question['conditions'] = $conditions;
        }
        return $questions;
    }

    
    /**
     * Insert or Update the conditions of questions
     *
     * @param Request $request
     */
    public function saveConditions (Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'id' => 'required',
                    'form' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $inserted = false;
            // Get all records from conditions so we can delete the correct records
            $question_conditions = DB::table('question_conditions')
                                    ->leftJoin('conditions', 'question_conditions.condition_id', 'conditions.id')                        
                                    ->where('question_conditions.question_id', $params['id'])
                                    ->where('conditions.formulario_id', $params['form'])
                                    ->pluck('condition_id')
                                    ->toArray();
            // $answer_conditions = DB::table('answer_conditions')->where('question_id', $params['id'])->pluck('condition_id')->toArray();
            // Delete the records from conditions where id is equal to previous sql query
            // Get the conditions id's to delete in the question_conditions
            DB::table('question_conditions')
                            ->leftJoin('conditions', 'question_conditions.condition_id', 'conditions.id')                        
                            ->where('question_conditions.question_id', $params['id'])
                            ->where('conditions.formulario_id', $params['form'])
                            ->delete();
                            
            Condition::whereIn('id', $question_conditions)->where('formulario_id', $params['form'])->delete();
            
            if (isset($params['question']) && !empty($params['question']) && isset($params['question']['conditions']) && !empty($params['question']['conditions'])){
                foreach ($params['question']['conditions'] as $question) {
                    $condition = Condition::create([
                        'question_id' => $question['question'],
                        'answer_id' => $question['answer'],
                        'rule' => $question['rule'],
                        'select_condition' => $question['select_condition'],
                        'type_question' => 1,
                        'formulario_id' => $params['form']
                    ]);
                    DB::table('question_conditions')->insert([
                        'question_id' => $params['id'],
                        'condition_id' => $condition['id'],
                        'status' => $params['question']['status']
                    ]);
                    if (isset($condition->id)) {
                        $inserted = true;
                    }
                }
            }
            if (isset($params['answers']) && !empty($params['answers']) && isset($params['answers']['conditions']) && !empty($params['answers']['conditions'])){
                foreach ($params['answers']['conditions'] as $answers) {
                    $condition = Condition::create([
                        'question_id' => $answers['question'],
                        'answer_id' => $answers['selectedAnswer'],
                        'rule' => $answers['rule'],
                        'type_question' => 0,
                        'formulario_id' => $params['form']
                    ]);
                    DB::table('answer_conditions')->insert([
                        'question_id' => $params['answers']['currentQuestion'],
                        'answer_id' => $answers['answerToApply'],
                        'condition_id' => $condition['id'],
                        'status' => $answers['status']
                    ]);
                    if (isset($condition->id)) {
                        $inserted = true;
                    }
                }
            }
            return $inserted;
        }
    }

    /**
     * Insert or Update the answers given by users in the form
     *
     * @param Request $request
     */
    public function saveAnswers (Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'answer' => 'required',
                    'checklist' => 'required',
                    'files' => 'required'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            // Before do something we have to check if email is already registered 3 times or if the cc has already been used
            foreach ($params['answer'] as $question_id => $answer) {
                $qt = Question::find($question_id);
                if ($qt->type_question == 'email') {
                    $email_count = Registration::where('email', $answer)->count();
                    if ($email_count >= 3) {
                        return response()->json(['error' => 'Este email já foi utilizado para 3 inscrições. Utilize outro, por favor.'], 403);
                    }
                }
                if ($qt->type_question == 'number' && ((strpos($qt->description, "CC") !== false && $answer != 'null' && $answer != '') || (strpos($qt->description, "Passaporte") !== false && $answer != 'null' && $answer != ''))) {
                    $id_num = DB::table('registration_answers')
                    ->join('registrations', 'registration_answers.registration_id', '=', 'registrations.id')
                    ->where('question_id', $question_id)
                    ->where('answer', $answer)
                    ->where('registrations.formulario_id', 1)
                    ->count();
                    if ($id_num > 0) {
                        $id_text = 'CC';
                        if (strpos($qt->description, "Passaporte") !== false) {
                            $id_text = 'Passaporte';
                        }
                        return response()->json(['error' => 'O número de ' . $id_text . ' já foi utilizado noutra inscrição.'], 403);
                    }
                }
            }

            // Get status id
            $status_id = DB::table('registration_statuses')->where('description', 'Em análise')->pluck('id')->first();
            $reg_id = Registration::create(
                [
                    'code' => 1,
                    'status_id' => $status_id,
                    'formulario_id' => $params['checklist']
                ]
            );
            $code = '#' . (1065 + intval($reg_id['id']));
            $img_code =  (1065 + intval($reg_id['id']));
            Registration::where('id', $reg_id['id'])->update([
                'code' => $code
            ]);

            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Receção de nova inscrição.'
            ]);
            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Estado alterado para "Em análise".'
            ]);

            $userEmail = '';
            
            foreach ($params['answer'] as $question_id => $answer) {
                $qt = Question::find($question_id);
                if ($qt->type_question == 'textarea') {
                    $obs = DB::table('registration_answers')->where('registration_id', $reg_id['id'])
                    ->where('question_id', $question_id)->pluck('answer')->first();
                    if ($answer != 'null' && $answer != null && $answer != ''){
                        $obs = $obs . "\n" . $answer;
                    } else {
                        $obs = '';
                    }
                    DB::table('registration_answers')->insert([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => $obs
                    ]);
                    if ($answer != 'null' && $answer != null && $answer != '') {
                        DB::table('history_registrations')->insert([
                            'registration_id' => $reg_id['id'],
                            'description' => 'Obs - ' . $answer
                        ]);
                    }
                } else {
                    DB::table('registration_answers')->insert([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => $answer
                    ]);
                    if ($qt->type_question == 'email') {
                        Registration::where('id', $reg_id['id'])->update([
                            'email' => $answer
                        ]);
                        $userEmail = $answer;
                    }
                }
            }

            $files = $request->file('files');
            foreach ($files as $question_id => $uploadedFiles) {
                $reg_anser = DB::table('registration_answers')->insertGetId([
                    'registration_id' => $reg_id['id'],
                    'question_id' => $question_id,
                    'answer' => ''
                ]);
                foreach ($uploadedFiles as $index=>$file) { 
                    // Gere um nome único para o ficheiro
                    $extension = $file->getClientOriginalExtension();
                    $name = '/Mordomia/' . $img_code .'_' . $index . '.' . $extension;
                    // Armazene o ficheiro no disco público
                    // Storage::disk('public')->put($name, file_get_contents($file));
                    $file->storePubliclyAs('/', $name, 'public');
                    
                    DB::table('answer_images')->insert([
                        'answer_id' => $reg_anser,
                        'path' => $name
                    ]);
                }
            }
            if ($code){
                // Mail para o admin
                // Mail::to('luis.dias@hovo.pt')
                //     ->bcc('diogo.freire@hovo.pt')
                //     ->send(new newRegistry($code));

                // Mail para o cliente
                // Mail::to('luis.dias@hovo.pt')
                //     ->bcc('diogo.freire@hovo.pt')
                //     ->send(new holdRegistry($code));
                // return true;

                Mail::to('mordomia@vianafestas.com')
                    ->bcc('no-reply@vianafestas.com')
                    ->send(new newRegistry($code));

                // Mail para o cliente
                Mail::to($userEmail)
                    ->bcc('no-reply@vianafestas.com')
                    ->send(new holdRegistry($code));
                return true;
            }
            return false;
        }
    }


    /**
     * Insert or Update the answers given by users in the Cortejo form
     *
     * @param Request $request
     */
    public function saveCortejoAnswers (Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'answer' => 'required',
                    'files' => 'required',
                    'checklist' => 'required',
                    'btn' => 'required',
                    'participants' => 'required_if:btn,2,3',
                ]
            )
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $board_id = null;
            // Before do something we have to check if email is already registered 3 times or if the cc has already been used
            foreach ($params['answer'] as $question_id => $answer) {
                $qt = Question::find($question_id);
                if ($qt->type_question == 'email') {
                    $email_count = Registration::where('email', $answer)->where('formulario_id', $params['checklist'])->count();
                    if ($email_count >= 3) {
                        return response()->json(['error' => 'Este email já foi utilizado para 3 inscrições. Utilize outro, por favor.'], 403);
                    }
                }
                if ($qt->type_question == 'number' && 
                    ((strpos($qt->description, "CC") !== false && $answer != 'null' && $answer != '') || 
                    (strpos($qt->description, "Passaporte") !== false && $answer != 'null' && $answer != ''))) {
                    
                    $id_num = DB::table('registration_answers')
                        ->join('registrations', 'registration_answers.registration_id', '=', 'registrations.id')
                        ->where('registration_answers.question_id', $question_id)
                        ->where('registration_answers.answer', $answer)
                        ->where('registrations.formulario_id', 2)
                        ->count();

                    if ($id_num > 0) {
                        $id_text = 'CC';
                        if (strpos($qt->description, "Passaporte") !== false) {
                            $id_text = 'Passaporte';
                        }
                        return response()->json(['error' => 'O número de ' . $id_text . ' já foi utilizado noutra inscrição.'], 403);
                    }
                }
                if ($qt->isSpecial && $answer != 'null' && $answer != '') {
                    $board_id = $answer;
                }
            }
            
            // At least exists one participant
            $count_participant = 1;

            if (isset($params['participants']) && !empty($params['participants'])) {
                $count_participant += count($params['participants']);
            }

            // Update the number of participants
            $cur_value_count = DB::table('quadros')->where('id', $board_id)->first();
            $cur_count = intval($cur_value_count->total_insc) + intval($count_participant);
            DB::table('quadros')->where('id', $board_id)->update([
                'total_insc' => $cur_count
            ]);

            $type_reg = $params['btn'];

            // Get status id
            $status_id = DB::table('registration_statuses')->where('description', 'Em análise')->pluck('id')->first();
            $reg_id = Registration::create(
                [
                    'code' => 1,
                    'status_id' => $status_id,
                    'formulario_id' => $params['checklist'],
                    'type' => $type_reg
                ]
            );
            $code = '#' . (1065 + intval($reg_id['id']));
            $img_code =  (1065 + intval($reg_id['id']));
            Registration::where('id', $reg_id['id'])->update([
                'code' => $code
            ]);

            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Receção de nova inscrição.'
            ]);
            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Estado alterado para "Em análise".'
            ]);

            $userEmail = '';
            
            foreach ($params['answer'] as $question_id => $answer) {
                $qt = Question::find($question_id);
                if ($qt->type_question == 'textarea') {
                    $obs = DB::table('registration_answers')->where('registration_id', $reg_id['id'])
                    ->where('question_id', $question_id)->pluck('answer')->first();
                    if ($answer != 'null' && $answer != null && $answer != ''){
                        $obs = $obs . "\n" . $answer;
                    } else {
                        $obs = '';
                    }
                    DB::table('registration_answers')->insert([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => $obs
                    ]);
                    if ($answer != 'null' && $answer != null && $answer != '') {
                        DB::table('history_registrations')->insert([
                            'registration_id' => $reg_id['id'],
                            'description' => 'Obs - ' . $answer
                        ]);
                    }
                } else {
                    DB::table('registration_answers')->insert([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => $answer
                    ]);
                    if ($qt->type_question == 'email') {
                        Registration::where('id', $reg_id['id'])->update([
                            'email' => $answer
                        ]);
                        $userEmail = $answer;
                    }
                }
            }

            if (isset($params['participants']) && !empty($params['participants'])) {
                foreach($params['participants'] as $key_participant => $participant) {
                    foreach ($participant as $qt_participant=>$answer_participant) {
                        // Insert Participants
                        DB::table('cortejo_participants')->insert([
                            'participant' => $key_participant,
                            'question_id' => $qt_participant,
                            'registration_id' => $reg_id['id'],
                            'answer' => $answer_participant
                        ]);
                    }
                }
            }

            $files = $request->file('files');
            foreach ($files as $question_id => $uploadedFiles) {
                $reg_anser = DB::table('registration_answers')->insertGetId([
                    'registration_id' => $reg_id['id'],
                    'question_id' => $question_id,
                    'answer' => ''
                ]);
                foreach ($uploadedFiles as $index=>$file) { 
                    // Gere um nome único para o ficheiro
                    $extension = $file->getClientOriginalExtension();
                    $name = '/Cortejo/' . $img_code .'_' . $index . '.' . $extension;
                    // Armazene o ficheiro no disco público
                    // Storage::disk('public')->put($name, file_get_contents($file));
                    $file->storePubliclyAs('/', $name, 'public');
                    
                    DB::table('answer_images')->insert([
                        'answer_id' => $reg_anser,
                        'path' => $name
                    ]);
                }
            }

            if ($code){
                // Mail para o admin
                // Mail::to('luis.dias@hovo.pt')
                //     ->send(new newRegistryCortejo($code));

                // // Mail para o cliente
                // Mail::to('luis.dias@hovo.pt')
                //     ->send(new holdCortejoRegistry($code));
                // return true;

                // Mail::to('mordomia@vianafestas.com')
                //     ->bcc('no-reply@vianafestas.com')
                //     ->send(new newRegistry($code));

                // // // Mail para o cliente
                Mail::to($userEmail)
                    ->bcc('no-reply@vianafestas.com')
                    ->send(new holdCortejoRegistry($code));
                return true;
            }
            return false;
        }
    }

/**
     * Insert or Update the answers given by users in the Cortejo form
     *
     * @param Request $request
     */
    public function editCortejoAnswers (Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'answer' => 'required',
                    'registration' => 'required',
                    'checklist' => 'required',
                    'old_files' => 'required',
                    'files' => 'required'
                ]
            )
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();

            // Get status id
            $status_id = DB::table('registration_statuses')->where('description', 'Em análise')->pluck('id')->first();
            $reg_id = Registration::find($params['registration']);
            $code = $reg_id['code'];
            $img_code =  (1065 + intval($reg_id['id']));
            Registration::where('id', $reg_id['id'])->update([
                'status_id' => $status_id
            ]);

            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Inscrição editada.'
            ]);
            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Estado alterado para "Em análise".'
            ]);
            
            foreach ($params['answer'] as $question_id => $answer) {
                $qt = Question::find($question_id);
                if ($qt->type_question == 'textarea') {
                    $obs = DB::table('registration_answers')->where('registration_id', $reg_id['id'])
                    ->where('question_id', $question_id)->pluck('answer')->first();
                    if ($answer != 'null' && $answer != null && $answer != ''){
                        $obs = $obs . "\n" . $answer;
                    } else {
                        $obs = $obs ?? '';
                    }
                    DB::table('registration_answers')->where('registration_id', $reg_id['id'])
                    ->where('question_id', $question_id)->update([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => $obs
                    ]);
                    if ($answer != 'null' && $answer != null && $answer != '') {
                        DB::table('history_registrations')->insert([
                            'registration_id' => $reg_id['id'],
                            'description' => 'Obs - ' . $answer
                        ]);
                    }
                }
            }

            // Remove all data from all participants anda add all again
            // DB::table('cortejo_participants')->where('registration_id', $reg_id['id'])->delete();

            // foreach($params['participants'] as $key_participant => $participant) {
            //     foreach ($participant as $qt_participant=>$answer_participant) {
            //         // Insert Participants
            //         DB::table('cortejo_participants')->insert([
            //             'participant' => $key_participant,
            //             'question_id' => $qt_participant,
            //             'registration_id' => $reg_id['id'],
            //             'answer' => $answer_participant
            //         ]);
            //     }
            // }

            // This means new files/Photos
            $files = $request->file('files');
            if (isset($files) && !empty($files)) {
                foreach ($files as $question_id => $uploadedFiles) {
                    $reg_anser = DB::table('registration_answers')->insertGetId([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => ''
                    ]);
                    foreach ($uploadedFiles as $index=>$file) {
                        $n_index = count($params['old_files'][$question_id]);
                        // $n_index = count($params['old_files']);
                        // Gere um nome único para o ficheiro
                        $extension = $file->getClientOriginalExtension();
                        $name = '/Mordomia/' . $img_code .'_' . ($n_index + $index) . '.' . $extension;
                        // Armazene o ficheiro no disco público
                        // Storage::disk('public')->put($name, file_get_contents($file));
                        $file->storePubliclyAs('/', $name, 'public');
                        
                        DB::table('answer_images')->insert([
                            'answer_id' => $reg_anser,
                            'path' => $name
                        ]);
                    }
                }
            }

            if ($code){   
                // Para este caso não interessa enviar emails  
                Registration::where('id', $reg_id['id'])->update([
                    'edit' => null
                ]);
                return true;
            }
            return false;
        }
    }

    public function getCortejo(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'id' => 'required'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $url = $params['id'];
            $code_reg = Registration::where('edit', $url)->where('formulario_id', $params['checklist'])->pluck('id')->first();
            if (!isset($code_reg) || empty($code_reg)) {
                return response()->json(['error' => 'Não é possível editar esta inscrição.'], 403);
            }
            
            $formulario = Registration::find($code_reg);
            $form_id = $formulario->formulario_id;
            $btSelected = 0;
            $btSelected = isset($formulario->type) && !empty($formulario->type) ? intval($formulario->type) : 0;

            $questions = Question::leftJoin('formulario_questions', 'questions.id', 'formulario_questions.question_id')
                            ->where('formulario_questions.formulario_id', $form_id)
                            ->select('questions.id', 'questions.type_question', 'questions.description')->get();
            
            $questions = $this->getQuestions($form_id);
            $answers = $this->getAnswers($code_reg, $form_id);
            $participants = DB::table('cortejo_participants')
            ->where('registration_id', $code_reg)
            ->distinct('participant')
            ->count('participant');
            // $btSelected = 0;
            // if ($participants <= 0) {
            //     $btSelected = 1;
            // }
            // if ($participants > 0 && $participants < 2) {
            //     $btSelected = 2;
            // }
            // if ($participants > 0 && $participants >= 2) {
            //     $btSelected = 3;
            // }
            $props = [];
            for ($j = 0; $j <= $btSelected; $j += 1) {
                $props[$j] = [];
            }
            for ($i = 0; $i < $participants; $i += 1) {
                $props[$btSelected][$i] = array('prop' => $btSelected . $i);
            }
            
            $participant_question = $this->getCortejoParticipant();
            $participant_answer = $this->getCortejoAnswer($code_reg);
            return array('questions' => $questions, 'answers' => $answers, 'registration' => $code_reg, 'participant_questions' => $participant_question, 'participant_answer' => $participant_answer, 'props' => $props, 'btnSelected' => $btSelected);
        }
    }

    public function getCountries(Request $request) {
        $countries = PhoneZones::all();
        return $countries;
    }

    public function editForm(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'id' => 'required'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            // $code = '#' . $params['code'];
            // $given_id = $params['identification'];
            // $given_email = $params['email'];
            // $registration = Registration::where('code', $code)->first();
            // $code_reg = Registration::where('code', $code)->pluck('id')->first();
            // if (!isset($code_reg) || empty($code_reg)) {
            //     return [];
            // }
            // $form_id = Registration::find($code_reg);

            // $edit_validation = false;
            // if (isset($form_id->edit) && !empty($form_id->edit) && $form_id->edit == $params['id']) {
            //     $edit_validation = true;
            // }

            $url = $params['id'];
            $code_reg = Registration::where('edit', $url)->pluck('id')->first();
            if (!isset($code_reg) || empty($code_reg)) {
                // return [];
                return response()->json(['error' => 'Não é possível editar esta inscrição.'], 403);
            }
            
            $formulario = Registration::find($code_reg);
            $form_id = $formulario->formulario_id;
            $questions = Question::leftJoin('formulario_questions', 'questions.id', 'formulario_questions.question_id')
                            ->where('formulario_questions.formulario_id', $form_id)
                            ->select('questions.id', 'questions.type_question', 'questions.description')->get();

            // $id_validation = false;
            // $email_validation = false;
            // foreach($questions as $question) {
            //     if ($question['type_question'] == 'number' && (strpos($question->description, "CC") !== false || strpos($question->description, "Passaporte") !== false)) {
            //         $givenAnswer = DB::table('registration_answers')->where('question_id', $question['id'])->where('registration_id', $code_reg)->pluck('answer')->first();
            //         if (isset($givenAnswer) && !empty($givenAnswer) && $givenAnswer != 'null') {
            //             $id_validation = ($given_id == $givenAnswer) ? true : false;
            //         }
            //     }
            // }
            // if (isset($registration->email) && !empty($registration->email)) {
            //     $email_validation = ($given_email == $registration->email) ? true : false;
            // }

            // This means that validations are ok, so we will retrieve all the form info 
            // if ($id_validation && $email_validation && $edit_validation) {
            //     $questions = $this->getQuestions($form_id);
            //     $answers = $this->getAnswers($code_reg, $form_id);
            //     return array('questions' => $questions, 'answers' => $answers, 'registration' => $code_reg);
            // }
            // if ($id_validation && $email_validation && !$edit_validation) {
            //     return response()->json(['error' => 'Esta inscrição já foi editada.'], 403);
            // }
            
            $questions = $this->getQuestions($form_id);
            $answers = $this->getAnswers($code_reg, $form_id);
            return array('questions' => $questions, 'answers' => $answers, 'registration' => $code_reg);
            // return array();
            // $question_email = Question::where('code', $code)->pluck('id')->first();
        }
    }

    private function getQuestions($id) {
        $result = [];

        $newData = Formulario::leftJoin('formulario_questions', 'formularios.id', 'formulario_questions.formulario_id')
                    ->leftJoin('questions', 'formulario_questions.question_id', 'questions.id')->where('formularios.id', '=', $id)->where('questions.active', 1);
        $result = $newData->select('questions.*', 'formulario_questions.mandatory')->get();
            
        $result = $this->getFormAnswers($result);

        $result = $this->getFormConditions($result, $id);

        return $result;
    }

    private function getAnswers($reg_id, $form_id) {
        $answers = DB::table('registration_answers')
            ->leftJoin('questions', 'registration_answers.question_id', '=', 'questions.id')
            ->where('registration_answers.registration_id', '=', $reg_id)
            ->select(
                'registration_answers.id as answer_id',
                'questions.type_question as type',
                'questions.description as question',
                'registration_answers.question_id as question_id', 
                'registration_answers.answer as answer'
            )
            ->get();

        // Recuperar todas as imagens associadas às respostas
        $images = DB::table('answer_images')
            ->whereIn('answer_id', $answers->pluck('answer_id'))
            ->select('answer_id', 'path')
            ->get();
        
        $result = array();
        foreach ($answers as $answer) {
            $img = $images->where('answer_id', $answer->answer_id)->pluck('path')->all();
            $value = $answer->answer;
            if ($answer->type == 'country' || $answer->type == 'district' || $answer->type == 'concelho' || $answer->type == 'freguesia' || $answer->type == 'select') $value = intval($answer->answer);
            if ($answer->type == 'checkbox') $value = intval($answer->answer);
            if ($answer->type == 'textarea') $value = '';
            if ($answer->type == 'phone' && (strpos($answer->question, "Responsável") === false)){
                $splitted_value = explode(' ', $answer->answer);
                if (count($splitted_value) > 0) {
                    $prefix = explode('+', $splitted_value[0])[1];
                    $number = $splitted_value[1];
                    $value = array();
                    $value['prefix'] = $prefix;
                    $value['number'] = $number;
                }
            }
            if (!isset($result[$answer->question_id])) {
                $result[$answer->question_id] = $value ?? '';
            }
            if (isset($img) && !empty($img)) {
                // Inicialize 'cur_img' e 'new_img' como arrays se ainda não estiverem definidos
                if (!is_array($result[$answer->question_id])) {
                    $result[$answer->question_id] = [];
                }
                if (!isset($result[$answer->question_id]['cur_img'])) {
                    $result[$answer->question_id]['cur_img'] = [];
                }
                if (!isset($result[$answer->question_id]['new_img'])) {
                    $result[$answer->question_id]['new_img'] = [];
                }
                // Adicione a imagem a 'cur_img'
                $result[$answer->question_id]['cur_img'] = array_merge($result[$answer->question_id]['cur_img'], $img);
            }
        }

        return $result;
    }

    public function uAnswers(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'answer' => 'required',
                    'checklist' => 'required',
                    'files' => 'required',
                    'old_files' => 'required',
                    'registration' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            // Get status id
            $status_id = DB::table('registration_statuses')->where('description', 'Em análise')->pluck('id')->first();
            $reg_id = Registration::find($params['registration']);
            $code = $reg_id['code'];
            $img_code =  (1065 + intval($reg_id['id']));
            Registration::where('id', $reg_id['id'])->update([
                'status_id' => $status_id
            ]);

            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Inscrição editada.'
            ]);
            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Estado alterado para "Em análise".'
            ]);
            
            foreach ($params['answer'] as $question_id => $answer) {
                $qt = Question::find($question_id);
                if ($qt->type_question !== 'image' && $qt->type_question !== 'textarea') {
                    DB::table('registration_answers')->where('registration_id', $reg_id['id'])
                    ->where('question_id', $question_id)->update([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => $answer
                    ]);
                    if ($qt->type_question == 'email') {
                        Registration::where('id', $reg_id['id'])->update([
                            'email' => $answer
                        ]);
                    }
                }
                if ($qt->type_question == 'textarea') {
                    $obs = DB::table('registration_answers')->where('registration_id', $reg_id['id'])
                    ->where('question_id', $question_id)->pluck('answer')->first();
                    if ($answer != 'null' && $answer != null && $answer != ''){
                        $obs = $obs . "\n" . $answer;
                    } else {
                        $obs = '';
                    }
                    DB::table('registration_answers')->where('registration_id', $reg_id['id'])
                    ->where('question_id', $question_id)->update([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => $obs
                    ]);
                    if ($answer != 'null' && $answer != null && $answer != '') {
                        DB::table('history_registrations')->insert([
                            'registration_id' => $reg_id['id'],
                            'description' => 'Obs - ' . $answer
                        ]);
                    }
                }
            }

            foreach ($params['old_files'] as $question_id => $answer) {
                if (!isset($answer) || empty($answer)) {
                    DB::table('registration_answers')
                    ->where('registration_id', $reg_id['id'])
                    ->where('question_id', $question_id)
                    ->delete();
                } else {
                    DB::table('registration_answers')->where('registration_id', $reg_id['id'])
                    ->where('question_id', $question_id)->update([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => $answer
                    ]);
                }
            }

            // This means new files/Photos
            $files = $request->file('files');
            if (isset($files) && !empty($files)) {
                foreach ($files as $question_id => $uploadedFiles) {
                    $reg_anser = DB::table('registration_answers')->insertGetId([
                        'registration_id' => $reg_id['id'],
                        'question_id' => $question_id,
                        'answer' => ''
                    ]);
                    foreach ($uploadedFiles as $index=>$file) {
                        $n_index = count($params['old_files'][$question_id]);
                        // $n_index = count($params['old_files']);
                        // Gere um nome único para o ficheiro
                        $extension = $file->getClientOriginalExtension();
                        $name = '/Mordomia/' . $img_code .'_' . ($n_index + $index) . '.' . $extension;
                        // Armazene o ficheiro no disco público
                        // Storage::disk('public')->put($name, file_get_contents($file));
                        $file->storePubliclyAs('/', $name, 'public');
                        
                        DB::table('answer_images')->insert([
                            'answer_id' => $reg_anser,
                            'path' => $name
                        ]);
                    }
                }
            }
            if ($code){   
                // TODO:Para este caso não interessa enviar emails  
                // Mail para o admin
                // Mail::to('luis.dias@hovo.pt')
                //     ->bcc('diogo.freire@hovo.pt')
                //     ->send(new updatedRegistry($code));

                // Mail para o cliente
                // Mail::to('luis.dias@hovo.pt')
                //     ->bcc('diogo.freire@hovo.pt')
                //     ->send(new editedRegistry($code));

                Registration::where('id', $reg_id['id'])->update([
                    'edit' => null
                ]);
                return true;
            }
            return false;
        }
    }

    /**
     * Function to get all questions with their conditions
     *
     * @param Request $request
     */
    public function getCount(Request $request) {
        $reg_count = Registration::count();
        return $reg_count < 950;
    }

    /**
     * Function to check if form is available and set it's color
     *
     * @param Request $request
     */
    public function getActive(Request $request) {
        $form = Formulario::pluck('active', 'id')->toArray();
        return $form;
    }

    public function getQuestionParticipantes(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'board' => 'required',
                    'size' => 'required'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $board_id = $params['board'];
            $size = $params['size'];
            $board = Quadro::find($board_id);
            if (!isset($board) || empty($board)) {
                return response()->json(['error' => 'Não foi possível obter o quadro selecionado.'], 404);
            }
            
            if ($board->insc_limit <= $board->total_insc || ($board->insc_limit <= $board->total_insc + $size)) {
                return response()->json(['error' => 'Não pode adicionar mais participantes ao quadro selecionado'], 404);
            }
            $id = 2;
            $q_id = [1, 2, 8, 9, 10, 11, 12, 21];
            $newData = Question::select()->whereIn('id', $q_id);
            $newData = Formulario::leftJoin('formulario_questions', 'formularios.id', 'formulario_questions.formulario_id')
                        ->leftJoin('questions', 'formulario_questions.question_id', 'questions.id')
                        ->whereIn('questions.id', $q_id)
                        ->where('formularios.id', '=', $id)->where('questions.active', 1);
            $result = $newData->select('questions.*', 'formulario_questions.mandatory')->get();
            $result = $this->getFormAnswers($result);

            $result = $this->getFormConditions($result, 2);

            return $result;
        }
    }

    private function getCortejoParticipant () {
        $id = 2;
        $q_id = [1, 2, 8, 9, 10, 11, 12, 21];
        $newData = Question::select()->whereIn('id', $q_id);
        $newData = Formulario::leftJoin('formulario_questions', 'formularios.id', 'formulario_questions.formulario_id')
                    ->leftJoin('questions', 'formulario_questions.question_id', 'questions.id')
                    ->whereIn('questions.id', $q_id)
                    ->where('formularios.id', '=', $id)->where('questions.active', 1);
        $result = $newData->select('questions.*', 'formulario_questions.mandatory')->get();
        $result = $this->getFormAnswers($result);

        $result = $this->getFormConditions($result, 2);

        return $result;
        
    }

    private function getCortejoAnswer($reg_id){
        $participants = DB::table('cortejo_participants')->where('registration_id', $reg_id)->select('cortejo_participants.*')->get();
        $result = array();
        foreach ($participants as $participant) {
            $question = Question::find($participant->question_id);
            $value = $participant->answer;
            if ($question->type_question == 'country' || $question->type_question == 'district' || $question->type_question == 'concelho' || $question->type_question == 'freguesia' || $question->type_question == 'select') $value = intval($value);
            if ($question->type_question == 'checkbox') $value = intval($value);
            if ($question->type_question == 'textarea') $value = '';
            if ($question->type_question == 'phone' && (strpos($question->type_question, "Responsável") === false)){
                $splitted_value = explode(' ', $value);
                if (count($splitted_value) > 0) {
                    $prefix = explode('+', $splitted_value[0])[1];
                    $number = $splitted_value[1];
                    $value = array();
                    $value['prefix'] = $prefix;
                    $value['number'] = intval($number);
                }
            }
            $code = $participant->participant . $participant->question_id;
            $result[$code] = $value;
        }
        return $result;
    }
}
