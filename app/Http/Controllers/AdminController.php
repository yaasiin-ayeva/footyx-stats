<?php

namespace App\Http\Controllers;

use App\Models\AdminFile;
use App\Models\AdminFileMatch;
use App\Models\Country;
use App\Models\League;
use App\Models\Player;
use App\Models\VarGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends BaseController
{
	public function get_dashboard(Request $request)
	{
		if(Auth::user()->type != 'admin') {
			abort(405);
		}

		$players = Player::count();
		$countries = Country::count();
		$leagues = League::count();
		$files = AdminFile::count();
		$matches = AdminFileMatch::count();
		$vg = VarGroup::count();

		return view('dashboards.admin', compact('players', 'countries', 'leagues', 'files', 'matches', 'vg'));
	}
}