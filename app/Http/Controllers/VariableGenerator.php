<?php

namespace App\Http\Controllers;

use stdClass;

class VariableGenerator
{
	// Row Mapping

	const YEAR_POS = 0;
	const TIME_POS = 1;
	const HOME_POS = 2;
	const AWAY_POS = 3;
	const SCORE_POS = 4;

	private $vars = null;

	public function __construct($vars)
	{
		$this->vars = $vars;
	}

	public function map_row($row)
	{
		$year = intval($row[static::YEAR_POS]);

		$time = str_replace(chr(194).chr(160), ' ', $row[static::TIME_POS]);

		$t = explode('.', $time);
		$t_0 = trim($t[0]);
		$t_1 = trim($t[1]);

		$t_2 = explode(':', $t[2]);
		$t_2_0 = trim($t_2[0]);
		$t_2_1 = trim($t_2[1]);

		$day =  intval($row[static::YEAR_POS])
				. '-' . str_pad(intval($t_1), 2, '0', STR_PAD_LEFT)
				. '-' . str_pad(intval($t_0), 2, '0', STR_PAD_LEFT);

		$date =  $day
				. ' ' . str_pad(intval($t_2_0), 2, '0', STR_PAD_LEFT)
				. ':' . str_pad(intval($t_2_1), 2, '0', STR_PAD_LEFT)
				. ':00';

		$home = trim($row[static::HOME_POS]);
		$away = trim($row[static::AWAY_POS]);

		$score = str_replace(chr(194).chr(160), ' ', $row[static::SCORE_POS]);

		$s = explode(':', $score);
		$s_0 = trim($s[0]);
		$s_1 = trim($s[1]);

		$home_goals = intval($s_0);
		$away_goals = intval($s_1);

		$winner = null;

		if($home_goals > $away_goals) {
				$winner = $home;
		}
		else if($away_goals > $home_goals) {
				$winner = $away;
		}

		return (object)compact('year', 'time', 'home', 'away', 'score', 'date', 'home_goals', 'away_goals', 'winner');
	}

	private $points = [
		'win' => [
			'first' => 5,
			'diff' => -0.02
		],
		'draw' => [
			'first' => 3.58,
			'diff' => -0.02
		],
		'lost' => [
			'first' => 0.02,
			'diff' => 0.02
		],
		'defense' => [
			'first' => -2,
			'diff' => 0.02
		],
	];

	private function get_points($res, $index)
	{
		return $this->points[$res]['first'] + ($index - 1) * $this->points[$res]['diff'];
	}

	public function fill_row($row, $matches, $teams)
	{
		$v = new stdClass;

		$v->x1 = $this->total_points($row->home, $matches);
		$v->y1 = $this->total_points($row->away, $matches);

		$v->x2 = $this->total_goals($row->home, $matches);
		$v->y2 = $this->total_goals($row->away, $matches);

		$v->x3 = $this->total_defense($row->home, $matches);
		$v->y3 = $this->total_defense($row->away, $matches);

		$v->x4 = $this->matches_played($row->home, $matches, $teams);
		$v->y4 = $this->matches_played($row->away, $matches, $teams);

		$v->x5 = $this->last_results($row->home, $matches);
		$v->y5 = $this->last_results($row->away, $matches);

		$v->x6 = $this->last_goals_scored($row->home, $matches);
		$v->y6 = $this->last_goals_scored($row->away, $matches);

		$v->x7 = $this->last_goals_conceded($row->home, $matches);
		$v->y7 = $this->last_goals_conceded($row->away, $matches);

		$v->x8 = $this->goals_scored_moving_average($row->home, $matches);
		$v->y8 = $this->goals_scored_moving_average($row->away, $matches);

		$v->x9 = $this->goals_conceded_moving_average($row->home, $matches);
		$v->y9 = $this->goals_conceded_moving_average($row->away, $matches);

		$v->x10 = $this->last_opponent_total_points($row->home, $matches);
		$v->y10 = $this->last_opponent_total_points($row->away, $matches);

		$v->x11 = $this->last_opponent_total_goals($row->home, $matches);
		$v->y11 = $this->last_opponent_total_goals($row->away, $matches);

		$v->x12 = $this->last_opponent_total_defense($row->home, $matches);
		$v->y12 = $this->last_opponent_total_defense($row->away, $matches);

		$shadow_score = $this->shadow_score($row->home, $row->away, $matches);
		$v->x13 = $shadow_score->team_shadow_score;
		$v->y13 = $shadow_score->opponent_shadow_score;

		$rate_1 = $this->rate_1($row->home, $row->away, $matches);
		$v->x14 = $rate_1->team_rate_1;
		$v->y14 = $rate_1->opponent_rate_1;

		$rate_2 = $this->rate_2($row->home, $row->away, $matches);
		$v->x15 = $rate_2->team_rate_2;
		$v->y15 = $rate_2->opponent_rate_2;

		$global_score = $this->global_score($row->home, $row->away, $matches);
		$v->x16 = $global_score->team_global_score;
		$v->y16 = $global_score->opponent_global_score;

		$v->x17 = $this->logarithm($row->home, $matches);
		$v->y17 = $this->logarithm($row->away, $matches);

		$v->x18 = $this->last_6_results($row->home, $matches);
		$v->y18 = $this->last_6_results($row->away, $matches);

		$v->x19 = $this->goal_scored_ratio($row->home, $matches);
		$v->y19 = $this->goal_scored_ratio($row->away, $matches);

		$v->x20 = $this->highest_goal_scored_ratio($row->home, $matches, $teams);
		$v->y20 = $this->highest_goal_scored_ratio($row->away, $matches, $teams);

		$v->z1 = $this->t_test_1($row->home, $row->away, $matches, $teams);
		$v->z2 = $this->t_test_2($row->home, $row->away, $matches, $teams);
		$v->z3 = $this->confidence($row->home, $row->away, $matches, $teams);
		$v->z4 = $this->linear($row->home, $row->away, $matches, $teams);
		$v->z5 = $this->insta_points($row->home, $row->away, $matches, $teams);
		$v->z6 = $this->insta_points_rate($row->home, $row->away, $matches, $teams);
		$v->z7 = $this->insta_goals($row->home, $row->away, $matches, $teams);
		$v->z8 = $this->insta_goals_rate($row->home, $row->away, $matches, $teams);
		$v->z9 = $this->insta_defense($row->home, $row->away, $matches, $teams);
		$v->z10 = $this->insta_defense_rate($row->home, $row->away, $matches, $teams);

		foreach ((array)$v as $key => $value) {
			if($value === null) {
				$result = false;
			}

			$row->$key = $value;
		}

		return $result ?? true;
	}

