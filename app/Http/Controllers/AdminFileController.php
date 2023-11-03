<?php

namespace App\Http\Controllers;

use App\Models\AdminFile;
use App\Models\AdminFileMatch;
use App\Models\Country;
use App\Models\League;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Shuchkin\SimpleXLSX;
use stdClass;

class AdminFileController extends BaseController
{
    // List all files loaded by admins
    public function all(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        return view('adf.all');
    }

    // List all files loaded by admins
    public function index(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate the league

        if(!($lg = League::find($request->input('lg')))) {
            abort(404);
        }

        return view('adf.index', compact('lg'));
    }

    // List all matches for a file
    public function get_matches(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate the file

        $adf = AdminFile::find($request->input('adf'));

        if (!$adf) {
            abort(404);
        }

        return view('adf.matches', compact('adf'));
    }

    // Get file loading page
    public function get_load()
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        $countries = Country::orderBy('name')->get();

        return view('adf.load', compact('countries'));
    }

    // Save the admin file
    public function save_file($adf, $file) {
        // Generate paths

        $xlsxPath = storage_path('app/' . Storage::putFileAs('tmp', $file, uniqid(rand(), true).'.xlsx'));

        // Open XLSX File

        if(!($xlsx = SimpleXLSX::parse($xlsxPath))) {
            return 'Unable to parse the XLSX File: ' . SimpleXLSX::parseError();
        }

        // Put all matches inside a collection

        $matches = collect();
        $teams = [];

        $var_gen = new VariableGenerator(Variable::get()->keyBy('code'));

        foreach ($xlsx->rows() as $key => $row) {
            if($key == 0) {
                continue;
            }

            $match = (object)$var_gen->map_row($row);

            $match->admin_file_id = $adf->id;
            $match->league_id = $adf->league_id;
            $match->country_id = $adf->country_id;
            $match->admin_id = $adf->admin_id;

            $match->day = Carbon::parse($match->date)->format('Y-m-d');

            $matches->push($match);

            // Add teams

            $teams[$match->home] = 0;
            $teams[$match->away] = 0;
        }

        // Get teams

        $teams = collect(array_keys($teams))->sort()->map(fn($name) => (object)compact('name'));

        // For all matches, only consider all matches of previous days
        // Generate variables

        $final = [];

        foreach ($matches as $match) {
            $considered = $matches->where('day', '<', $match->day)->values();

            for($i = 1; $i <= 20; $i++) {
                $match->{"x$i"} = null;
                $match->{"y$i"} = null;

                if($i <= 10) {
                    $match->{"z$i"} = null;
                }
            }

            $match->valid = $considered->isNotEmpty() && $var_gen->fill_row($match, $considered, $teams);

            $final[] = Arr::except((array)$match, ['day']);
        }
        
        AdminFileMatch::insert($final);

        // Delete temp files
        // unlink($xlsxPath);

        return '';
    }

    protected function getLoadValidator($data) {
        $rules = [
            'league_id' => 'bail|required|exists:leagues,id',
            'files.*' => 'bail|required|file'
        ];

        $messages = [

        ];

        return Validator::make($data, $rules, $messages);
    }

    public function load(Request $request)
    {
        set_time_limit(0);

        if (!in_array(Auth::user()->type, ['admin'])) {
            abort(405);
        }

        // Retrieve the data

        $data = [
            'league_id' => $request->input('league_id'),
            'files' => $request->file('files'),
        ];

        // Validate the data

        $validator = $this->getLoadValidator($data);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->getMessageBag()->toArray()]);
        }

        $lg = League::find($data['league_id']);

        // For each file
        foreach ($data['files'] as $file) {
            // Check the extension
            
            $ext = strtolower($file->getClientOriginalExtension());
            
            if(!in_array($ext, ['xlsx'])) {
                return $this->error('XLSX file expected');
            }
            
            // Create adf
            
            $adf = AdminFile::create([
                'name' => $file->getClientOriginalName(),
                'country_id' => $lg->country_id,
                'league_id' => $lg->id,
                'admin_id' => Auth::user()->admin->id
            ]);
            
            // Save the file content
            
            if($feedback = $this->save_file($adf, $file)) {
                return $this->error($feedback);
            }
        }

        return $this->success('Files loaded successfully');
    }

    // Delete an admin file
    public function delete(Request $request, $id)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate data

        if(!($adf = AdminFile::find($id))) {
            return $this->error('The file is not valid');
        }

        // Delete the file

        $adf->admin_file_matches()->delete();
        $adf->delete();

        return $this->success("The file has been deleted!");
    }

    // Lists

    public function adf_list(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate the league

        if(!($lg = League::find($request->input('lg')))) {
            abort(404);
        }

        $params = new stdClass;

        $params->searchColumns = ['name', 'loader'];
        $params->orderColumns = ['id', 'name', 'loader'];

        $subquery = DB::table('admin_files', 'adf')
        ->leftJoin('admins', 'admin_id', 'admins.id')
        ->leftJoin('users', 'user_id', 'users.id')
        ->selectRaw('
            adf.id,
            adf.name,
            users.name AS loader
        ')
        ->where('league_id', $lg->id);

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['name'] = $row->name;
            $r['name_link'] = '<a href="'.route('adf.get_matches', ['adf' => $row->id]).'">'.$row->name.'</a>';

            $r['loader'] = $row->loader;

            $r['actions'] = '
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill delete" title="Delete"><i class="fas fa-trash"></i></button>
            ';
            
            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }

    public function adf_all_list(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        $params = new stdClass;

        $params->searchColumns = ['name', 'league', 'country', 'loader'];
        $params->orderColumns = ['id', 'name', 'league', 'country', 'loader'];

        $subquery = DB::table('admin_files', 'adf')
        ->leftJoin('leagues AS lg', 'league_id', 'lg.id')
        ->leftJoin('countries AS cnt', 'adf.country_id', 'cnt.id')
        ->leftJoin('admins', 'admin_id', 'admins.id')
        ->leftJoin('users', 'user_id', 'users.id')
        ->selectRaw('
            adf.id,
            adf.name,
            league_id,
            lg.name AS league,
            cnt.code,
            adf.country_id,
            cnt.name AS country,
            users.name AS loader
        ');

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['name'] = $row->name;
            $r['name_link'] = '<a href="'.route('adf.get_matches', ['adf' => $row->id]).'">'.$row->name.'</a>';

            $r['league'] = '<a href="'.route('adf.index', ['lg' => $row->league_id]).'">'.$row->league.'</a>';

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

            $r['loader'] = $row->loader;

            $r['actions'] = '
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill delete" title="Delete"><i class="fas fa-trash"></i></button>
            ';
            
            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }

    public function matches_list(Request $request)
    {
        if (Auth::user()->type != 'admin') {
            abort(405);
        }

        // Validate the file

        $adf = AdminFile::find($request->input('adf'));

        if (!$adf) {
            abort(404);
        }

        $params = new stdClass;
        
        $params->searchColumns = ['year', 'time', 'home', 'away', 'score'];
        $params->orderColumns = ['id', 'year', 'time', 'home', 'away', 'score'];

        $subquery = DB::table('admin_file_matches')
        ->selectRaw('
            id,
            date,
            
            year,
            time,
            home,
            away,
            score
        ')
        ->where('admin_file_id', $adf->id);

        $params->builder = DB::query()->fromSub($subquery, 'sub');

        $params->rowsCallback = function ($row) {        
            $r['id'] = $row->id;
            $r['__no__'] = $row->__no__;
            $r['date'] = $row->date;
            
            $r['year'] = $row->year;
            $r['time'] = $row->time;
            $r['home'] = $row->home;
            $r['away'] = $row->away;
            $r['score'] = $row->score;

            return $r;
        };

        return $this->fetch_list_data($request, $params);
    }
}
