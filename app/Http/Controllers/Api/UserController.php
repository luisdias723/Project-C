<?php

namespace App\Http\Controllers\Api;

// use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\Extra\JsonResponse;
// use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class UserController extends BaseController
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
        $userQuery = User::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $role = Arr::get($searchParams, 'role', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        $rating_filter = Arr::get($searchParams, 'rating', '');
        $active = Arr::get($searchParams, 'active', '');

      
        
        $currentUser = Auth::user();
        // $roles = $currentUser->getRoleNames()->toArray();
      

        $category_filter = Arr::get($searchParams, 'category_id', '');

        // if(($role == "" || $role == "admin" || $role == "gestor") && !$currentUser->isAdmin()){
        //     return response()->json(['error' => 'Não possui permissões ver este conteúdo'], 403);
        // }

        // // only admins and gestores can see coaches and client listings
        // if($role == 'cliente' && !in_array('gestor', $roles) && !in_array('admin', $roles)){
        //     return response()->json(['error' => 'Não possui permissões ver este conteúdo'], 403);
        // }

        // if($role == 'terapeuta' && !in_array('gestor', $roles) && !in_array('admin', $roles)){
        //     return response()->json(['error' => 'Não possui permissões ver este conteúdo'], 403);
        // }

        if (!empty($category_filter)) {
            // if($category_filter != '1'){
                $userQuery->where('users.category_id', $category_filter);
            // }
        }
        
        // search by role
        if (!empty($role)) {
            $userQuery->whereHas('roles', function($q) use ($role) { $q->where('name', $role); });
        }


        if (isset($active)) {
            $userQuery->where('users.active', $active);
        }

        // search by keyword
        if (!empty($keyword)) {
            $userQuery->where(function($query) use ($keyword){
                    $query->where('users.name', 'LIKE', '%' . $keyword . '%');
                    $query->orWhere('users.email', 'LIKE', '%' . $keyword . '%');
            });
        }

        
        // get avg rating for coaches
        if(!empty($role) && $role == 'terapeuta') {
         
        //     $userQuery->select('users.id', 'users.name', DB::raw('AVG(ratings.score) as avg_rating'))
        //     ->join('ratings', 'users.id', '=', 'ratings.user_id')
        //     ->leftJoin('plan_sessions', function ($join) {
        //     $join->on('ratings.session_id', '=', 'plan_sessions.id')
        //     ->whereRaw('plan_sessions.doctor_id != ratings.created_by');
        //  })
        //    ->where('ratings.score', '>', 0)
        //    ->groupBy('users.id', 'users.name');
    
        $userQuery->select('users.id', 'users.name','users.active', DB::raw('AVG(ratings.score) as avg_rating'))
        ->leftJoin('ratings', function ($join) {
            $join->on('users.id', '=', 'ratings.user_id');
        })
        ->leftJoin('plan_sessions', function ($join) {
            $join->on('ratings.session_id', '=', 'plan_sessions.id')
                ->whereRaw('plan_sessions.doctor_id != ratings.created_by');
        })
        ->where(function ($query) {
            $query->where('ratings.score', '>', 0)
                ->orWhereNull('ratings.score');
        })
        ->groupBy('users.id', 'users.name', 'users.active');
         
            // $userQuery->leftJoin('module_categories', 'users.category_id', 'module_categories.id');
            // $userQuery->leftJoin('plan_sessions', 'users.id', 'plan_sessions.doctor_id');

            // // $userQuery->select('plan_sessions.id   as session_id')->where('plan_session.finished' == 1)->where('doctor_id' =='users.id');

            // // echo '<pre>', print_r($userQuery->get());

            // $userQuery->select('users.id', 'users.name', 'users.email', 'users.created_at', 'module_categories.name as category_name', 'users.active','plan_sessions.doctor_id as doctor_id',
            //                     DB::raw('(SELECT AVG(ratings.score) FROM ratings WHERE ratings.user_id = users.id AND doctor_id !=ratings.created_by AND ratings.active = 1) as avg_rating'));
            //                     // ->orderBy('avg_rating','DESC');

            if(!empty($rating_filter)){
                $userQuery->whereRaw('(SELECT IFNULL(AVG(ratings.score), 0) FROM ratings WHERE ratings.user_id = users.id  AND ratings.active = 1 AND ratings.score>0) BETWEEN  ? AND ? ', [$rating_filter[0], $rating_filter[1]]);
            }

           
        }

        return UserResource::collection($userQuery->distinct()->paginate($limit));
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(),
                [
                    'password' => (($params['set_password'] == 'no') ? 'sometimes' : 'required|min:6'),
                    'confirmPassword' => 'same:password',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $user = User::create([
                'name' => $params['name'],
                'email' => $params['email'],
                'password' => Hash::make($params['password'] ?? Str::random(15)),
                'register_completed' => ($params['type'] == 'cliente' ? 0 : 1),
                'type' => $params['type'] ?? null,
                'category_id' =>  $params['category_id'] ?? null,
            ]);

            $params['roles'] = isset($params['roles']) ? $params['roles'] : 4;
            $role = Role::findByName($params['role']);
      
            $user->syncRoles($role);

            if($params['set_password'] == 'no'){
                // generate a reset password token
                $token = Str::random(60);

                $insertedReset = DB::table('password_resets')->insert([
                    'email' => $user['email'],
                    'token' => $token
                ]);
                
                $url = env('APP_URL') . '#/password?id=' . $token;
                $user['url'] = $url;
                
                if ($insertedReset) {
                    return $this->sendNewUserPasswordMail($user);
                }

            } else {
                $this->sendRegistrationFinishedEmail($params['email'], env('APP_URL'));
            }

            return new UserResource($user);
        }
    }

    public function startRegister(Request $request)
    {
        // TODO: Put roles to new users
        $validator = Validator::make(
            $request->all(),
            
                [
                    'password' => ['required', 'min:6'],
                    'confirmPassword' => 'same:password',
                ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {

            $params = $request->all();
            
            $existsEmail = isset($params['email']) ? User::where('email', 'like', $params['email'])->first() : null;

            if(isset($existsEmail) && !empty($existsEmail)) {
                return response()->json(['errors' => 'Já existe um utilizador com o email introduzido'], 403);
            }

            $user = User::create([
                'name' => $params['name'],
                'phone' => isset($params['phone']) ? $params['phone'] : null,
                'email' => $params['email'],
                'password' => Hash::make($params['password']),
                'type' => isset($params['type']) ? $params['type'] : 'cliente',
            ]);
            
            $userRole = isset($params['roles']) ? $params['roles'] : 'cliente';
            $role = Role::findByName($userRole);
            
            $user->syncRoles($role);
            Log::info('utilizador registado');

            $this->sendRegistrationFinishedEmail($params['email'], 'https://sessoes.oky.pt/');
            
            return new UserResource($user);
        }
    }

    public function register(Request $request)
    {
        // TODO: Put roles to new users
        $validator = Validator::make(
            $request->all(),
                $this->getValidationRules(true),
                [
                    'password' => ['required', 'min:6'],
                    'confirmPassword' => 'same:password',
                ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {

            $params = $request->all();
            
            $existsEmail = isset($params['email']) ? User::where('email', 'like', $params['email'])->first() : null;

            if(isset($existsEmail) && !empty($existsEmail)) {
                return response()->json(['errors' => 'Já existe um utilizador com o email introduzido'], 403);
            }

            // $user = User::create([
            //     'name' => $params['name'],
            //     'phone' => isset($params['phone']) ? $params['phone'] : null,
            //     'email' => $params['email'],
            //     'password' => Hash::make($params['password']),
            //     'type' => isset($params['type']) ? $params['type'] : 'cliente',
                    // 'register_completed' = '1'
            // ]);

            // $userRole = isset($params['roles']) ? $params['roles'] : 'cliente';
            // $role = Role::findByName($userRole);
            
            // $user->syncRoles($role);
            Log::info('utilizador registado');

            // $this->sendRegistrationStartedEmail($params['email'], 'https://sessoes.oky.pt/');
            // $this->sendFinishRegistrationMail($params['email'], 'https://sessoes.oky.pt/');
            
            return new UserResource($user);
        }
    }
    
    public function newOrder(Request $request)
    {
        
        // TODO: Put roles to new users
        $validator = Validator::make(
            $request->all(),
                [
                    'name' => 'required|max:255',
                    'email' => 'required|email',
                    'password' => ['required', 'min:6'],
                    'confirmPassword' => 'same:password',
                    'pack_acquired' => 'required',
                ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {

            $params = $request->all();
            
            $existsEmail = isset($params['email']) ? User::where('email', 'like', $params['email'])->first() : null;


            if (!isset($existsEmail) || empty($existsEmail)) {
                $user = User::create([
                    'name' => $params['name'],
                    'phone' => isset($params['phone']) ? $params['phone'] : null,
                    'email' => $params['email'],
                    'password' => Hash::make($params['password']),
                    'type' => isset($params['type']) ? $params['type'] : 'cliente',
                ]);

                // create a password reset token so the new customer can define a new one
                $token = Str::random(60);
                $insertedReset = DB::table('password_resets')->insert([
                    'email' => $params['email'],
                    'token' => $token
                ]);

                $url = 'https://sessoes.oky.pt/#/password?id=' . $token;
                $user['url'] = $url;
                if ($insertedReset) {
                    $this->sendRegistrationStartedEmail($params['email'], $url);
                }
                
            } else {
                $user = $existsEmail;
            }
            $userRole = isset($params['roles']) ? $params['roles'] : 'cliente';
            $role = Role::findByName($userRole);
            $user->syncRoles($role);
            
            Log::info('utilizador registado');
            
            if($user) {
                // create the new plan
                $plan = Plan::create([
                    // 'doctor_id' => $params['doctor_id'],
                    'client_id' => $user->id,
                    'num_modules' => DB::table('packs')->where('id', $params['pack_acquired'])->pluck('num_modules')->first(),
                    'value' => DB::table('packs')->where('id', $params['pack_acquired'])->pluck('value')->first(),
                    'pack_id' => $params['pack_acquired'],
                ]);

                $url = env('BASE_URL', '');

                $url .= '#/sessoes/ver/' . $plan->id;

                $this->createNotification($user->id, 'Foi adicionado um novo plano.', null, $url);
        
                // duplicate all the associated modules and content
                $modules = DB::table('packs_module')->where('pack_id', $params['pack_acquired'])->get()->toArray();

                Payment::create([
                    'user_id' => $user->id,
                    'description' => 'Pack adquirido',
                    'value' => DB::table('packs')->where('id', $params['pack_acquired'])->pluck('value')->first(),
                ]);
        
                foreach($modules as $module){
                    $this->createPlanSession($plan->id, $module->module_id);
                }
                
                
            }

            return new UserResource($user);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  User $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        $userQuery = User::where('users.id', $user->id);
        
        $userQuery->leftJoin('module_categories', 'users.category_id', 'module_categories.id',);
        
        $userQuery->select('users.*', 'module_categories.name as category_name', 'users.category_id', 
        DB::raw('(SELECT AVG(ratings.score) FROM ratings WHERE ratings.user_id = users.id AND ratings.active = 1) as avg_rating'));

        return new UserResource($userQuery->first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'Utilizador não encontrado'], 404);
        }

        $cur_user = Auth::user();

        if ($user->isAdmin() && !$cur_user->isAdmin()) {
            return response()->json(['error' => 'Admin não pode ser modificado'], 403);
        }

        $currentUser = Auth::user();
        if (!$currentUser->isAdmin()
            && $currentUser->id !== $user->id
            && !$currentUser->hasPermission(\App\Extra\Acl::PERMISSION_USER_MANAGE)
        ) {
            return response()->json(['error' => 'Permissão negada'], 403);
        }

        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(false),
                [
                    'newPassword' => ['sometimes', 'nullable', 'min:6'],
                    'confirmNewPassword' => ['sometimes', 'same:newPassword'],
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $user->name = $request->get('name');
            $user->email = $request->get('email');
            $user->phone = $request->get('phone');
            $user->mobile_code = $request->get('mobile_code');

            $user->birthday = $request->get('birthday');
            $user->address = $request->get('address');
            $user->zipcode = $request->get('zipcode');
            $user->city = $request->get('city');
            $user->country = $request->get('country');
            $user->tax_regime = $request->get('tax_regime');

            $user->iban = $request->get('iban');
            $user->nif = $request->get('nif');

            $user->category_id = $request->get('category_id');

            $user->same_invoice_data = $request->get('same_invoice_data');
            $user->invoice_name = $request->get('invoice_name');
            $user->invoice_address = $request->get('invoice_address');
            $user->invoice_iban = $request->get('invoice_iban');
            $user->invoice_nif = $request->get('invoice_nif');
            $user->is_mastermind = $request->get('is_mastermind');
            $user->colors = $request->get('colors');
            $user->coach_coachee = $request->get('coach_coachee') ?? 0;

            if($request->get('newPassword')) {
                $user->password = Hash::make($request->get('newPassword'));
            }

            $user->update();
            return new UserResource($user);
        }
    }


    public function updateActive(Request $request){

        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'active' => ['required'],
                    'user_id' => ['required'],
                ]
            )
        );

        $params = $request->all();

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $is_admin = User::where('id', $params['user_id'])->whereHas('roles', function($q) { $q->where('name', 'admin'); })->exists();

            if ($is_admin) {
                return response()->json(['error' => 'Admin não pode ser modificado'], 403);
            }
    
          
            User::where('id', $params['user_id'])->update(['active' => $params['active']]);

            return true;
        }
          
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function updatePermissions(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->isAdmin()) {
            return response()->json(['error' => 'Admin can not be modified'], 403);
        }

        $permissionIds = $request->get('permissions', []);
        $rolePermissionIds = array_map(
            function($permission) {
                return $permission['id'];
            },

            $user->getPermissionsViaRoles()->toArray()
        );

        $newPermissionIds = array_diff($permissionIds, $rolePermissionIds);
        $permissions = Permission::allowed()->whereIn('id', $newPermissionIds)->get();
        $user->syncPermissions($permissions);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function updateGroupId(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->isAdmin()) {
            return response()->json(['error' => 'Admin can not be modified'], 403);
        }

        $group_id = $request->get('group_id', null);
        User::where('id', $user->id)->update(array('group_id' => $group_id));

        // $user->group_id = $group_id;

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            response()->json(['error' => 'Ehhh! Não pode eliminar utilizadores admin'], 403);
        }

        $currentUser = Auth::user();

        $roles = $currentUser->getRoleNames()->toArray();

        if (!in_array(['admin'], $roles)){
            response()->json(['error' => 'Não foi possível concluír o pedido.'], 403);
        }

        try {
            $user->delete();
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }

    /**
     * Get permissions from role
     *
     * @param User $user
     * @return array|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function permissions(User $user)
    {
        try {
            return new JsonResponse([
                'user' => PermissionResource::collection($user->getDirectPermissions()),
                'role' => PermissionResource::collection($user->getPermissionsViaRoles()),
            ]);
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules($isNew = true)
    {
        return [
            'name' => 'required',
            'email' => $isNew ? 'required|email|unique:users' : 'required|email',
            'roles' => [
                'required',
                'array'
            ],
        ];
    }


    public function updateAvatar(Request $request, User $user)
    {
        $validator = Validator::make(
            $request->all(),
            array(
                'avatar'   => ['image', 'dimensions:max_width=700,max_height=700'],
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
        
            $file = $request->file('avatar');
            $name = '/avatar/' . uniqid() . '.' . $file->extension();
            $file->storePubliclyAs('public', $name);

            $user->avatar = $name;
            $user->save();
            
            return new userResource($user);
        }
    }

    public function getAllClients()
    {
        $clients = User::query()->select('id', 'name')->where('type', 'cliente');
        
        foreach($clients as $client){
            $client['name'] = html_entity_decode($client['name']);
        }

        return array('data' => $clients->get());
    }

    public function getAllDoctors(Request $request)
    {
        $keyword = $request->get('query');
        $category_id = $request->get('category_id');
        // $limit = $request->get('limit');
        
        $doctors = User::query()
                    ->leftJoin('module_categories', 'users.category_id', 'module_categories.id')
                    ->select('users.id', 'users.name', 'users.category_id', 'module_categories.name as category_name')
                    ->where('users.type', 'terapeuta');

        if(!empty($keyword)){
            $doctors->where('users.name', 'LIKE', '%'. $keyword .'%');
        }

        if(!empty($category_id)){

            if($category_id != 1){
                $doctors->where('users.category_id', $category_id);
            }
        }

        if(!empty($limit)){

            $doctors->limit($limit);
            
        }
        

        foreach($doctors as $doctor){
            $doctor['name'] = html_entity_decode($doctor['name']);
        }
        return UserResource::collection($doctors->get());
        
    }

    public function updateAvailableHours(Request $request) {
        
        $user_id = $request->get('user_id');
        $new_hours = $request->get('hours');

        if(!$user_id){
            return response()->json(['error' => 'Utilizador não encontrado'], 403);
        }
        
        foreach($new_hours as $hour){
            
            if(!$this->validateHour($hour)){
                return response()->json(['error' => 'Formato de hora incorreto em "' . $hour['name'] . '". Formato correto "01:00"'], 403);
            } else {
                if($hour['active'] == true && strtotime($hour['start']) >= strtotime($hour['end'])){
                    return response()->json(['error' => 'Hora de ínicio maior que hora de fim: ' . $hour['name']], 403);
                }
            }
        }

        if(!serialize($new_hours)){
            return response()->json(['error' => 'Dados não definidos corretamente'], 403);
        }

        User::where('id', $user_id)->update(['available_hours' => serialize($new_hours)]);

        // dd($new_hours);
        return true;
    }

    private function validateHour($item){
        if($item['active'] == true){

            if(!$item['start'] || !$item['end'] || $item['start'] == '' || $item['end'] == '' ){
                return false;
            }
            
            if ($item['start'] && !preg_match("/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/", $item['start'])) {
                return false;
            }

            if ($item['end'] && !preg_match("/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/", $item['end'])) {
                return false;
            }
            return true;
        } 

        return true;
    }


    public function getClientData(Request $request){

        $client_data =  User::where('id', $request->client_id)->select('users.*')->first();

        $notes = DB::table('clinical_notes')->select('clinical_notes.*')->where('client_id', $request->client_id)
                                ->leftJoin('users', 'clinical_notes.created_by', 'users.id')
                                ->select('clinical_notes.*', 'users.name as created_by_user')
                                ->get();


        return array('client_data' => new UserResource($client_data), 'notes' => $notes);
    }

    public function getAvailableDoctors(Request $request){

        $validator = Validator::make(
            $request->all(),
            array(
                'session_id'   => ['required'],
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {

            $params = $request->all();

            // TODO: CHECK IF DURATION IS CORRECTLY SET

            // get session's confirmed date
            // $event_date = Event::where('session_id', $params['session_id'])
            //             ->where('confirmed', 1)
            //             ->selectRaw('date_start, ROUND(time_to_sec((TIMEDIFF(date_end, date_start))) / 60) as duration')
            //             ->orderBy('id', 'DESC')
            //             ->first()->toArray();

            // TODO: get available doctors
            $users = User::where('type', 'terapeuta')->orWhere('id', 1)->select('id as value', 'name as label', 
                    DB::raw('(SELECT IFNULL(ROUND(AVG(ratings.score),2),0) FROM ratings WHERE ratings.user_id = users.id  AND ratings.active = 1) as avg_rating'))->get();

            return array('data' => $users);
        }
    }

    // Get link to Reset password
    public function getReset(Request $request){

        $validator = Validator::make(
            $request->all(),
            array(
                'email'   => ['required'],
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $user = User::where('email', $params['email'])->first();
            if (isset($user) && $user['id']) {
                return $this->generateURL($user);
            } else {
                return response()->json(['errors' => 'Não existe um utilizador com esse email'], 403);
            }
        }
    }

    // Generate token to send to user on email
    private function generateURL($user) {
        $token = Str::random(60);

        $insertedReset = DB::table('password_resets')->insert([
            'email' => $user['email'],
            'token' => $token
        ]);
        $url = env('APP_URL') . '#/password?id=' . $token;
        $user['url'] = $url;
        
        if ($insertedReset) {
            return $this->sendResetPasswordMail($user);
        }
    }

    // sends an email to reset Password
    private function sendResetPasswordMail($user) {

        $details = [
            'email' => $user['email'],
            'username' => $user['name'],
            'url' => $user['url']
        ];

        Mail::to($user['email'])
        ->send(new ResetPassword($details));
        
        return 'Email enviado';
    }

    private function sendNewUserPasswordMail($user) {

        $details = [
            'email' => $user['email'],
            'username' => $user['name'],
            'url' => $user['url']
        ];

        Mail::to($user['email'])->send(new NewUserPassword($details));
        
        return 'Email enviado';
    }
    
    // return all users to be used in a select filter
    public function getAllUsers() {

        $currentUser = Auth::user();

        $roles = $currentUser->getRoleNames()->toArray();

        if (!in_array(['admin', 'gestor'], $roles)){
            $userQuery = User::select('name as label', 'id as value', 'type as type');
        }

        
        return array('data' => $userQuery->get());
    }
    
    private function sendRegistrationFinishedEmail($email, $url) {
    
        $details = [
            'title' => 'Registo efetuado',
            'subtitle' => 'Pode agora aceder à nossa plataforma',
            'email' => $email,
            'url' => $url
        ];

        Mail::to($email)->send(new RegistrationFinished($details));
        
        Log::info('Email de registo enviado para ' . $email);

        // return 'Email enviado';
        return true;
    }

    // sends an email invitation to all participants
    private function sendRegistrationStartedEmail($email, $url) {
    

        $details = [
            'title' => 'Obrigado',
            'subtitle' => 'por se registar na nossa plataforma',
            'email' => $email,
            'url' => $url
        ];

        Mail::to($email)->send(new UserRegistered($details));
        
        Log::info('Email de registo enviado para ' . $email);

        // return 'Email enviado';
        return true;
    }
    
    
    private function createPlanSession($plan_id, $module_id){

        if(!$module_id){
            return;
        }   

        // a sessão estratégica não é elinminada, verica-se se já existe para não existir duplicação
        if($module_id == 1 && PlanSession::where('module_id', 1)->where('plan_id', $plan_id)->exists()){
            return;
        }

        $plan_value = DB::table('plans')->where('id', $plan_id)->pluck('value')->first();
        $num_modules = DB::table('plans')->where('id', $plan_id)->pluck('num_modules')->first();

        $plan_session = PlanSession::create([
            'plan_id' => $plan_id,
            'module_id' => $module_id,
            'doctor_id' => null,
            'value' =>  round($plan_value / $num_modules, 2),
            'name' => DB::table('modules')->where('id', $module_id)->pluck('name')->first(),
        ]);

        $contents = DB::table('module_content')->where('module_id', $module_id)->get();

        foreach($contents as $content) {
            DB::table('session_content')->insert([
                'session_id'    => $plan_session->id,
                'title'         => $content->title,
                'content'       => $content->content,
                'url'           => $content->url,
                'type'          => $content->type,
                'file_id'       => $content->file_id,
                'public'        => $content->public ?? 0,
                'editable'      => 1,
                'order'         => $content->order,
            ]);
        }

        return true;
    }

    public function resetPassword(Request $request) {
        $validator = Validator::make(
            $request->all(),
            array(
                'email'   => ['required'],
                'password'   => ['required','min:6'],
                'confirm_password'   => ['required','same:password','min:6'],
                'token'   => ['required'],
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => 'Não foi possível validar os campos introduzidos'], 403);
        } else {
            $params = $request->all();

            $tokenGenerated = DB::table('password_resets')->where('token', 'like', $params['token'])->where('email', 'like', $params['email'])->first();
            // dd(isset($tokenGenerated) && !empty($tokenGenerated));
            if (isset($tokenGenerated) && !empty($tokenGenerated)) {
                $user = User::where('email', '=', $params['email'])->update([
                    'password' => Hash::make($params['password']),
                ]);
                $tokenGenerated = DB::table('password_resets')->where('token', '=', $params['token'])->where('email', '=', $params['email'])->delete();
                return $user;
            } else {
                return 0;
            }
        }
    }

    /**
     * Set a new user avatar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeAvatar(Request $request){

        $currentUser = Auth::user();
        $roles = $currentUser->getRoleNames()->toArray();

        if($currentUser->id !== intval($request->user) && (!in_array('admin', $roles) && !in_array('gestor', $roles))){
            return response()->json(['error' => 'Não possui permissões alterar esta informação'], 403);
        }

        $params = $request->all();

        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png|max:20480',
            'user' => 'required'
        ]);

        if ($request->file()) {

            $file_name = time() . '_' . $params['title'] . '.' . $request->file('file')->extension();
            $file_path = $request->file('file')->storeAs('avatars', $file_name, 'public');
            $newPath = '/storage/' . $file_path;

            $user = User::where('id', '=', $request->user)->update([
                'profile_photo_path' => $newPath
            ]);

            return $newPath;
        }
        return null;
    }

    // creates a notification about the event that just occured
    private function createNotification($user_id, $message, $reason = null, $url = null) {

        if(!$user_id){
            return;
        }

        Notification::create([
            'user_id' => $user_id,
            'description' => $message,
            'url' => $url
        ]);
        return true;
    }


    public function getTotalClients(){
        
        return array('total' => User::where('type', 'cliente')->count());
    }

    public function coachAndCoachee(Request $request) {
        $validator = Validator::make(
            $request->all(),
            
                [
                    'newRole' => ['required'],
                    'user_id' => ['required'],
                ]
        );

        $params = $request->all();
        // Get user from params
        $getUser = User::find($params['user_id']);

        $roles_DB = array('coach' => 'terapeuta', 'coachee' => 'cliente');

        // Get current User
        $currentUser = Auth::user();

        if(!$getUser->id || $getUser->id !== $currentUser->id){
            return response()->json(['error' => 'Não é possível alterar o papel deste utilizador'], 403);
        }

        // Update on table users
        $updateUser = User::where('id', $currentUser->id)
                ->update(['type' => $roles_DB[$params['newRole']]]);
        
        if ($updateUser){
            // Get role id and set on model_has_roles the new role
            $role_id = DB::table('roles')
                    ->select('id')
                    ->where('name', 'LIKE',  $roles_DB[$params['newRole']])
                    ->first();

            $updateModel = DB::table('model_has_roles')
                    ->where('model_id', '=',  $currentUser->id)
                    ->where('model_type', 'LIKE', 'App%Models%User')
                    ->update(['role_id' => $role_id->id]);
            if ($updateModel) {
                return response()->json(['msg' => 'Alterado com sucesso', 'status' => '200'], 200);
            } else {
                return response()->json(['error' => 'Não foi possível alterar o seu perfil.'], 403);
            }
        }
    }

    public function getAllDoctorsFormated(Request $request) {
        // Obter todos os doctors com a formatação para o autocomplete
        $users = User::where('type', 'terapeuta')->where('active', 1)->get();
        $result = array();
        foreach ($users as $user) {
            $values = null;
            $values['id'] = $user['id'];
            $values['value'] = $user['name'];
            array_push($result, $values);
        }
        return $result;
    }

}