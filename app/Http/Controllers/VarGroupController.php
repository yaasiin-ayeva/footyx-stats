<?php

namespace App\Http\Controllers;

use App\Models\VarGroup;
use App\Models\Variable;
use App\Models\VarLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass;

class VarGroupController extends BaseController
{
    public function index(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        $vars = Variable::get()->keyBy('id');

        return view('vg.index', compact('vars'));
    }

    protected function get_store_validator($data)
    {
        $rules = [
            'name' => 'bail|required|max:255',
            'vars' => [
                'bail',
                'required',
                'array',
                function($attribute, $vars, $fail) {
                    $unique_vars = array_unique($vars);

                    if(count($unique_vars) != count($vars)) {
                        $fail('An error occured');
                        return;
                    }
                    
                    if(Variable::whereIn('id', $unique_vars)->count() != count($unique_vars)) {
                        $fail('An error occured');
                        return;
                    }
                }
            ]
        ];

        $messages = [
        ];

        return Validator::make($data, $rules, $messages);
    }

    private function create_links($vg, $vars)
    {
        $links = [];

        foreach ($vars as $var) {
            $links[] = [
                'var_group_id' => $vg->id,
                'var' => $var
            ];
        }

        VarLink::insert($links);
    }

    public function store(Request $request)
    {
        if (Auth::user()->type != 'admin') {
			abort(405);
		}

        // Retrieve data

        $data = [
            'name' => $request->input('name'),
            'vars' => $request->input('vars'),
        ];

        Log::debug($request);

        // Validate data

        $validator = $this->get_store_validator($data);

        if($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();

            return $this->error($errors['vars'][0] ?? '', ['errors' => $errors]);
        }

        // Save the group

        $vg = VarGroup::create([
            'name' => $data['name'],
            'admin_id' => Auth::user()->admin->id,
        ]);

        // Create links

        $this->create_links($vg, $data['vars']);

        return $this->success("{$data['name']} has been created!");
    }

    protected function get_update_validator($data)
    {
        $rules = [
            'name' => 'bail|required|max:255',
            'vars' => [
                'bail',
                'required',
                'array',
                function($attribute, $vars, $fail) {
                    $unique_vars = array_unique($vars);

                    if(count($unique_vars) != count($vars)) {
                        $fail('An error occured');
                        return;
                    }
                    
                    if(Variable::whereIn('id', $unique_vars)->count() != count($unique_vars)) {
                        $fail('An error occured');
                        return;
                    }
                }
            ]
        ];

        $messages = [
        ];

        return Validator::make($data, $rules, $messages);
    }

    public function update(Request $request, $id)
    {
        if(Auth::user()->type != 'admin') {
			abort(405);
		}

        // Retrieve data

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
            'vars' => $request->input('vars'),

        ];

        // Validate data

        if(!($vg = VarGroup::find($id))) {
            return $this->error('Group not valid');
        }

        $validator = $this->get_update_validator($data);

        if($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();

            return $this->error($errors['vars'][0] ?? '', ['errors' => $errors]);
        }

        // Update the vg

        $vg->update([
            'name' => $data['name'],
        ]);

        // Adapt links

        $vg->var_links()->delete();
        $this->create_links($vg, $data['vars']);

        return $this->success("{$data['name']} has been updated!");
    }

    public function delete(Request $request, $id)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate data

        if(!($vg = VarGroup::find($id))) {
            return $this->error('Group not valid');
        }

        // Delete the vg

        $vg->var_links()->delete();
        $vg->delete();

        return $this->success("{$vg->name} has been dissolved!");
    }

    // Lists

    public function vg_list(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        $params = new stdClass;

        $params->searchColumns = ['name', 'var_count'];
        $params->orderColumns = ['id', 'name', 'var_count'];

        $subquery = DB::table('var_groups', 'vg')
        ->leftJoin('var_links AS vl', 'vg.id', 'var_group_id')
        ->selectRaw('
            vg.id,
            name,
            COUNT(vl.id) AS var_count,
            GROUP_CONCAT(var SEPARATOR " ") AS vars
        ')
        ->groupByRaw('vg.id, name');

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {        
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['name'] = $row->name;
            $r['var_count'] = $row->var_count;
            $r['vars'] = explode(' ', $row->vars);

            $r['actions'] = '
                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill edit" title="Edit"><i class="fas fa-edit"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill dissolve" title="Dissolve"><i class="fas fa-skull"></i></button>
            ';
            
            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }
}