	//********************** Home-Away Variables **********************

	// 1 - total point = sum(win point | draw point | lost point) 1 dec place
	public function total_points($team, $matches, $from = 0, $size = null)
	{
		$n = 1;

		$total =  0;

		$c = $matches->count();

		for ($i = $from; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team || $match->away == $team) {
				$res = '';

				if ($match->winner == $team) {
					$res = 'win';
				} else if ($match->winner === null) {
					$res = 'draw';
				} else {
					$res = 'lost';
				}

				$total += $this->get_points($res, $n);

				if ($size !== null && $n == $size) {
					break;
				}

				$n++;
			}
		}

		if ($size !== null && $n < $size) {
			return null;
		}

		if ($total === 0) {
			return null;
		}

		return round($total, $this->vars['HTP']->dec_place);
	}

	// 2 - total goals = sum((goal_scored + 1) * win_point) 1 dec place
	public function total_goals($team, $matches, $from = 0, $size = null)
	{
		$n = 1;

		$total =  0;

		$c = $matches->count();

		for ($i = $from; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team || $match->away == $team) {
				$goals = ($match->home == $team ? $match->home_goals : $match->away_goals) + 1;

				$total += $goals * $this->get_points('win', $n);

				if ($size !== null && $n == $size) {
					break;
				}

				$n++;
			}
		}

		if ($size !== null && $n < $size) {
			return null;
		}

		if ($total === 0) {
			return null;
		}

		return round($total, $this->vars['HTG']->dec_place);
	}

	// 3 - total defense = sum((goal_conceded + 1) * negative_points) 1 dec place
	public function total_defense($team, $matches, $from = 0, $size = null)
	{
		$n = 1;

		$total =  0;

		$c = $matches->count();

		for ($i = $from; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team || $match->away == $team) {
				$goals = ($match->home == $team ? $match->away_goals : $match->home_goals) + 1;

				$total += $goals * $this->get_points('defense', $n);

				if ($size !== null && $n == $size) {
					break;
				}

				$n++;
			}
		}

		if ($total === 0) {
			return null;
		}

		return round($total, $this->vars['HTD']->dec_place);
	}

	// 4 - matches played = match played / total matches to be played in the league (2 dec place)
	// Example, with 20 teams, each team will play 38 (19 * 2) matches. if play 5, we get 5 / 38 (2 dec place)
	public function matches_played($team, $matches, $teams, $from = 0)
	{
		$n = 0;

		$c = $matches->count();

		for ($i = $from; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team || $match->away == $team) {
				$n++;
			}
		}

		$result = $n / (($teams->count() - 1) * 2);

		return round($result, $this->vars['HMP']->dec_place);
	}

	// 5 - last results = 3 if he won its last match, 2 if draw, 1 if lost
	public function last_results($team, $matches)
	{
		$c = $matches->count();

		for ($i = 0; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team || $match->away == $team) {
				if ($match->winner == $team) {
					return 3;
				} else if ($match->winner === null) {
					return 2;
				}

				return 1;
			}
		}
	}

	// 6 - last goals scored = number of goals scored during its last match
	public function last_goals_scored($team, $matches)
	{
		$c = $matches->count();

		for ($i = 0; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team) {
				return $match->home_goals;
			} else if ($match->away == $team) {
				return $match->away_goals;
			}
		}
	}

	// 7 - last conceded = number of goals conceded during its last match
	public function last_goals_conceded($team, $matches)
	{
		$c = $matches->count();

		for ($i = 0; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team) {
				return $match->away_goals;
			} else if ($match->away == $team) {
				return $match->home_goals;
			}
		}
	}

	// 8 - goals scored moving average = last 6 matches moving average (average of average 2 - 2 consecutive) (2 dec place)
	public function goals_scored_moving_average($team, $matches)
	{
		$result = $this->goals_moving_func($team, $matches, 'scored', 'average');

		return round($result, $this->vars['HGSMA']->dec_place);
	}

	// 9 - goals conceded moving average = same like goals scored moving average except that it is the goals conceded (negative, multiplied by -1)
	public function goals_conceded_moving_average($team, $matches)
	{
		$result = $this->goals_moving_func($team, $matches, 'conceded', 'average');
		
		return round($result, $this->vars['HGCMA']->dec_place);
	}

	// 10 - last opponent's "total point" (except that last match) 1 dec place
	public function last_opponent_total_points($team, $matches)
	{
		$opp = $this->last_opponent($team, $matches);

		if($opp === null) {
			return null;
		}

		$result = $this->total_points($opp->team, $matches, $opp->match_index + 1);

		return round($result, $this->vars['HLOTP']->dec_place);
	}

	// 11 - last opponent's "total goals" (except that last match) 1 dec place
	public function last_opponent_total_goals($team, $matches)
	{
		$opp = $this->last_opponent($team, $matches);

		if($opp === null) {
			return null;
		}

		$result = $this->total_goals($opp->team, $matches, $opp->match_index + 1);

		return round($result, $this->vars['HLOTG']->dec_place);
	}

	// 12 - last opponent's "total defense" (except that last match) 1 dec place
	public function last_opponent_total_defense($team, $matches)
	{
		$opp = $this->last_opponent($team, $matches);

		if($opp === null) {
			return null;
		}

		$result = $this->total_defense($opp->team, $matches, $opp->match_index + 1);

		return round($result, $this->vars['HLOTD']->dec_place);
	}

	// 13 - shadow score (1 dec place)

	// For the team and its opponent...
	// Identify the last 2 opponents

	// Get the team total points (total_points)

	// Obtain for each opponent the total points before the last match against the team
	// Get the average (avg_total_points_opponents)

	// Obtain the total goals conceded by the team from the 2 opponents
	// Add 1 to the total goals conceded
	// Divide it by 2 (total_goals_conceded)

	// home.shadow_score = home.total_points * away.total_goals_conceded / away.avg_total_points_opponents

	public function shadow_score($team, $opponent, $matches)
	{
		$shadow_elements = function ($team) use ($matches) {
			// Identify the last 2 opponents

			$last_2_opponents = [];

			$c = $matches->count();

			for ($i = 0; $i < $c; $i++) {
				$match = $matches[$i];

				if ($match->home == $team || $match->away == $team) {
					$last_2_opponents[] = (object)[
						'team' => $match->home == $team ? $match->away : $match->home,
						'match_index' => $i,
						'goals_conceded' => $match->home == $team ? $match->away_goals : $match->home_goals
					];

					if (count($last_2_opponents) === 2) {
						break;
					}
				}
			}

			$c = count($last_2_opponents);

			if ($c < 2) {
				return null;
			}

			// Get the team total points

			$total_points = $this->total_points($team, $matches);

			// Obtain for each opponent the total points before the last match against the team
			// Get the average (avg_total_points_opponents)

			$avg_total_points_opponents = 0;

			for ($i = 0; $i < 2; $i++) {
				$op = $last_2_opponents[$i];

				$total_points = $this->total_points($op->team, $matches, $op->match_index + 1);

				if ($total_points === null) {
					return null;
				}

				$avg_total_points_opponents += $total_points;
			}

			$avg_total_points_opponents /= 2;

			// Obtain the total goals conceded by the team from the 2 opponents
			// Add 1 to the total goals conceded
			// Divide it by 2 (total_goals_conceded)

			$total_goals_conceded = 0;

			for ($i = 0; $i < 2; $i++) {
				$total_goals_conceded += $last_2_opponents[$i]->goals_conceded;
			}

			$total_goals_conceded += 1;
			$total_goals_conceded /= 2;

			return (object)compact(
				'total_points',
				'avg_total_points_opponents',
				'total_goals_conceded'
			);
		};

		$team_shadow_elements = $shadow_elements($team);

		if ($team_shadow_elements === null) {
			return (object)[
				'team_shadow_score' => null,
				'opponent_shadow_score' => null
			];
		}

		$opponent_shadow_elements = $shadow_elements($opponent);

		if ($opponent_shadow_elements === null) {
			return (object)[
				'team_shadow_score' => null,
				'opponent_shadow_score' => null
			];
		}

		if (
			$opponent_shadow_elements->avg_total_points_opponents == 0 ||
			$team_shadow_elements->avg_total_points_opponents == 0
		) {
			return (object)[
				'team_shadow_score' => null,
				'opponent_shadow_score' => null
			];
		}

		// home.shadow_score = home.total_points * away.total_goals_conceded / away.avg_total_points_opponents

		$team_shadow_score = ($team_shadow_elements->total_points * $opponent_shadow_elements->total_goals_conceded / $opponent_shadow_elements->avg_total_points_opponents);

		$opponent_shadow_score = ($opponent_shadow_elements->total_points * $team_shadow_elements->total_goals_conceded / $team_shadow_elements->avg_total_points_opponents);

		$team_shadow_score = round($team_shadow_score, $this->vars['HSS']->dec_place);
		$opponent_shadow_score = round($opponent_shadow_score, $this->vars['HSS']->dec_place);

		return (object)compact(
			'team_shadow_score',
			'opponent_shadow_score'
		);
	}

	// 14 - home rate 1
	public function rate_1($team, $opponent, $matches)
	{
		$shadow_score = $this->shadow_score($team, $opponent, $matches);

		if ($shadow_score->team_shadow_score === null || $shadow_score->opponent_shadow_score === null) {
			return (object)[
				'team_rate_1' => null,
				'opponent_rate_1' => null
			];
		}

		$rate_1_elements = function ($team) use ($matches) {
			$total_points = $this->total_points($team, $matches);

			if ($total_points === null) {
				return null;
			}

			$last_results = $this->last_results($team, $matches);

			if ($last_results === null) {
				return null;
			}

			$last_opponent_total_points = $this->last_opponent_total_points($team, $matches);

			if ($last_opponent_total_points === null) {
				return null;
			}

			$goals_scored_moving_average = $this->goals_scored_moving_average($team, $matches);

			if ($goals_scored_moving_average === null) {
				return null;
			}

			$goals_scored_moving_std_dev = $this->goals_scored_moving_std_dev($team, $matches);

			if ($goals_scored_moving_std_dev === null) {
				return null;
			}

			return (object)compact(
				'total_points',
				'last_results',
				'last_opponent_total_points',
				'goals_scored_moving_average',
				'goals_scored_moving_std_dev'
			);
		};

		$team_elements = $rate_1_elements($team);

		if ($team_elements === null) {
			return (object)[
				'team_rate_1' => null,
				'opponent_rate_1' => null
			];
		}

		$opponent_elements = $rate_1_elements($opponent);

		if ($opponent_elements === null) {
			return (object)[
				'team_rate_1' => null,
				'opponent_rate_1' => null
			];
		}

		if ($team_elements->last_opponent_total_points == 0 ||
			$opponent_elements->last_opponent_total_points == 0) {
			return (object)[
				'team_rate_1' => null,
				'opponent_rate_1' => null
			];
		}

		$team_rate_1 = (
			($team_elements->total_points / $team_elements->last_opponent_total_points) *
			$team_elements->last_results *
			$shadow_score->team_shadow_score *
			$team_elements->goals_scored_moving_average
		) - $team_elements->goals_scored_moving_std_dev;

		$opponent_rate_1 = (
			($opponent_elements->total_points / $opponent_elements->last_opponent_total_points) *
			$opponent_elements->last_results *
			$shadow_score->opponent_shadow_score *
			$opponent_elements->goals_scored_moving_average
		) - $opponent_elements->goals_scored_moving_std_dev;

		$team_rate_1 = round($team_rate_1, $this->vars['HR1']->dec_place);
		$opponent_rate_1 = round($opponent_rate_1, $this->vars['HR1']->dec_place);

		return (object)compact(
			'team_rate_1',
			'opponent_rate_1'
		);
	}

	// 15 - home rate 2
	public function rate_2($team, $opponent, $matches)
	{
		$shadow_score = $this->shadow_score($team, $opponent, $matches);

		if ($shadow_score->team_shadow_score === null || $shadow_score->opponent_shadow_score === null) {
			return (object)[
				'team_rate_2' => null,
				'opponent_rate_2' => null
			];
		}

		$rate_2_elements = function ($team) use ($matches) {
			$last_results = $this->last_results($team, $matches);

			if ($last_results === null) {
				return null;
			}

			$last_opp_tp = $this->last_opponent_total_points($team, $matches);

			if ($last_opp_tp === null) {
				return null;
			}

			$goals_scored_moving_avg = $this->goals_scored_moving_average($team, $matches);

			if ($goals_scored_moving_avg === null) {
				return null;
			}

			$goals_scored_moving_std_dev = $this->goals_scored_moving_std_dev($team, $matches);

			if ($goals_scored_moving_std_dev === null) {
				return null;
			}

			return (object)compact(
				'last_results',
				'last_opp_tp',
				'goals_scored_moving_avg',
				'goals_scored_moving_std_dev'
			);
		};

		$team_el = $rate_2_elements($team);

		if ($team_el === null) {
			return (object)[
				'team_rate_2' => null,
				'opponent_rate_2' => null
			];
		}

		$opp_el = $rate_2_elements($opponent);

		if ($opp_el === null) {
			return (object)[
				'team_rate_2' => null,
				'opponent_rate_2' => null
			];
		}

		if ($opp_el->last_opp_tp == 0 || $team_el->last_opp_tp == 0) {
			return (object)[
				'team_rate_2' => null,
				'opponent_rate_2' => null
			];
		}

		$team_rate_2 = (
			($team_el->last_opp_tp / $opp_el->last_opp_tp) *
			$team_el->last_results *
			$shadow_score->team_shadow_score *
			$team_el->goals_scored_moving_avg
		) - $team_el->goals_scored_moving_std_dev;

		$opponent_rate_2 = (
			($opp_el->last_opp_tp / $team_el->last_opp_tp) *
			$opp_el->last_results *
			$shadow_score->opponent_shadow_score *
			$opp_el->goals_scored_moving_avg
		) - $opp_el->goals_scored_moving_std_dev;

		$team_rate_2 = round($team_rate_2, $this->vars['HR2']->dec_place);
		$opponent_rate_2 = round($opponent_rate_2, $this->vars['HR2']->dec_place);

		return (object)compact(
			'team_rate_2',
			'opponent_rate_2'
		);
	}

	// 16 - Global score (1 dec place)
	public function global_score($team, $opponent, $matches)
	{
		$global_elements = function ($team) use ($matches) {
			// Identify the last 2 opponents

			$last_2_opponents = [];

			$c = $matches->count();

			for ($i = 0; $i < $c; $i++) {
				$match = $matches[$i];

				if ($match->home == $team || $match->away == $team) {
					$last_2_opponents[] = (object)[
						'team' => $match->home == $team ? $match->away : $match->home,
						'match_index' => $i,
						'goals_conceded' => $match->home == $team ? $match->away_goals : $match->home_goals
					];

					if (count($last_2_opponents) === 2) {
						break;
					}
				}
			}

			$c = count($last_2_opponents);

			if ($c < 2) {
				return null;
			}

			// Get the team sum_totals (total_points * 0.5) + (total_goals * 0.3) + (total_defense * 0.2)

			$tp = $this->total_points($team, $matches);
			$tg = $this->total_goals($team, $matches);
			$td = $this->total_defense($team, $matches);

			if ($tp === null || $tg === null || $td === null) {
				return null;
			}

			$sum_totals = $tp * 0.5 + $tg * 0.3 + $td * 0.2;

			// Obtain for each opponent the sum_totals before the last match against the team
			// Get the average (avg_sum_totals_opponents)

			$avg_sum_totals_opponents = 0;

			for ($i = 0; $i < 2; $i++) {
				$op = $last_2_opponents[$i];

				$tp = $this->total_points($op->team, $matches, $op->match_index + 1);
				$tg = $this->total_goals($op->team, $matches, $op->match_index + 1);
				$td = $this->total_defense($op->team, $matches, $op->match_index + 1);

				if ($tp === null || $tg === null || $td === null) {
					return null;
				}

				$avg_sum_totals_opponents += $tp * 0.5 + $tg * 0.3 + $td * 0.2;
			}

			$avg_sum_totals_opponents /= 2;

			// Obtain the total goals conceded by the team from the 2 opponents
			// Add 1 to the total goals conceded
			// Divide it by 2 (total_goals_conceded)

			$total_goals_conceded = 0;

			for ($i = 0; $i < 2; $i++) {
				$total_goals_conceded += $last_2_opponents[$i]->goals_conceded;
			}

			$total_goals_conceded += 1;
			$total_goals_conceded /= 2;

			return (object)compact(
				'sum_totals',
				'avg_sum_totals_opponents',
				'total_goals_conceded'
			);
		};

		$team_global_elements = $global_elements($team);

		if ($team_global_elements === null) {
			return (object)[
				'team_global_score' => null,
				'opponent_global_score' => null
			];
		}

		$opponent_global_elements = $global_elements($opponent);

		if ($opponent_global_elements === null) {
			return (object)[
				'team_global_score' => null,
				'opponent_global_score' => null
			];
		}

		if(
			$opponent_global_elements->avg_sum_totals_opponents == 0 ||
			$team_global_elements->avg_sum_totals_opponents == 0
		) {
			return (object)[
				'team_global_score' => null,
				'opponent_global_score' => null
			];
		}

		// home.global_score = home.sum_totals * away.total_goals_conceded / away.avg_sum_totals_opponents

		$team_global_score = ($team_global_elements->sum_totals * $opponent_global_elements->total_goals_conceded / $opponent_global_elements->avg_sum_totals_opponents);

		$opponent_global_score = ($opponent_global_elements->sum_totals * $team_global_elements->total_goals_conceded / $team_global_elements->avg_sum_totals_opponents);

		$team_global_score = round($team_global_score, $this->vars['HGS']->dec_place);
		$opponent_global_score = round($opponent_global_score, $this->vars['HGS']->dec_place);

		return (object)compact(
			'team_global_score',
			'opponent_global_score'
		);
	}

	// 17 - Logarithm (2 decimal places)
	public function logarithm($team, $matches)
	{
		// TEAM

		$team_nb_mp = $this->matches_played_count($team, $matches);

		if ($team_nb_mp < 6) {
			return null;
		}

		$team_tp = $this->total_points($team, $matches);
		$team_tg = $this->total_goals($team, $matches);

		if($team_tp === null || $team_tg === null) {
			return null;
		}

		// LAST OPPONENT
		
		$last_opp = $this->last_opponent($team, $matches);

		$last_opp_nb_mp = $this->matches_played_count($last_opp->team, $matches, $last_opp->match_index + 1);

		if ($last_opp_nb_mp < 6) {
			return null;
		}

		$last_opp_tp = $this->total_points($last_opp->team, $matches, $last_opp->match_index + 1);
		$last_opp_tg = $this->total_goals($last_opp->team, $matches, $last_opp->match_index + 1);

		if ($last_opp_tp === null || $last_opp_tg === null) {
			return null;
		}

		$log_team_tp = log($team_tp, $team_nb_mp);
		$log_team_tg = log($team_tg, $team_nb_mp);

		$log_last_opp_tp = log($last_opp_tp, $last_opp_nb_mp);
		$log_last_opp_tg = log($last_opp_tg, $last_opp_nb_mp);

		$result = $log_team_tp * 0.4 + $log_team_tg * 0.2 + $log_last_opp_tp * 0.3 + $log_last_opp_tg * 0.1;

		return round($result, $this->vars['HL']->dec_place);
	}

	// 18 - Last 6 results
	public function last_6_results($team, $matches)
	{
		$result = '';
		$nb = 0;

		$c = $matches->count();

		for ($i = 0; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team || $match->away == $team) {
				if ($match->winner === $team) {
					$result .= '3';
				} else if ($match->winner === null) {
					$result .= '2';
				} else {
					$result .= '1';
				}

				if (++$nb == 6) {
					break;
				}
			}
		}

		if ($nb < 6) {
			return null;
		}

		return intval($result);
	}

	// 19 - goal scored ratio (2 dec place)
	public function goal_scored_ratio($team, $matches)
	{
		$global_goals = 0;
		$team_goals = 0;

		$c = $matches->count();

		for ($i = 0; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team) {
				$team_goals += $match->home_goals;
			} else if ($match->away == $team) {
				$team_goals += $match->away_goals;
			}

			$global_goals += $match->home_goals + $match->away_goals;
		}

		if($global_goals == 0) {
			return null;
		}

		$result = $team_goals / $global_goals;

		return round($result, $this->vars['HGSR']->dec_place);
	}

	// 20 - Highest goal ratio (2 dec place)
	public function highest_goal_scored_ratio($team, $matches, $teams)
	{
		$teams_indexed = [];

		foreach ($teams as $t) {
			$teams_indexed[$t->name] = 0;
		}

		$c = $matches->count();

		for ($i = 0; $i < $c; $i++) {
			$match = $matches[$i];

			$teams_indexed[$match->home] += $match->home_goals;
			$teams_indexed[$match->away] += $match->away_goals;
		}

		$max = max($teams_indexed);

		if($max == 0) {
			return null;
		}

		$result = $teams_indexed[$team] / $max;

		return round($result, $this->vars['HHGSR']->dec_place);
	}

	//********************** Global Variables **********************

	// 1 - t_test_1 (2 dec place)
	public function t_test_1($team, $opponent, $matches, $teams)
	{
		$shadow_score = $this->shadow_score($team, $opponent, $matches);

		if ($shadow_score->team_shadow_score === null || $shadow_score->opponent_shadow_score === null) {
			return null;
		}

		$get_elements = function ($team, $shadow_score) use ($matches, $teams) {
			$mp = $this->matches_played($team, $matches, $teams);

			if ($mp === null) {
				return null;
			}

			$tp = $this->total_points($team, $matches);

			if ($tp === null) {
				return null;
			}

			$tg = $this->total_goals($team, $matches);

			if ($tg === null) {
				return null;
			}

			$td = $this->total_defense($team, $matches);

			if ($td === null) {
				return null;
			}

			$moving_avg = $this->goals_scored_moving_average($team, $matches);

			if ($moving_avg === null) {
				return null;
			}

			$moving_std_dev = $this->goals_scored_moving_std_dev($team, $matches);

			if ($moving_std_dev === null) {
				return null;
			}

			$last_op_tp = $this->last_opponent_total_points($team, $matches);

			if ($last_op_tp === null) {
				return null;
			}

			$last_op_tg = $this->last_opponent_total_goals($team, $matches);

			if ($last_op_tg === null) {
				return null;
			}

			$last_op_td = $this->last_opponent_total_defense($team, $matches);

			if ($last_op_td === null) {
				return null;
			}

			$last_op_mp = $this->last_opponent_matches_played($team, $matches, $teams);

			if ($last_op_mp === null) {
				return null;
			}

			return [
				$tp * $mp,
				$tg * $mp,
				$td * $mp,
				$shadow_score * ($moving_avg - $moving_std_dev),
				$last_op_tp * $last_op_mp,
				$last_op_tg * $last_op_mp,
				$last_op_td * $last_op_mp,
			];
		};

		$A = $get_elements($team, $shadow_score->team_shadow_score);

		if ($A === null) {
			return null;
		}

		$B = $get_elements($opponent, $shadow_score->opponent_shadow_score);

		if ($B === null) {
			return null;
		}

		$D = array_map(function ($a, $b) {
			return $a - $b;
		}, $A, $B);

		$sumD = array_reduce($D, function ($carry, $item) {
			return $carry + $item;
		});

		$Dsq = array_map(function ($d) {
			return $d * $d;
		}, $D);

		$sumDsq = array_reduce($Dsq, function ($carry, $item) {
			return $carry + $item;
		});

		$N = count($A) + count($B);

		if($N - 1 == 0 || $N * $sumDsq - $sumD * $sumD == 0) {
			return null;
		}

		$result = $sumD / sqrt(($N * $sumDsq - $sumD * $sumD) / ($N - 1));

		return round($result, $this->vars['TT1']->dec_place);
	}

	// 2 - t_test_2 (2 dec place)
	public function t_test_2($team, $opponent, $matches, $teams)
	{
		$get_elements = function ($team) use ($matches, $teams) {
			$last_op_tp = $this->last_opponent_total_points($team, $matches);

			if ($last_op_tp === null) {
				return null;
			}

			$last_op_tg = $this->last_opponent_total_goals($team, $matches);

			if ($last_op_tg === null) {
				return null;
			}

			$last_op_td = $this->last_opponent_total_defense($team, $matches);

			if ($last_op_td === null) {
				return null;
			}

			$last_op_mp = $this->last_opponent_matches_played($team, $matches, $teams);

			if ($last_op_mp === null) {
				return null;
			}

			return [
				$last_op_tp * $last_op_mp,
				$last_op_tg * $last_op_mp,
				$last_op_td * $last_op_mp,
			];
		};

		$A = $get_elements($team);

		if ($A === null) {
			return null;
		}

		$B = $get_elements($opponent);

		if ($B === null) {
			return null;
		}

		$D = array_map(function ($a, $b) {
			return $a - $b;
		}, $A, $B);

		$sumD = array_reduce($D, function ($carry, $item) {
			return $carry + $item;
		});

		$Dsq = array_map(function ($d) {
			return $d * $d;
		}, $D);

		$sumDsq = array_reduce($Dsq, function ($carry, $item) {
			return $carry + $item;
		});

		$N = count($A) + count($B);

		if($N - 1 == 0 || $N * $sumDsq - $sumD * $sumD == 0) {
			return null;
		}

		$result = $sumD / sqrt(($N * $sumDsq - $sumD * $sumD) / ($N - 1));

		return round($result, $this->vars['TT2']->dec_place);
	}

	// 3 - confidence (1 dec place)
	// For all t_test_1 variables:
	//   Xa = average for home
	//   Xb = average for away
	//   Sa = standard deviation for home
	//   Sb = standard deviation for away
	// confidence = (Xa - Xb) / square_root(square(Sa)/number_of_variables + square(Sb)/number_of_variables)

	public function confidence($team, $opponent, $matches, $teams)
	{
		$shadow_score = $this->shadow_score($team, $opponent, $matches);

		if ($shadow_score->team_shadow_score === null || $shadow_score->opponent_shadow_score === null) {
			return null;
		}

		$get_elements = function ($team, $shadow_score) use ($matches, $teams) {
			$mp = $this->matches_played($team, $matches, $teams);

			if ($mp === null) {
				return null;
			}

			$tp = $this->total_points($team, $matches);

			if ($tp === null) {
				return null;
			}

			$tg = $this->total_goals($team, $matches);

			if ($tg === null) {
				return null;
			}

			$td = $this->total_defense($team, $matches);

			if ($td === null) {
				return null;
			}

			$moving_avg = $this->goals_scored_moving_average($team, $matches);

			if ($moving_avg === null) {
				return null;
			}

			$moving_std_dev = $this->goals_scored_moving_std_dev($team, $matches);

			if ($moving_std_dev === null) {
				return null;
			}

			$last_op_tp = $this->last_opponent_total_points($team, $matches);

			if ($last_op_tp === null) {
				return null;
			}

			$last_op_tg = $this->last_opponent_total_goals($team, $matches);

			if ($last_op_tg === null) {
				return null;
			}

			$last_op_td = $this->last_opponent_total_defense($team, $matches);

			if ($last_op_td === null) {
				return null;
			}

			$last_op_mp = $this->last_opponent_matches_played($team, $matches, $teams);

			if ($last_op_mp === null) {
				return null;
			}

			return [
				$tp * $mp,
				$tg * $mp,
				$td * $mp,
				$shadow_score * ($moving_avg - $moving_std_dev),
				$last_op_tp * $last_op_mp,
				$last_op_tg * $last_op_mp,
				$last_op_td * $last_op_mp,
			];
		};

		$A = $get_elements($team, $shadow_score->team_shadow_score);

		if ($A === null) {
			return null;
		}

		$B = $get_elements($opponent, $shadow_score->opponent_shadow_score);

		if ($B === null) {
			return null;
		}

		$Xa = $this->average($A);
		$Xb = $this->average($B);
		$Sa = $this->standard_deviation($A);
		$Sb = $this->standard_deviation($B);

		if(($Sa * $Sa) / count($A) + ($Sb * $Sb) / count($B) == 0) {
			return null;
		}

		$result = ($Xa - $Xb) / sqrt(($Sa * $Sa) / count($A) + ($Sb * $Sb) / count($B));

		return round($result, $this->vars['C']->dec_place);
	}

	// 4 - Linear (1 dec place)

	// a = home match played
	// b = away match played
	// c = total_match_available (2 = 1 + 1)
	// d = home_total_goals
	// e = away_total_goals
	// f = total_goals_available (for max match, sum (5 * win_respective_points)) * 2

	// constraint 1: ax + by <= c
	// constraint 2: dx + ey <= f

	// Linear = home_total_points * x + away_total_points * y

	public function linear($team, $opponent, $matches, $teams)
	{
		$a = $this->matches_played($team, $matches, $teams);
		$b = $this->matches_played($opponent, $matches, $teams);
		$c = 2;
		$d = $this->total_goals($team, $matches);
		$e = $this->total_goals($opponent, $matches);

		$max_matches = ($teams->count() - 1) * 2;

		$f = 0;

		for ($n = 1; $n <= $max_matches; $n++) {
			$f += 5 * $this->get_points('win', $n);
		}

		$f *= 2;

		if ($a === null || $b === null || $d === null || $e === null || $a * $e - $d * $b == 0) {
			return null;
		}

		$y = ($a * $f - $d * $c) / ($a * $e - $d * $b);
		$x = ($c - $b * $y) / $a;

		$htp = $this->total_points($team, $matches);
		$atp = $this->total_points($opponent, $matches);

		if ($htp === null || $atp === null) {
			return null;
		}

		$result = ($x * $htp) + ($y * $atp);

		return round($result, $this->vars['L']->dec_place);
	}

	// 5 - Insta points (1 dec place)
	// x = home left points
	// y = away left points
	// result = Insta points = Math.sqrt(x*x + y*y)

	public function insta_points($team, $opponent, $matches, $teams)
	{
		$av = $this->available_points($teams);

		$x = $av - $this->total_points($team, $matches);
		$y = $av - $this->total_points($opponent, $matches);

		$result = sqrt($x * $x + $y * $y);

		return round($result, $this->vars['IP']->dec_place);
	}

	// 6 - Insta points rate (1 dec place)
	// x = home left points
	// y = away left points
	// s = Insta points = Math.sqrt(x*x + y*y)
	// x_s = average_of_home_last_3_matches_points
	// y_s = average_of_away_last_3_matches_points
	// result = (2*x*x_s + 2*y*y_s) / (2*s)

	public function insta_points_rate($team, $opponent, $matches, $teams)
	{
		$av = $this->available_points($teams);

		$x = $av - $this->total_points($team, $matches);
		$y = $av - $this->total_points($opponent, $matches);

		$x_s = $this->total_points($team, $matches, 0, 3);

		if ($x_s === null) {
			return null;
		}

		$x_s /= 3;
		
		$y_s = $this->total_points($opponent, $matches, 0, 3);
		
		if ($y_s === null) {
			return null;
		}

		$y_s /= 3;

		$s = sqrt($x * $x + $y * $y);

		if($s == 0) {
			return null;
		}

		$result = (2 * $x * $x_s + 2 * $y * $y_s) / (2 * $s);

		return round($result, $this->vars['IPR']->dec_place);
	}

	// 7 - Insta goals (1 dec place)
	// x = home left goals
	// y = away left goals
	// result = Insta goals = Math.sqrt(x*x + y*y)

	public function insta_goals($team, $opponent, $matches, $teams)
	{
		$av = $this->available_goals($teams);

		$x = $av - $this->total_goals($team, $matches);
		$y = $av - $this->total_goals($opponent, $matches);

		$result = sqrt($x * $x + $y * $y);

		return round($result, $this->vars['IG']->dec_place);
	}

	// 8 - Insta goals rate (1 dec place)
	// x = home left goals
	// y = away left goals
	// s = Insta goals = Math.sqrt(x*x + y*y)
	// x_s = average_of_home_last_3_matches_goals
	// y_s = average_of_away_last_3_matches_goals
	// result = (2*x*x_s + 2*y*y_s) / (2*s)

	public function insta_goals_rate($team, $opponent, $matches, $teams)
	{
		$av = $this->available_goals($teams);

		$x = $av - $this->total_goals($team, $matches);
		$y = $av - $this->total_goals($opponent, $matches);

		$x_s = $this->total_goals($team, $matches, 0, 3);

		if ($x_s === null) {
			return null;
		}

		$x_s /= 3;

		$y_s = $this->total_goals($opponent, $matches, 0, 3);

		if ($y_s === null) {
			return null;
		}

		$y_s /= 3;

		$s = sqrt($x * $x + $y * $y);

		if($s == 0) {
			return null;
		}

		$result = (2 * $x * $x_s + 2 * $y * $y_s) / (2 * $s);

		return round($result, $this->vars['IGR']->dec_place);
	}

	// 9 - Insta defense (1 dec place)
	// x = home left defense
	// y = away left defense
	// result = Insta defense = Math.sqrt(x*x + y*y)

	public function insta_defense($team, $opponent, $matches, $teams)
	{
		$av = $this->available_defense($teams);

		$x = $av - $this->total_defense($team, $matches);
		$y = $av - $this->total_defense($opponent, $matches);

		$result = sqrt($x * $x + $y * $y);

		return round($result, $this->vars['ID']->dec_place);
	}

	// 10 - Insta defense rate (1 dec place)
	// x = home_left_defense
	// y = away_left_defense
	// s = Insta defense = Math.sqrt(x*x + y*y)
	// x_s = average_of_home_last_3_matches_defense
	// y_s = average_of_away_last_3_matches_defense
	// result = (2*x*x_s + 2*y*y_s) / (2*s)

	public function insta_defense_rate($team, $opponent, $matches, $teams)
	{
		$av = $this->available_defense($teams);

		$x = $av - $this->total_defense($team, $matches);
		$y = $av - $this->total_defense($opponent, $matches);

		$x_s = $this->total_defense($team, $matches, 0, 3);

		if ($x_s === null) {
			return null;
		}

		$x_s /= 3;

		$y_s = $this->total_defense($opponent, $matches, 0, 3);

		if ($y_s === null) {
			return null;
		}

		$y_s /= 3;

		$s = sqrt($x * $x + $y * $y);

		if($s == 0) {
			return null;
		}

		$result = (2 * $x * $x_s + 2 * $y * $y_s) / (2 * $s);

		return round($result, $this->vars['IDR']->dec_place);
	}

	//********************** Other Variables **********************

	// The maximum point a team can get
	private function available_points($teams)
	{
		$nb_matches = ($teams->count() - 1) * 2;
		$total = 0;

		for ($n = 1; $n <= $nb_matches; $n++) {
			$total += $this->get_points('win', $n);
		}

		return $total;
	}

	// The maximum goals a team can get
	private function available_goals($teams)
	{
		$nb_matches = ($teams->count() - 1) * 2;
		$total = 0;

		for ($n = 1; $n <= $nb_matches; $n++) {
			$total += 5 * $this->get_points('win', $n);
		}

		return $total;
	}

	// The maximum defenses a team can get
	private function available_defense($teams)
	{
		$nb_matches = ($teams->count() - 1) * 2;
		$total = 0;

		for ($n = 1; $n <= $nb_matches; $n++) {
			$total += 1 * $this->get_points('defense', $n);
		}

		return $total;
	}

	// The average of an array of numbers
	private function average($numbers)
	{
		$c = count($numbers);
		$sum = 0;

		for ($i = 0; $i < $c; $i++) {
			$sum += $numbers[$i];
		}

		return $sum / $c;
	}

	// The standard deviation of an array of numbers
	private function standard_deviation($numbers)
	{
		$mean = $this->average($numbers);

		$mapped = array_map(function ($x) use ($mean) {
			return pow($x - $mean, 2);
		}, $numbers);

		return sqrt($this->average($mapped));
	}

	private function goal_moving_means($team, $matches, $type)
	{
		$nb_last_matches = 6;

		$last_matches = $matches->filter(function ($match, $key) use ($team) {
			return $match->home == $team || $match->away == $team;
		})->slice(0, $nb_last_matches)->values();

		if ($last_matches->count() < $nb_last_matches) {
			return null;
		}

		$last_goals_scored = $last_matches->map(function ($match, $key) use ($team, $type) {
			if ($match->home == $team) {
				return $type === 'scored' ? $match->home_goals : -$match->away_goals;
			} else if ($match->away == $team) {
				return $type === 'scored' ? $match->away_goals : -$match->home_goals;
			}
		});

		$means = [];

		for ($i = 0; $i < $nb_last_matches - 1; $i++) {
			$means[] = ($last_goals_scored[$i] + $last_goals_scored[$i + 1]) / 2;
		}

		return $means;
	}

	private function goal_scored_moving_means($team, $matches)
	{
		return $this->goal_moving_means($team, $matches, 'scored');
	}

	private function goal_conceded_moving_means($team, $matches)
	{
		return $this->goal_moving_means($team, $matches, 'conceded');
	}

	private function goals_moving_func($team, $matches, $type, $func)
	{
		$means = $this->{"goal_{$type}_moving_means"}($team, $matches);

		if ($means === null) {
			return null;
		}

		$result = $this->$func($means);

		return $result;
	}

	// goals scored moving standard deviation = last 6 matches moving standard deviation (2 dec place)
	private function goals_scored_moving_std_dev($team, $matches)
	{
		return $this->goals_moving_func($team, $matches, 'scored', 'standard_deviation');
	}

	// goal conceded moving standard deviation = same like... (check the stand dev formula)
	private function goal_conceded_moving_std_dev($team, $matches)
	{
		return $this->goals_moving_func($team, $matches, 'conceded', 'standard_deviation');
	}

	private function last_opponent($team, $matches)
	{
		$c = $matches->count();

		for ($i = 0; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team || $match->away == $team) {
				return (object)[
					'team' => $match->home == $team ? $match->away : $match->home,
					'match_index' => $i,
				];
			}
		}

		return null;
	}

	// last opponent's "match played" (except that last match) 2 dec place
	private function last_opponent_matches_played($team, $matches, $teams)
	{
		$opp = $this->last_opponent($team, $matches);

		if($opp === null) {
			return null;
		}

		return $this->matches_played($opp->team, $matches, $teams, $opp->match_index + 1);
	}

	private function matches_played_count($team, $matches, $from = 0)
	{
		$nb = 0;

		$c = $matches->count();

		for ($i = $from; $i < $c; $i++) {
			$match = $matches[$i];

			if ($match->home == $team || $match->away == $team) {
				$nb++;
			}
		}

		return $nb;
	}
}
