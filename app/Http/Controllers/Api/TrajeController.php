<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Resources\TrajeResource;
use App\Models\Traje;
use Validator;
use Illuminate\Support\Facades\DB;

class TrajeController extends BaseController
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

        $queryData = Traje::where('active', 1)->get();

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
            $traje = Traje::create(
                [
                    'description' => $params['description'],
                ]
            );

            return new TrajeResource($traje);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return TrajeResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Traje $traje)
    {
        if ($traje === null) {
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

            $traje->description = $request->get('description');

            $traje->update();
            return new TrajeResource($traje);
        }
    }


    /**
     * Update the active value of traje
     *
     * @param Request $request
     * @return TrajeResource|\Illuminate\Http\JsonResponse
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
            $traje = Traje::find($request->id);

            if (!isset($traje) || empty($traje)) {
                return response()->json(['errors' => 'Não foi possível atualizar o registo'], 403);
            }
            $active = !$traje->active;

            $updated = Traje::where('id', $traje->id)
            ->update([
                'active' => $active
            ]);

            return $updated;
        }
    }
}
