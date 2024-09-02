<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Validator;
use App\Models\Traje;
use App\Models\Country;
use App\Models\District;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Mail\acceptedRegistry;
use App\Mail\declinedRegistry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\RegistrationResource;
use App\Mail\acceptedCortejoRegistry;
use App\Mail\declinedCortejoRegistry;
use App\Mail\obsRegistry;
use App\Models\Answer;
use App\Models\Concelho;
use App\Models\Condition;
use App\Models\Formulario;
use App\Models\Freguesia;
use App\Models\Quadro;
use App\Models\Question;
use App\Models\Rancho;

class RegistrationController extends BaseController
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
        $rating_filter = Arr::get($searchParams, 'rating', '');
        $active = Arr::get($searchParams, 'active', '');
        $status = Arr::get($searchParams, 'status', 1);
        $country = Arr::get($searchParams, 'country', '');
        $freguesia = Arr::get($searchParams, 'freguesia', '');
        $traje = Arr::get($searchParams, 'traje', '');
        $form_id = Arr::get($searchParams, 'form_id', '');

        $queryData = Registration::select('registrations.*')
        ->leftJoin('registration_answers', 'registrations.id', '=', 'registration_answers.registration_id')
        ->leftJoin('questions', 'registration_answers.question_id', '=', 'questions.id')
        ->when(isset($keyword) && !empty($keyword), function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('registrations.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere(function ($query) use ($keyword) {
                        $query->where('questions.description', 'LIKE', '%Nome Completo%')
                            ->where('registration_answers.answer', 'LIKE', "%$keyword%");
                    })
                    ->orWhere(function ($query) use ($keyword) {
                        $query->where('registrations.email', 'LIKE', "%$keyword%");
                    });
                });
            })
            ->when(isset($status) && !empty($status), function ($query) use ($status) {
                return $query->where('registrations.status_id', '=', $status);
            })
            ->when(isset($form_id) && !empty($form_id), function ($query) use ($form_id) {
                return $query->where('registrations.formulario_id', '=', $form_id);
            })
            ->when(isset($country) && !empty($country), function ($query) use ($country) {
                $query->where(function ($query) use ($country) {
                    $query->where('questions.description', 'LIKE', '%País%')
                        ->where('registration_answers.answer', '=', $country);
                });
            })
            ->when(isset($freguesia) && !empty($freguesia), function ($query) use ($freguesia) {
                $query->where(function ($query) use ($freguesia) {
                    $query->where('questions.description', 'LIKE', '%Freguesia%')
                        ->where('registration_answers.answer', '=', $freguesia);
                });
            })
            ->when(isset($traje) && !empty($traje), function ($query) use ($traje) {
                $query->where(function ($query) use ($traje) {
                    $query->where('questions.description', 'LIKE', '%Traje%')
                        ->where('registration_answers.answer', '=', $traje);
                });
            })
            ->groupBy('registrations.id')
            ->paginate($limit);

        foreach ($queryData as &$data) {
            // Obter descrição do status
            $status_desc = DB::table('registration_statuses')->where('id', $data->status_id)->pluck('description')->first();
            $data->status_des = $status_desc;

            // Obter todas as respostas dadas
            $answers = DB::table('registration_answers')
                ->leftJoin('questions', 'registration_answers.question_id', '=', 'questions.id')
                ->where('registration_answers.registration_id', '=', $data->id)
                ->select(
                    'registration_answers.id as answer_id',
                    'questions.description as question', 
                    'registration_answers.answer as answer'
                )
                ->get();

            // Recuperar todas as imagens associadas às respostas
            $images = DB::table('answer_images')
                ->whereIn('answer_id', $answers->pluck('answer_id'))
                ->select('answer_id', 'path')
                ->get();

            // Anexar as imagens às respostas
            $answers->transform(function ($answer) use ($images, &$data) {
                if (strpos($answer->question, "Nome Completo") !== false) {
                    $data->name = $answer->answer;
                }
                if (strpos($answer->question, "Email") !== false) {
                    $data->email = $answer->answer;
                }
                $answer->images = $images->where('answer_id', $answer->answer_id)->pluck('path')->all();
                return $answer;
            });
            $data->answers = $answers;
        }


        return RegistrationResource::collection($queryData);
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
     * @param Registration    $Registration
     * @return RegistrationResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Registration $registration)
    {
        if ($registration === null) {
            return response()->json(['error' => 'Não foi possível atualizar o registo solicitado'], 404);
        }

        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'code' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $registration->code = $request->get('code');
            $registration->status_id = $request->get('status_id');
            $registration->active = $request->get('active');

            $registration->update();

            DB::table('history_registrations')->insert([
                'registration_id' => $registration->id,
                'description' => 'Inscrição Atualizada.'
            ]);

            return new RegistrationResource($registration);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Registration $registration
     * @return \Illuminate\Http\Response
     */
    public function destroy(Registration $registration)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  Registration $registration
     * @return RegistrationResource|\Illuminate\Http\JsonResponse
     */
    public function show(Registration $registration)
    {
        $status_desc = DB::table('registration_statuses')->where('id', $registration->status_id)->pluck('description')->first();
        $registration->status_des = $status_desc;

        // Obter todas as respostas dadas
        // $answers = DB::table('registration_answers')
        //     ->leftJoin('questions', 'registration_answers.question_id', '=', 'questions.id')
        //     ->where('registration_answers.registration_id', '=', $registration->id)
        //     ->select(
        //         'registration_answers.id as answer_id',
        //         'questions.description as question',
        //         'questions.type_question as type_question',
        //         'questions.isTraje as isTraje',
        //         'registration_answers.answer as answer'
        //     )
        //     ->get();

        // // Recuperar todas as imagens associadas às respostas
        // $images = DB::table('answer_images')
        //     ->whereIn('answer_id', $answers->pluck('answer_id'))
        //     ->select('answer_id', 'path')
        //     ->get();

        // // Anexar as imagens às respostas
        // $answers->transform(function ($answer) use ($images, &$registration) {
        //     if (strpos($answer->question, "Nome Completo") !== false) {
        //         $registration->name = $answer->answer;
        //     }
        //     if (strpos($answer->question, "Email") !== false) {
        //         $registration->email = $answer->answer;
        //     }
        //     if (strpos($answer->question, "Nascimento") !== false) {
        //         $registration->dt_nascimento = $answer->answer;
        //         $answer->cur_age = $this->calculateCurrentAge($answer->answer);
        //         $registration->isOlder = $this->is14OrOlder($answer->answer);
        //     }
        //     // $answer->images = $images->where('answer_id', $answer->answer_id)->pluck('path')->all();
        //     // if (isset($answer->images) && !empty($answer->images)) {
        //     //     $registration->images = $answer->images;
        //     // }
        //     $answer->images = $images->where('answer_id', $answer->answer_id)->pluck('path')->all();
        //     if (!isset($registration->images)) {
        //         $registration->images = [];
        //     }
        //     if (!empty($answer->images)) {
        //         $registration->images = array_merge($registration->images, $answer->images);
        //     }

        //     if ($answer->isTraje) {
        //         $answer->answer = intval($answer->answer);
        //         $answer->old_answer = intval($answer->answer);
        //         $trajes = Traje::where('active', 1)->get();
        //         $answer->possible_answers = $trajes;
        //     }

        //     if ($answer->type_question == 'country'){
        //         $country = Country::find(intval($answer->answer));
        //         $answer->answer = $country->description;
        //     }
            
            
        //     if ($answer->type_question == 'district' && $answer->answer != 0){
        //         $district = District::find(intval($answer->answer));
        //         $answer->answer = $district->description;
        //     }

        //     if ($answer->type_question == 'freguesia' && $answer->answer != 0){
        //         $freguesia = Freguesia::find(intval($answer->answer));
        //         $answer->answer = $freguesia->description;
        //     }

        //     if ($answer->type_question == 'concelho' && $answer->answer != 0){
        //         $concelho = Concelho::find(intval($answer->answer));
        //         $answer->answer = $concelho->description;
        //     }
        //     return $answer;
        // });

        // $registration->answers = $answers;

        // // $newData = Formulario::leftJoin('formulario_questions', 'formularios.id', 'formulario_questions.formulario_id')
        // //             ->leftJoin('questions', 'formulario_questions.question_id', 'questions.id')->where('formularios.id', '=', 1)->where('questions.active', 1);
        // // $result = $newData->select('questions.*', 'formulario_questions.mandatory')->get();
            
        // // $result = $this->getFormAnswers($result);
        if ($registration->formulario_id == 1) {
            $registration = $this->getSimple($registration);
        } else {
            $registration = $this->getCortForm($registration);
        }

        return $registration;
    }

    private function getSimple($registration) {
        $answers = DB::table('registration_answers')
        ->leftJoin('questions', 'registration_answers.question_id', '=', 'questions.id')
        ->where('registration_answers.registration_id', '=', $registration->id)
        ->select(
            'registration_answers.id as answer_id',
            'questions.description as question',
            'questions.type_question as type_question',
            'questions.isTraje as isTraje',
            'registration_answers.answer as answer'
        )
        ->get();

        // Recuperar todas as imagens associadas às respostas
        $images = DB::table('answer_images')
        ->whereIn('answer_id', $answers->pluck('answer_id'))
        ->select('answer_id', 'path')
        ->get();

        $registration->email = '';
        $registration->identification = '';
        $registration->name = '';
        $registration->dt_nascimento = '';
        $registration->images = [];


        $answers->transform(function ($answer) use ($images, &$registration) {
            $answer->images = $images->where('answer_id', $answer->answer_id)->pluck('path')->all();
            if (!isset($registration->images)) {
                $registration->images = [];
            }
            if (!empty($answer->images)) {
                $registration->images = array_merge($registration->images, $answer->images);
            }
            if (strpos($answer->question, "Nascimento") !== false) {
                $registration->dt_nascimento = $answer->answer;
                $registration->isOlder = $this->is14OrOlder($answer->answer);
            }
            if (strpos($answer->question, "Nome Completo") !== false) {
                $registration->name = $answer->answer ?? '';
            }
            if (strpos($answer->question, "Email") !== false) {
                $registration->email = $answer->answer ?? '';
            }
            if ($answer->type_question == 'number' && (strpos($answer->question, "CC") !== false || strpos($answer->question, "Passaporte") !== false) && (strpos($answer->question, "Responsável") === false)) {
                $registration->identification = $answer->answer ?? '';
            }
        });
        
        $result = $this->getQuestions(1);
        $answers = $this->getAnswers($registration->id);

        
        $registration->answers = $answers;

        $registration->questions = $result;

        $registration->qrCodeText = base64_encode("{code:" . $registration->code . ",email:" . $registration->email . ",id:" . $registration->id . ",formulario_id: " . $registration->formulario_id . ",created_at: " . $registration->created_at . "}");

        return $registration;
    }

    private function getCortForm(&$registration) {
        $code_reg = $registration->id;
        $form_id = 2;
        $btSelected = 0;
        $btSelected = isset($registration->type) && !empty($registration->type) ? intval($registration->type) : 0;

        $questions = Question::leftJoin('formulario_questions', 'questions.id', 'formulario_questions.question_id')
                        ->where('formulario_questions.formulario_id', $form_id)
                        ->select('questions.id', 'questions.type_question', 'questions.description', 'questions.isSpecial', 'questions.isSpecialBoards')->get();

        foreach ($questions as $qt) {
            if ($qt->isSpecial) {
                $registration->question_board = $qt->id;
            }
            if ($qt->isSpecialBoards) {
                $registration->question_trajes = $qt->id;
            }
        }
        

        // Get all the images
        $answers = DB::table('registration_answers')
        ->leftJoin('questions', 'registration_answers.question_id', '=', 'questions.id')
        ->where('registration_answers.registration_id', '=', $registration->id)
        ->select(
            'registration_answers.id as answer_id',
            'questions.id as question_id',
            'questions.description as question',
            'questions.type_question as type_question',
            'questions.isTraje as isTraje',
            'registration_answers.answer as answer'
        )
        ->get();

        // Recuperar todas as imagens associadas às respostas
        $images = DB::table('answer_images')
        ->whereIn('answer_id', $answers->pluck('answer_id'))
        ->select('answer_id', 'path')
        ->get();

        $answers->transform(function ($answer) use ($images, &$registration) {
            $answer->images = $images->where('answer_id', $answer->answer_id)->pluck('path')->all();
            if (!isset($registration->images)) {
                $registration->images = [];
            }
            if (!empty($answer->images)) {
                $registration->images = array_merge($registration->images, $answer->images);
            }
            if (strpos($answer->question, "Nascimento") !== false) {
                $registration->dt_nascimento = $answer->answer;
                $registration->isOlder = $this->is14OrOlder($answer->answer);
            }
            if (strpos($answer->question, "Nome Completo") !== false) {
                $registration->name = $answer->answer ?? '';
            }
            if (strpos($answer->question, "Email") !== false) {
                $registration->email = $answer->answer ?? '';
            }
            if ($answer->type_question == 'number' && (strpos($answer->question, "CC") !== false || strpos($answer->question, "Passaporte") !== false) && (strpos($answer->question, "Responsável") === false)) {
                $registration->identification = $answer->answer ?? '';
            }
            if ($answer->question_id == $registration->question_board) {
                $boar_selected = Quadro::find($answer->answer);
                $registration->board = $boar_selected->description ?? '';
            }
        });
        
        
        $questions = $this->getQuestions($form_id);
        $answers = $this->getAnswers($code_reg, $form_id);
        $participants = DB::table('cortejo_participants')
        ->where('registration_id', $code_reg)
        ->distinct('participant')
        ->count('participant');
        

        $props = [];
        // for ($j = 0; $j <= $btSelected; $j += 1) {
        //     $props[$j] = [];
        // }
        for ($i = 0; $i < $participants; $i += 1) {
            $props[$i] = array('prop' => $btSelected . $i);
        }

        $registration->answers = $answers;
        $registration->questions = $questions;
        
        $registration->participant_answers = $this->getCortejoAnswer($code_reg);
        $registration->participant_questions = $this->getCortejoParticipant();
        $registration->participant_email = $this->getCortejoPartAnswer($code_reg);
        $registration->props = $props;

        $registration->qrCodeText = base64_encode("{code:" . $registration->code . ",email:" . $registration->email . ",id:" . $registration->id . ",formulario_id: " . $registration->formulario_id . ",created_at: " . $registration->created_at . "}");

        return $registration;
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

    private function getCortejoPartAnswer($reg_id){
        $result = array();

        $participants = DB::table('cortejo_participants')
                        ->leftJoin('questions', 'cortejo_participants.question_id', '=', 'questions.id')
                        ->where('registration_id', $reg_id)
                        ->select('cortejo_participants.*',
                            'questions.description as question',
                            'questions.type_question as type_question',
                            'questions.isTraje as isTraje',)
                        ->get();

        $participants->transform(function ($answer) use (&$result) {
            if (!isset($result[$answer->participant])) {
                $result[$answer->participant] = array();
            }
            if (strpos($answer->question, "Nascimento") !== false) {
                $result[$answer->participant]['dt_nascimento'] = $answer->answer;
            }
            if (strpos($answer->question, "Nome Completo") !== false) {
                $result[$answer->participant]['name'] = $answer->answer ?? '';
            }
            if (strpos($answer->question, "Email") !== false) {
                $result[$answer->participant]['email'] = $answer->answer ?? '';
            }
            if ($answer->type_question == 'number' && (strpos($answer->question, "CC") !== false || strpos($answer->question, "Passaporte") !== false) && (strpos($answer->question, "Responsável") === false)) {
                $result[$answer->participant]['identification'] = $answer->answer ?? '';
            }
        });

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

    private function getQuestions($id) {
        $result = [];

        $newData = Formulario::leftJoin('formulario_questions', 'formularios.id', 'formulario_questions.formulario_id')
                    ->leftJoin('questions', 'formulario_questions.question_id', 'questions.id')->where('formularios.id', '=', $id)->where('questions.active', 1);
        $result = $newData->select('questions.*', 'formulario_questions.mandatory')->get();
            
        $result = $this->getFormAnswers($result);

        $result = $this->getFormConditions($result, $id);

        return $result;
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
     * Function to calculate the age of current registration.
     *
     * @param  string $age
     * @return age
     */
    private function calculateAge($age)
    {
        
        // Convert the birthdate to a DateTime object
        $birthDate = new DateTime($age);

        // Get the current year and create a DateTime object for January 1st of the current year
        $currentYear = (new DateTime())->format('Y');
        $startOfYear = new DateTime("$currentYear-01-01");

        // Calculate the difference between January 1st of the current year and the birthdate
        $age = $startOfYear->diff($birthDate);

        // Check if the person turns 14 exactly on January 1st
        if ($age->y == 14 && $birthDate->format('m-d') == '01-01') {
            return 13; // Consider the person as 13 years old for the logic
        }

        // Return the age in years
        return $age->y;
    }

    private function checkAge($birthdate) {
        // Converter a data de nascimento para um objeto DateTime
        $birthDate = new DateTime($birthdate);
    
        // Obter o ano atual
        $currentYear = (new DateTime())->format('Y');
    
        // Criar um objeto DateTime para o dia 30/06 do ano atual
        $endOfJune = new DateTime("$currentYear-06-30");
    
        // Calcular a diferença entre 30/06 do ano atual e a data de nascimento
        $age = $endOfJune->diff($birthDate);
    
        // Verificar se a pessoa já fez 16 anos até o dia 30/06
        if ($age->y >= 16) {
            return true; // A pessoa já fez 16 anos até 30/06
        } else {
            return false; // A pessoa ainda não fez 16 anos até 30/06
        }
    }

    private function calculateCurrentAge($birthdate) {
        // Converter a data de nascimento para um objeto DateTime
        $birthDate = new DateTime($birthdate);
    
        // Obter a data atual
        $currentDate = new DateTime();
    
        // Calcular a diferença entre a data atual e a data de nascimento
        $age = $currentDate->diff($birthDate);
    
        // Retornar a idade em anos
        return $age->y;
    }

    private function is14OrOlder($birthdate) {
        $age = $this->checkAge($birthdate);
        return $age;
    }

     /**
     * Get all status from registration.
     *
     * @param  Request $request
     * @return RegistrationResource|\Illuminate\Http\JsonResponse
     */
    public function getStatus(Request $request)
    {
        $status = DB::table('registration_statuses')->get();
        $status_id = DB::table('registration_statuses')->where('description', 'Em análise')->pluck('id')->first();

        $status_id = isset($status_id) && !empty($status_id) ? intval($status_id) : 0;

        return array('data' => $status, 'status' => $status_id);
    }

    /**
     * Get all notifications templates from registration.
     *
     * @param  Request $request
     * @return RegistrationResource|\Illuminate\Http\JsonResponse
     */
    public function getTemplates(Request $request)
    {
        $template = DB::table('observations_templates')->get();
        return $template;
    }

     /**
     * Get all status from registration.
     *
     * @param  Request $request
     * @return RegistrationResource|\Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request)
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
            $history = DB::table('history_registrations')
                        ->leftJoin('history_reg_users', 'history_registrations.id', 'history_reg_users.hist_id')
                        ->leftJoin('users', 'history_reg_users.user_id', 'users.id')
                        ->where('registration_id', $params['id'])
                        ->select('history_registrations.*', 'users.name as name')
                        ->get();

            // Formata o campo created_at usando a função date do PHP
            $formattedHistory = $history->map(function ($item) {
                $name = '';
                if (isset($item->name) && !empty($item->name)) {
                    $name = $item->name . ' - ';
                }
                $item->created_at = $name . date('d/m/Y H:i:s', strtotime($item->created_at)); // Formato desejado
                return $item;
            });
            return $formattedHistory;
        }
    }

    /**
     * Update status from registration.
     *
     * @param  Request $request
     * @return RegistrationResource|\Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'id' => 'required',
                    'status_id' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();

            $currentUser = Auth::user();

            $updated = Registration::where('id', $params['id'])->update([
                'status_id' => $params['status_id']
            ]);
            if ($updated){
                $desc = DB::table('registration_statuses')->where('id', $params['status_id'])->pluck('description')->first();
                if (isset($params['obs']) && !empty($params['obs'])) {
                    DB::table('history_registrations')->insert([
                        'registration_id' => $params['id'],
                        'description' => $desc . ' - ' . $params['obs'] . '.'
                    ]);
                    // Quebra a string em um array de linhas
                    $lines = explode("\n", $params['obs']);

                    // Envolve cada linha com a tag <p> e junta tudo em uma string
                    $html = '';
                    foreach ($lines as $line) {
                        $html .= '<p>' . htmlspecialchars($line) . '</p>';
                    }
                    $params['obs'] = $html;
                } else {
                    DB::table('history_registrations')->insert([
                        'registration_id' => $params['id'],
                        'description' => 'Estado alterado para em análise.'
                    ]);
                }
                if (strpos($desc, "Aprovada") !== false) {
                    $pdf_path = Registration::find($params['id']);

                    $h_id = DB::table('history_registrations')->insertGetId([
                        'registration_id' => $params['id'],
                        'description' => 'Estado alterado para Aprovada.'
                    ]);

                    DB::table('history_reg_users')->insert([
                        'hist_id' => $h_id,
                        'user_id' => $currentUser->id
                    ]);

                    if (isset($pdf_path) && !empty($pdf_path) && isset($pdf_path->ticket) && !empty($pdf_path->ticket)) {
                        $regs = $this->formatDataEmail($params['id']);

                        // Mail para o cliente
                        Mail::to($pdf_path->email)
                            ->bcc('no-reply@vianafestas.com')
                            ->send(new acceptedRegistry($pdf_path->code, $pdf_path->ticket, $regs, $pdf_path->qrCode, $params['obs']));
                        
                        // Mail::to('luis.dias@hovo.pt')
                        //     ->bcc('diogo.freire@hovo.pt')
                        //     ->send(new acceptedRegistry($pdf_path->code, $pdf_path->ticket, $regs, $pdf_path->qrCode, $params['obs']));
                    }
                }

                if (strpos($desc, "Rejeitada") !== false) {
                    $h_id = DB::table('history_registrations')->insertGetId([
                        'registration_id' => $params['id'],
                        'description' => 'Estado alterado para Recusada.'
                    ]);

                    DB::table('history_reg_users')->insert([
                        'hist_id' => $h_id,
                        'user_id' => $currentUser->id
                    ]);
                    $regst = Registration::find($params['id']);
                    $link = null;
                    if (isset($params['urllink']) && $params['urllink'] == true) {
                        $str = $this->generateUniqueString();
                        $link = env('APP_URL_SERVER') . '/#/inscrever?id=' . $str;
                        Registration::where('id', $params['id'])->update([
                            'edit' => $str
                        ]);
                    }
                    // Mail para o cliente
                    Mail::to($regst->email)
                        ->bcc('no-reply@vianafestas.com')
                        ->send(new declinedRegistry($regst->code, $params['obs'], $link));

                    // Mail::to('luis.dias@hovo.pt')
                    //     ->bcc('diogo.freire@hovo.pt')
                    //     ->send(new declinedRegistry($regst->code, $params['obs'], $link));
                }
                return $desc;
            }
            return null;
        }
    }

    private function generateUniqueString($length = 60) {
        do {
            $str = Str::random($length);
            $exists = Registration::where('edit', $str)->exists();
        } while ($exists);
    
        return $str;
    }

    public function formatDataEmail($reg_id) {
        $answers = DB::table('registration_answers')
            ->leftJoin('questions', 'registration_answers.question_id', '=', 'questions.id')
            ->where('registration_answers.registration_id', '=', $reg_id)
            ->select(
                'registration_answers.id as answer_id',
                'questions.description as question',
                'questions.type_question as type_question',
                'questions.isTraje as isTraje',
                'questions.isSpecial as isSpecial',
                'registration_answers.answer as answer'
            )
            ->get();

        $result = array();

        $result['name'] = '';
        $result['dt_nascimento'] = '';
        $result['cur_age'] = '';
        $result['traje'] = '';
        $result['identification'] = '';
        $result['quadro'] = '';

        // Anexar as imagens às respostas
        $answers->transform(function ($answer) use (&$result) {
            if (strpos($answer->question, "Nome Completo") !== false) {
                $result['name'] = $answer->answer;
            }
            if (strpos($answer->question, "Nascimento") !== false) {
                $result['dt_nascimento'] = $answer->answer;
                $result['cur_age'] = $this->calculateAge($answer->answer);
            }
            if ($answer->isTraje) {
                $traje = Traje::find(intval($answer->answer));
                $result['traje'] = $traje->description;
            }
            if ($answer->isSpecial) {
                $quadro = Quadro::find(intval($answer->answer));
                $result['quadro'] = $quadro->description;
            }
            if ($answer->type_question == 'number' && (strpos($answer->question, "CC") !== false || strpos($answer->question, "Passaporte") !== false) && (strpos($answer->question, "Responsável") === false)) {
                if ($answer->answer != 'null' && $answer->answer != '' && $answer->answer != null) $result['identification'] = $answer->answer;
            }
        });
        return $result;
    }

    public function formatParticipantDataEmail($reg_id) {
        $answers = DB::table('cortejo_participants')
            ->leftJoin('questions', 'cortejo_participants.question_id', '=', 'questions.id')
            ->where('cortejo_participants.registration_id', '=', $reg_id)
            ->select(
                'cortejo_participants.id as answer_id',
                'questions.description as question',
                'questions.type_question as type_question',
                'questions.isTraje as isTraje',
                'questions.isSpecialBoards as isSpecialBoards',
                'cortejo_participants.answer as answer',
                'cortejo_participants.participant as participant'
            )
            ->get();

        $result = array();

        // Anexar as imagens às respostas
        $answers->transform(function ($answer) use (&$result) {
            if (!isset($result[$answer->participant]) || empty($result[$answer->participant])) {
                $result[$answer->participant]['name'] = '';
                $result[$answer->participant]['dt_nascimento'] = '';
                $result[$answer->participant]['cur_age'] = '';
                $result[$answer->participant]['traje'] = '';
            }

            if (strpos($answer->question, "Nome Completo") !== false) {
                $result[$answer->participant]['name'] = $answer->answer;
            }
            if (strpos($answer->question, "Nascimento") !== false) {
                $result[$answer->participant]['dt_nascimento'] = $answer->answer;
                $result[$answer->participant]['cur_age'] = $this->calculateAge($answer->answer);
            }
            if ($answer->isSpecialBoards) {
                $traje = Traje::find(intval($answer->answer));
                $result[$answer->participant]['traje'] = $traje->description;
            }
        });
        return $result;
    }

    /**
     * Update Answer from registration.
     *
     * @param  Request $request
     * @return RegistrationResource|\Illuminate\Http\JsonResponse
     */
    public function updateAnswer(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'answer_id' => 'required',
                    'answer' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $reg_answer = DB::table('registration_answers')->where('id', $params['answer_id'])->first();
            if (isset($reg_answer) && !empty($reg_answer)) {
                $updated = DB::table('registration_answers')->where('id', $params['answer_id'])->update([
                    'answer' => $params['answer']
                ]);
                DB::table('history_registrations')->insert([
                    'registration_id' => $reg_answer->registration_id,
                    'description' => 'Resposta editada' . ' - ' . 'Formulário editado pelo administrador do sistema',
                    'isObs' => 0
                ]);
                return $updated;
            }
            return null;
        }
    }

    /**
     * Save observations from registration and save it as template.
     *
     * @param  Request $request
     * @return RegistrationResource|\Illuminate\Http\JsonResponse
     */
    // public function saveObservation(Request $request)
    // {
    //     $validator = Validator::make(
    //         $request->all(),
    //         array_merge(
    //             [
    //                 'subject' => 'required',
    //                 'obs' => 'required',
    //                 'isTemplate' => 'required',
    //                 'notify' => 'required',
    //                 'registration_id' => 'required',
    //             ]
    //         )
    //     );

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 403);
    //     } else {
    //         $params = $request->all();

    //         DB::table('history_registrations')->insert([
    //             'registration_id' => $params['registration_id'],
    //             'description' => $params['subject'] . ' - ' . $params['obs'],
    //             'isObs' => 1
    //         ]);

    //         // This variable is wrong. Currently is being used to not send the url to email
    //         if (isset($params['notify']) && $params['notify']) {
    //             // Enviar o email a notificar a observação
    //             $reg_data = Registration::find($params['registration_id']);
    //             // Mail para o cliente
    //             Mail::to('luis.dias@hovo.pt')
    //                 ->bcc('diogo.freire@hovo.pt')
    //                 ->send(new obsRegistry($reg_data->code, $params['subject'], $params['obs']));
    //         }
    //         return true;
    //     }
    // }

    public function getCount(Request $request) {
        $params = $request->all();
        $rejected_status = DB::table('registration_statuses')->where('description', 'Rejeitada')->pluck('id')->first();
        $ongoing_status = DB::table('registration_statuses')->where('description', 'Em análise')->pluck('id')->first();
        $accepted_status = DB::table('registration_statuses')->where('description', 'Aprovada')->pluck('id')->first();

        $rejected = Registration::where('formulario_id', $params['form'])->where('status_id', $rejected_status)->count();
        $ongoing = Registration::where('formulario_id', $params['form'])->where('status_id', $ongoing_status)->count();
        $accepted = Registration::where('formulario_id', $params['form'])->where('status_id', $accepted_status)->count();

        return array('rejected' => $rejected, 'ongoing' => $ongoing, 'accepted' => $accepted);
    }

    public function validateQR(Request $request) {
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
            $reg = Registration::find($params['id']);
            // just confirm that qrcode is not available
            if (isset($reg['qrCode']) && !empty($reg['qrCode'])) {
                return $reg['qrCode'];
            }
            return 0;
            // Se não existir vai ser criado aqui
        }
    }
    
    public function SaveQR(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'id' => 'required',
                    'file' => 'required',
                    'data' => 'required'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $reg_id = $request->get('id');
            $reg = Registration::find($reg_id);
            // just confirm that qrcode is not available
            if (isset($reg['qrCode']) && !empty($reg['qrCode'])) {
                return $reg['qrCode'];
            }
            // Se não existir vai ser criado aqui
            // Recebe o arquivo base64 do FormData
            $fileBase64 = $request->input('file');
            $pdfContent = $fileBase64;

            // Decodifica o arquivo base64
            list($type, $fileBase64) = explode(';', $fileBase64);
            list(, $fileBase64) = explode(',', $fileBase64);
            $fileData = base64_decode($fileBase64);

            // Salva o arquivo no storage do Laravel
            $fileName = (1065 + intval($reg_id)) . '.png'; // ou qualquer outra extensão de arquivo desejada
            $filePath = public_path('/file1_qr2_code/') . $fileName;
            file_put_contents($filePath, $fileData);
            $db_name = '/file1_qr2_code/' . $fileName;
            // Now we have to generate the pdf!
            $banner_content = file_get_contents(public_path('/assets/') . 'email_banner.jpg');
            $banner = base64_encode($banner_content);
            $event = array(
                'eventName' => 'Desfile da Mordomia 2024',
                'data' => '2024-08-16',
                'hora' => '14h00m',
            );

            $pdfName = (1065 + intval($reg_id)) . '.pdf'; // ou qualquer outra extensão de arquivo desejada
            $ticket_path = '/tickets_generated/' . $pdfName;
            $pdf = PDF::loadView('pdf.ticket', ['qrcode' => $pdfContent, 'banner' => $banner, 'event' => $event, 'data' => json_decode($request->input('data'), true)]);
            $pdfPath = public_path($ticket_path);
            $pdf->save($pdfPath);
            $update = Registration::where('id', $reg_id)->update([
                'qrCode' => $db_name,
                'ticket' => $ticket_path
            ]);
            if ($update) {
                return $db_name;
            }
            return 0;
        }
    }


    public function SaveQRCortejo(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'id' => 'required',
                    'file' => 'required',
                    'data' => 'required'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $reg_id = $request->get('id');
            $reg = Registration::find($reg_id);
            // just confirm that qrcode is not available
            if (isset($reg['qrCode']) && !empty($reg['qrCode'])) {
                return $reg['qrCode'];
            }
            // Se não existir vai ser criado aqui
            // Recebe o arquivo base64 do FormData
            $fileBase64 = $request->input('file');
            $pdfContent = $fileBase64;

            // Decodifica o arquivo base64
            list($type, $fileBase64) = explode(';', $fileBase64);
            list(, $fileBase64) = explode(',', $fileBase64);
            $fileData = base64_decode($fileBase64);

            // Salva o arquivo no storage do Laravel
            $fileName = (1065 + intval($reg_id)) . '.png'; // ou qualquer outra extensão de arquivo desejada
            $filePath = public_path('/file1_qr2_code/') . $fileName;
            file_put_contents($filePath, $fileData);
            $db_name = '/file1_qr2_code/' . $fileName;
            // Now we have to generate the pdf!
            $banner_content = file_get_contents(public_path('/assets/') . 'email_cortejo_banner.jpg');
            $banner = base64_encode($banner_content);
            $event = array(
                'eventName' => 'Cortejo Etnográfico 2024',
                'data' => '2024-08-17',
                'hora' => '14h00m',
            );

            $pdfName = (1065 + intval($reg_id)) . '.pdf'; // ou qualquer outra extensão de arquivo desejada
            $ticket_path = '/cortejo_tickets_generated/' . $pdfName;
            $pdf = PDF::loadView('pdf.ticket_corte', ['qrcode' => $pdfContent, 'banner' => $banner, 'event' => $event, 'data' => json_decode($request->input('data'), true)]);
            $pdfPath = public_path($ticket_path);
            $pdf->save($pdfPath);
            $update = Registration::where('id', $reg_id)->update([
                'qrCode' => $db_name,
                'ticket' => $ticket_path
            ]);
            if ($update) {
                return $db_name;
            }
            return 0;
        }
    }


    public function getTrajeCounter(Request $request) {
        $answers = DB::table('registration_answers')
            ->leftJoin('questions', 'registration_answers.question_id', '=', 'questions.id')
            ->select(
                'registration_answers.id as answer_id',
                'questions.description as question',
                'questions.type_question as type_question',
                'questions.isTraje as isTraje',
                'registration_answers.answer as answer'
            )
            ->get();

            $result = array();

            $trajes = Traje::all();

            foreach ($trajes as $tj) {
                $result[$tj->id] = array('description' => $tj->description, 'count' => 0);
            }

            $answers->transform(function ($answer) use (&$result) {
                if ($answer->isTraje) {
                    $traje = Traje::find(intval($answer->answer));
                    if (!isset($result[intval($answer->answer)])){
                        $result[intval($answer->answer)] = array('description' => $traje->description, 'count' => 0);
                    }
                    $result[intval($answer->answer)]['count'] += 1;
                }
            });

        return $result;
    }

    public function getFilters(Request $request) {
        $trajes = Traje::all();
        $paises = Country::all();
        $freguesias = Freguesia::all();

        return array(
            'trajes' => $trajes,
            'paises' => $paises,
            'freguesias' => $freguesias
        );
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

    private function getAnswers($reg_id) {
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
            if (!isset($img) || empty($img)) {
                if ($answer->answer == '0' || $answer->answer == 'null' || $answer->answer == 0) $answer->answer = '';
                $value = $answer->answer;
                if ($answer->type == 'country' || $answer->type == 'district' || $answer->type == 'concelho' || $answer->type == 'freguesia' || $answer->type == 'select'){
                    if ($answer->answer != '') $value = intval($answer->answer);
                }
                if ($answer->type == 'checkbox') $value = intval($answer->answer);
                // if ($answer->type == 'textarea') $value = '';
                if ($answer->type == 'phone' && (strpos($answer->question, "Responsável") === false)){
                    $splitted_value = explode(' ', $answer->answer);
                    if (count($splitted_value) > 1) {
                        $prefix = explode('+', $splitted_value[0])[1];
                        $number = $splitted_value[1];
                        $value = array();
                        $value['prefix'] = $prefix;
                        $value['number'] = $number;
                    } else {
                        $value = array();
                        $value['prefix'] = '351';
                        $value['number'] = $answer->answer;
                    }
                }
                if (!isset($result[$answer->question_id])) {
                    $result[$answer->question_id] = $value ?? '';
                }
            }
        }
        return $result;
    }

    public function editAnswer(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'answers' => 'required',
                    'id' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            // Get status id
            // $status_id = DB::table('registration_statuses')->where('description', 'Em análise')->pluck('id')->first();
            $reg_id = Registration::find($params['id']);
            $code = $reg_id['code'];
            $img_code =  (1065 + intval($reg_id['id']));
            Registration::where('id', $reg_id['id'])->update([
                'qrCode' => '',
                'ticket' => ''
            ]);

            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Inscrição editada pelo Admin.'
            ]);
            
            foreach ($params['answers'] as $question_id => $answer) {
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
            }
            if ($code){ 
                return true;
            }
            return false;
        }
    }

    public function editCortejoAnswer(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'answers' => 'required',
                    'id' => 'required',
                ]
            )
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            // Get status id
            // $status_id = DB::table('registration_statuses')->where('description', 'Em análise')->pluck('id')->first();
            $reg_id = Registration::find($params['id']);
            $code = $reg_id['code'];
            Registration::where('id', $reg_id['id'])->update([
                'qrCode' => '',
                'ticket' => ''
            ]);

            DB::table('history_registrations')->insert([
                'registration_id' => $reg_id['id'],
                'description' => 'Inscrição editada pelo Admin.'
            ]);
            
            foreach ($params['answers'] as $question_id => $answer) {

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
            }
            if (isset($params['participations']) && !empty($params['participations'])) {
                
                foreach ($params['participations'] as $q_part => $part) {

                    foreach($part as $test=>$test_2){

                        $qt_p = Question::find($test);
                        if ($qt_p->type_question !== 'image' && $qt_p->type_question !== 'textarea') {
                            DB::table('cortejo_participants')->where('registration_id', $reg_id['id'])
                            ->where('question_id', $test)->update([
                                'registration_id' => $reg_id['id'],
                                'question_id' => $test,
                                'answer' => $test_2
                            ]);
                            // if ($qt->type_question == 'email') {
                            //     dd($test_2);
                            //     Registration::where('id', $reg_id['id'])->update([
                            //         'email' => $test_2
                            //     ]);
                            // }


                    }

                   
                    }
                }
            }
            if ($code){ 
                return true;
            }
            return false;
        }
    }

    public function getEstatistica (Request $request) {
        $answers = Registration::leftJoin('registration_answers', 'registrations.id', 'registration_answers.registration_id')
        ->leftJoin('questions', 'registration_answers.question_id', '=', 'questions.id')
        ->where('registrations.status_id', '=', 3)
        ->select(
            'registration_answers.id as answer_id',
            'questions.description as question',
            'questions.type_question as type_question',
            'questions.isTraje as isTraje',
            'registration_answers.answer as answer'
        )
        ->get();

        $registration = (object)array();

        $registration->total = count(Registration::select('id')->where('registrations.status_id', '=', 3)->get());
        $registration->media = 0;
        $registration->moda = [];
        $registration->youngest = '';
        $registration->oldest = '';
        // $registration->countries_array = [];
        $registration->countries = [];
        // $registration->distritos_array = [];
        $registration->distritos = [];
        // $registration->trajes_array = [];
        $registration->trajes = [];
        $count_age = 0;

        $answers->transform(function ($answer) use (&$registration, &$count_age) {
            if (strpos($answer->question, "Nascimento") !== false) {
                if ($registration->youngest === null || $registration->youngest === '' || new DateTime($registration->youngest) < new DateTime($answer->answer)) {
                    $registration->youngest = $answer->answer;
                }
                if ($registration->oldest === null || $registration->oldest === '' || new DateTime($answer->answer) < new DateTime($registration->oldest)) {
                    $registration->oldest = $answer->answer;
                }
                if ($answer->answer != '' && $answer->answer != 'null' && $answer->answer != null) {
                    $count_age += 1;
                    $registration->media += intval($this->calculateAge($answer->answer));
                    array_push($registration->moda, intval($this->calculateAge($answer->answer)));
                }
            }
            if (strpos($answer->question, "Nome Completo") !== false) {
                $registration->name = $answer->answer ?? '';
            }
            if (strpos($answer->question, "Email") !== false) {
                $registration->email = $answer->answer ?? '';
            }
            if (strpos($answer->question, "País") !== false) {
                if ($answer->answer != '' && $answer->answer != 'null' && $answer->answer != null) {
                    $country = Country::find($answer->answer);
                    if (!in_array($country->description, $registration->countries)) {
                        array_push($registration->countries, $country->description);
                    }
                }
            }
            if (strpos($answer->question, "Distrito") !== false) {
                if ($answer->answer != '' && $answer->answer != 'null' && $answer->answer != null) {
                    $district = District::find($answer->answer);
                    if (!in_array($district->description, $registration->distritos)) {
                        array_push($registration->distritos, $district->description);
                    }
                }
            }
            if (strpos($answer->question, "Traje") !== false) {
                if ($answer->answer != '' && $answer->answer != 'null' && $answer->answer != null) {
                    $traje = Traje::find($answer->answer);
                    if (!in_array($traje->description, $registration->trajes)) {
                        array_push($registration->trajes, $traje->description);
                    }
                }
            }
            if ($answer->type_question == 'number' && (strpos($answer->question, "CC") !== false || strpos($answer->question, "Passaporte") !== false) && (strpos($answer->question, "Responsável") === false)) {
                $registration->identification = $answer->answer ?? '';
            }
        });
        $registration->youngest = $registration->youngest . ' (' . intval($this->calculateAge($registration->youngest)) . ' anos)';
        $registration->oldest = $registration->oldest . ' (' . intval($this->calculateAge($registration->oldest)) . ' anos)';
        $registration->media = $registration->media / $count_age;
        // $registration->countries = count($registration->countries_array) > 0 ? implode(', ', $registration->countries_array) : '';
        // $registration->distritos = count($registration->distritos_array) > 0 ? implode(', ', $registration->distritos_array) : '';
        // $registration->trajes = count($registration->trajes_array) > 0 ? implode(', ', $registration->trajes_array) : '';

        // Inicialize um array associativo para contar as frequências
        $frequencies = array();

        // Conte a frequência de cada número
        foreach ($registration->moda as $number) {
            if (!isset($frequencies[$number])) {
                $frequencies[$number] = 0;
            }
            $frequencies[$number]++;
        }

        // Encontre o número com a maior frequência
        $maiorFrequencia = 0;
        $moda = null;

        foreach ($frequencies as $data => $frequencia) {
            if ($frequencia > $maiorFrequencia) {
                $maiorFrequencia = $frequencia;
                $moda = $data;
            }
        }
        $registration->moda = $moda;

        $pdfName = 'Desfile_Mordomia.pdf'; // ou qualquer outra extensão de arquivo desejada
        $ticket_path = $pdfName;
        $pdf = PDF::loadView('estatistica.estatistica', ['data' => $registration]);
        $pdfPath = public_path($ticket_path);
        $pdf->save($pdfPath);

        // Ver se existe o ficheiro e fazer download dele!
    }

    /**
     * Update status from registration.
     *
     * @param  Request $request
     * @return RegistrationResource|\Illuminate\Http\JsonResponse
     */
    public function updateCortejoStatus(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'id' => 'required',
                    'status_id' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();

            $currentUser = Auth::user();

            $updated = Registration::where('id', $params['id'])->update([
                'status_id' => $params['status_id']
            ]);
            if ($updated){
                $desc = DB::table('registration_statuses')->where('id', $params['status_id'])->pluck('description')->first();
                if (isset($params['obs']) && !empty($params['obs'])) {
                    DB::table('history_registrations')->insert([
                        'registration_id' => $params['id'],
                        'description' => $desc . ' - ' . $params['obs'] . '.'
                    ]);
                    // Quebra a string em um array de linhas
                    $lines = explode("\n", $params['obs']);

                    // Envolve cada linha com a tag <p> e junta tudo em uma string
                    $html = '';
                    foreach ($lines as $line) {
                        $html .= '<p>' . htmlspecialchars($line) . '</p>';
                    }
                    $params['obs'] = $html;
                } else {
                    DB::table('history_registrations')->insert([
                        'registration_id' => $params['id'],
                        'description' => 'Estado alterado para em análise.'
                    ]);
                }
                if (strpos($desc, "Aprovada") !== false) {
                    $pdf_path = Registration::find($params['id']);

                    $h_id = DB::table('history_registrations')->insertGetId([
                        'registration_id' => $params['id'],
                        'description' => 'Estado alterado para Aprovada.'
                    ]);

                    DB::table('history_reg_users')->insert([
                        'hist_id' => $h_id,
                        'user_id' => $currentUser->id
                    ]);

                    if (isset($pdf_path) && !empty($pdf_path) && isset($pdf_path->ticket) && !empty($pdf_path->ticket)) {
                        $regs = $this->formatDataEmail($params['id']);
                        $participants = $this->formatParticipantDataEmail($params['id']);


                        // Mail para o cliente
                        Mail::to($pdf_path->email)
                            ->bcc('no-reply@vianafestas.com')
                            ->send(new acceptedCortejoRegistry($pdf_path->code, $pdf_path->ticket, $regs, $participants, $pdf_path->qrCode, $params['obs']));
                        
                        // Mail::to('luis.dias@hovo.pt')
                        //     ->bcc('diogo.freire@hovo.pt')
                        //     ->send(new acceptedRegistry($pdf_path->code, $pdf_path->ticket, $regs, $pdf_path->qrCode, $params['obs']));
                    }
                }

                if (strpos($desc, "Rejeitada") !== false) {
                    $h_id = DB::table('history_registrations')->insertGetId([
                        'registration_id' => $params['id'],
                        'description' => 'Estado alterado para Recusada.'
                    ]);

                    DB::table('history_reg_users')->insert([
                        'hist_id' => $h_id,
                        'user_id' => $currentUser->id
                    ]);
                    $regst = Registration::find($params['id']);
                    $link = null;
                    if (isset($params['urllink']) && $params['urllink'] == true) {
                        $str = $this->generateUniqueString();
                        $link = env('APP_URL_SERVER') . '/#/inscrever?cortejo=' . $str;
                        Registration::where('id', $params['id'])->update([
                            'edit' => $str
                        ]);
                    }
                    // Mail para o cliente
                    Mail::to($regst->email)
                        ->bcc('no-reply@vianafestas.com')
                        ->send(new declinedCortejoRegistry($regst->code, $params['obs'], $link));

                    // Mail::to('luis.dias@hovo.pt')
                    //     ->bcc('diogo.freire@hovo.pt')
                    //     ->send(new declinedRegistry($regst->code, $params['obs'], $link));
                }
                return $desc;
            }
            return null;
        }
    }

}