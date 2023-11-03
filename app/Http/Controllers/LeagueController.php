<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\League;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;

class LeagueController extends BaseController
{
    // List all leagues to the admin
    public function all(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        return view('leagues.all');
    }

    // List all leagues to the admin for a country
    public function index(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate the country

        if(!($cnt = Country::find($request->input('cnt')))) {
            abort(404);
        }

        return view('leagues.index', compact('cnt'));
    }

    // Validate league data for store
    protected function get_store_validator($data)
    {
        $rules = [
            'name' => 'bail|required|max:63|unique:leagues',
            'country_id' => 'bail|required|exists:countries,id',
        ];

        $messages = [
        ];

        return Validator::make($data, $rules, $messages);
    }

    // Save the league
    public function store(Request $request)
    {
        // Only an admin can create a league

        if (Auth::user()->type != 'admin') {
			abort(405);
		}

        // Retrieve data

        $data = [
            'name' => $request->input('name'),
            'country_id' => $request->input('country_id'),
        ];

        // Validate data

        $validator = $this->get_store_validator($data);

        if($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }

        // Save the league

        League::create([
            'name' => $data['name'],
            'country_id' => $data['country_id'],
        ]);

        return $this->success("{$data['name']} has been added!");
    }

    // Validate the league data for update
    protected function get_update_validator($data)
    {
        $rules = [
            'name' => "bail|required|max:63|unique:leagues,name,{$data['id']}",
        ];

        $messages = [
        ];

        return Validator::make($data, $rules, $messages);
    }

    // Update the league data
    public function update(Request $request, $id)
    {
        // Only the admin can update a league

        if(Auth::user()->type != 'admin') {
			abort(405);
		}

        // Retrieve data

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
        ];

        // Validate data

        if(!($lg = League::find($id))) {
            return $this->error('The league is not valid');
        }

        $validator = $this->get_update_validator($data);

        if($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }

        // Update the lg

        $lg->update([
            'name' => $data['name'],
        ]);

        return $this->success("{$data['name']} has been updated!");
    }

    // Delete the lg
    public function delete(Request $request, $id)
    {
        // Only an admin can delete an lg

        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate data

        if(!($lg = League::find($id))) {
            return $this->error('The league is not valid');
        }

        // Delete the lg

        $lg->admin_files()->delete();
        $lg->admin_file_matches()->delete();
        $lg->delete();

        return $this->success("{$lg->name} has been deleted!");
    }

    // Lists

    public function lg_list(Request $request)
    {
        // Only an admin can get lg list

        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate the country

        if(!($cnt = Country::find($request->input('cnt')))) {
            abort(404);
        }

        $params = new stdClass;

        $params->searchColumns = ['name'];
        $params->orderColumns = ['id', 'name'];

        $subquery = DB::table('leagues', 'lg')
        ->selectRaw('
            id,
            name
        ')
        ->where('country_id', $cnt->id);

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {        
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['name'] = $row->name;
            $r['name_link'] = '<a href="'.route('adf.index', ['lg' => $row->id]).'">'.$row->name.'</a>';

            $r['actions'] = '
                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill edit" title="Edit"><i class="fas fa-edit"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill delete" title="Delete"><i class="fas fa-trash"></i></button>
            ';
            
            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }

    public function lg_all_list(Request $request)
    {
        // Only an admin can get lg list

        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        $params = new stdClass;

        $params->searchColumns = ['name', 'country'];
        $params->orderColumns = ['id', 'name', 'country'];

        $subquery = DB::table('leagues', 'lg')
        ->leftJoin('countries AS cnt', 'country_id', 'cnt.id')
        ->selectRaw('
            lg.id,
            lg.name,
            cnt.name AS country,
            code,
            country_id
        ');

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {        
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['name'] = $row->name;
            $r['name_link'] = '<a href="'.route('adf.index', ['lg' => $row->id]).'">'.$row->name.'</a>';

            $r['flag_name'] = '
                <span>
                    <img style="display: inline;"
                        src="https://flagcdn.com/16x12/'.$row->code.'.png"
                        srcset="https://flagcdn.com/32x24/'.$row->code.'.png 2x,
                        https://flagcdn.com/48x36/'.$row->code.'.png 3x"
                        width="16"
                        height="12"
                        alt="'.$row->code.'">
                </span>
                <a href="'.route('lg.index', ['cnt' => $row->country_id]).'">'.$row->country.'</a>
            ';

            $r['actions'] = '
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill delete" title="Delete"><i class="fas fa-trash"></i></button>
            ';
            
            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }
}
