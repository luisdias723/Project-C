<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Resources\QuadroResource;
use App\Models\Quadro;
use Validator;
use Illuminate\Support\Facades\DB;

class QuadroController extends BaseController
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

        $queryData = Quadro::where('active', 1)->get();

        foreach($queryData as &$data) {
            $trajes = DB::table('trajes_quadros')
                        ->leftJoin('trajes', 'trajes_quadros.traje_id', '=', 'trajes.id')
                        ->where('trajes_quadros.board_id', $data->id)
                        ->select('trajes.*')
                        ->get();
            $descriptions = $trajes->pluck('description')->toArray();
            $data->trajes = $trajes;
            $data->trajes_array = $descriptions;
            $data->count_trajes = count($trajes);
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
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $quadro = Quadro::create(
                [
                    'description' => $params['description'],
                ]
            );

            if (isset($params['trajes']) && !empty($params['trajes'])) {
                foreach($params['trajes'] as $traje) {
                    DB::table('trajes_quadros')
                        ->insert([
                            'board_id' => $quadro['id'],
                            'traje_id' => $traje['id']
                        ]);
                }
            }

            return new QuadroResource($quadro);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Quadro    $quadro
     * @return QuadroResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Quadro $quadro)
    {
        if ($quadro === null) {
            return response()->json(['error' => 'Não foi possível atualizar o registo solicitado'], 404);
        }

        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'description' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();

            $quadro->description = $request->get('description');

            $quadro->update();

            DB::table('trajes_quadros')->where('board_id', $quadro->id)->delete();

            if (isset($params['trajes']) && !empty($params['trajes'])) {
                foreach($params['trajes'] as $traje) {
                    DB::table('trajes_quadros')
                        ->insert([
                            'board_id' => $quadro->id,
                            'traje_id' => $traje['id']
                        ]);
                }
            }

            return new QuadroResource($quadro);
        }
    }


    /**
     * Update the active value of quadro
     *
     * @param Request $request
     * @return QuadroResource|\Illuminate\Http\JsonResponse
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
            $quadro = Quadro::find($request->id);

            if (!isset($quadro) || empty($quadro)) {
                return response()->json(['errors' => 'Não foi possível atualizar o registo'], 403);
            }
            $active = !$quadro->active;

            $updated = Quadro::where('id', $quadro->id)
            ->update([
                'active' => $active
            ]);

            return $updated;
        }
    }
}
