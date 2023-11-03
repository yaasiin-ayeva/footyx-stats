<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class TestController extends BaseController
{
	public function info(Request $request)
	{
		echo phpinfo();
	}

	public function bcrypt(Request $request, $text)
	{
		echo bcrypt($text);
	}

	public function format()
	{
		// Generate paths

		$srcPath = storage_path('app/tmp/src.xlsx');
		$destPath = storage_path('app/tmp/dest.xlsx');

		// Open source file

		if (!($srcFile = SimpleXLSX::parse($srcPath))) {
			return 'Unable to parse the source file: ' . SimpleXLSX::parseError();
		}

		// Function that generates payment date

		function payment_date($i, $particular)
		{
			if ($i == 0) {
				return 'Payment date';
			} else {
				$matches = [];

				$formats = [
					// "/(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}/",
					// "/(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2])\.\d{4}/",
					// "/([1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2])\.\d{2}/",
					// "/([1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2]|d{3})\.\d{2}/",
					"/(\d{3}|\d{2}|\d) ?(\/|\.)(\d{3}|\d{2}|\d)(\/|\.)(\d{4}|\d{2})/",
					"/(\d{3}|\d{2}|\d)(\/|\.)(\d{6})/",
					"/\d{6}/",
				];

				foreach ($formats as $format) {
					if (preg_match($format, $particular, $matches)) {
						if (strlen($matches[0]) == 6) {
							$a = [
								$matches[0][0] . '' . $matches[0][1],
								$matches[0][2] . '' . $matches[0][3],
								$matches[0][4] . '' . $matches[0][5],
							];
						} else {
							$date = str_replace(['.', ' '], ['/', ''], $matches[0]);
							$a = explode('/', $date);
						}

						// Cases are ordered by priority

						// case 12/122015

						if (count($a) == 2) {
							$v = $a[1];

							$a[1] = substr($v, 0, 2);
							$a[2] = substr($v, 2, 4);
						}

						// case 083/06/15

						if (strlen($a[0]) == 3) {
							$a[0] = $a[0][2];
						}

						// case 6/27/2016

						if (intval($a[1]) > 12) {
							$temp = $a[1];
							$a[1] = $a[0];
							$a[0] = $temp;
						}

						// case 2 digits year

						if (strlen($a[2]) == 2) {
							$a[2] = 2000 + intval($a[2]);
						}

						$date = intval($a[2]) . '-' . intval($a[1]) . '-' . intval($a[0]);

						return Carbon::parse($date)->format('Y-m-d');
					}
				}

				return '';
			}
		}

		// Generate new file

		$i = 0;

		$new_data = [];
		$payment_dates = [];

		foreach ($srcFile->readRows() as $data) {
			$data[0] = trim($data[0]);

			$p_date = payment_date($i, $data[2]);

			$new_data[] = [
				$data[0],
				$data[1],
				$data[2],
				$p_date,
				$data[3],
				$data[4],
				$data[5],
				$data[6],
			];

			if ($p_date) {
				$payment_dates[$data[0]] = $p_date;
			}

			$i++;
		}

		// $bug = [];

		foreach ($new_data as $key => &$row) {
			if ($key == 0) {
				$row[] = 'Principal interest';
				$row[] = 'Present value';
			} else {
				if ($row[3] == '') {
					$row[3] = $payment_dates[$row[0]] ?? '';
				}

				$credit_amount = floatval($row[5]);

				$payment_date = Carbon::parse($row[3]);
				$r_post_date = Carbon::parse($row[7]);

				// if(Carbon::parse($row[0]) <= $payment_date) {
				// Log::debug([
				//     'position' => $key + 1,
				//     'post_date' => $row[0],
				//     'payment_date' => $row[3]
				// ]);
				// $bug[] = $key + 1;
				// }

				$int_comp_date = Carbon::now();

				$delay_days = $payment_date->diffInDays($r_post_date) - 2;

				if ($delay_days <= 0) {
					continue;
				}

				$interest_rate = 0.00045;
				$interest_amount = round($credit_amount * $interest_rate * $delay_days, 2);

				$interest_days = $r_post_date->diffInDays($int_comp_date);

				$present_value = round($interest_amount * pow(1 + $interest_rate, $interest_days), 2);

				$row[] = $interest_amount;
				$row[] = $present_value;
			}
		}

		// Log::debug(implode(', ', $bug));
		// Log::debug(count($bug));

		SimpleXLSXGen::fromArray($new_data)->downloadAs($destPath);
	}

	public function load_csv_data($filepath, $table, $cols = [])
    {
        return DB::connection()->getPdo()->exec("
            LOAD DATA LOCAL INFILE '".str_replace('\\', '\\\\', $filepath)."'
            INTO TABLE $table
            FIELDS TERMINATED BY ','
            ENCLOSED BY '\"'
            LINES TERMINATED BY '\n'
            ". ($cols ? '(' . implode(',', $cols) . ')' : '')
        );
    }

	public function zeepay_old()
	{
		// Disable time limit

		set_time_limit(0);

		// Get begin time

		$begin_time = Carbon::now();

		// Generate paths

		$sumPath = storage_path('app/tmp/zeepay-windows-summary.xlsx');

		// Get the transactions

		$transactions = DB::table('zp_transactions')
		->selectRaw('
			tr_timestamp,
			tr_date,
			debit,
			credit
		')
		->orderBy('id')
		->lazy(30000);

		$summary = [];

		// Create the 2 time sections

		$section1 = '00:00:00 - 12:00:00';
		$section2 = '12:00:01 - 23:59:59';

		// For each path

		foreach($srcPaths as $srcPath) {
			// Open the file

			if (!($srcFile = SimpleXLSX::parse($srcPath))) {
				return 'Unable to parse the source file: ' . SimpleXLSX::parseError();
			}

			Log::debug('File opened: '. $srcPath);
			
			// Read all rows
	
			$i = 0;
	
			foreach ($srcFile->readRows() as $data) {
				// Sanitize data

				$data = [
					trim($data[0]),
					trim($data[1]),
					trim($data[2]),
					trim($data[3]),
					trim($data[4]),
					trim($data[5]),
					trim($data[6]),
					trim($data[7]),
					trim($data[8]),
					trim($data[9]),
				];

				// Skip the first row
	
				if($i++ == 0) {
					continue;
				}
	
				// Get reference
	
				$reference = $data[0];

				// Check if the line is duplicated

				// Use the reference to make the index shorter

				if($reference) {
					// Look for the same data

					$found = false;

					foreach ($duplicates[$reference] ?? [] as &$arr) {
						// If the two arrays are exactly the same
						
						if(!array_diff($data, $arr[0])) {
							$arr[1]++;
							$found = true;
							break;
						}
					}

					// If the line has not been found, create a new

					if(!$found) {
						$duplicates[$reference][] = [$data, 0];
					}

					// If the line is a duplicate, go to the next line

					else {
						continue;
					}
				}

				// Get debit, credit and try to get the date

				$debit = floatval($data[8]);
				$credit = floatval($data[9]);
	
				try {
					$tr_date = Carbon::createFromFormat('d/m/Y H:i:s', $data[7]);
				}
				catch(Exception $e) {
					// If the value is not a date, handle it as an exception
	
					$value = $data[7];
	
					// Check that the value is already existing or create it
					
					if(!array_key_exists($value, $exceptions)) {
						$exceptions[$value] = [
							'date' => $value,
							'section' => '',
							'nb_debit' => 0,
							'total_debit' => 0.00,
							'nb_credit' => 0,
							'total_credit' => 0.00,
							'net' => ''
						];
					}
	
					// If the transaction is debit...
	
					if($debit > 0) {
						$exceptions[$value]['nb_debit']++;
						$exceptions[$value]['total_debit'] += $debit;
					}
	
					// If the transaction is credit...
	
					else if($credit > 0) {
						$exceptions[$value]['nb_credit']++;
						$exceptions[$value]['total_credit'] += $credit;
					}
	
					// Go to the next row
	
					continue;
				}
	
				// Format the date
	
				$date = $tr_date->format('Y-m-d');
	
				// Check that the date is already existing or create it
	
				if(!array_key_exists($date, $summary)) {
					// Add section 1
	
					$summary[$date][$section1] = [
						'date' => $tr_date->format('d/m/Y'),
						'section' => $section1,
						'nb_debit' => 0,
						'total_debit' => 0.00,
						'nb_credit' => 0,
						'total_credit' => 0.00,
						'net' => ''
					];
	
					// Add section 2
	
					$summary[$date][$section2] = [
						'date' => $tr_date->format('d/m/Y'),
						'section' => $section2,
						'nb_debit' => 0,
						'total_debit' => 0.00,
						'nb_credit' => 0,
						'total_credit' => 0.00,
						'net' => ''
					];
				}
	
				// Get the first section time interval for the date
	
				$section_1_begin = Carbon::parse($date . ' 00:00:00');
				$section_1_end = Carbon::parse($date . ' 12:00:00');
	
				// Get the correct time section
	
				$section = $tr_date->between($section_1_begin, $section_1_end) ? $section1 : $section2;
	
				// If the transaction is debit...
	
				if($debit > 0) {
					$summary[$date][$section]['nb_debit']++;
					$summary[$date][$section]['total_debit'] += $debit;
				}
	
				// If the transaction is credit...
	
				else if($credit > 0) {
					$summary[$date][$section]['nb_credit']++;
					$summary[$date][$section]['total_credit'] += $credit;
				}
			}
		}

		// Create an output array with the title row

		$output = [
			[
				'Date',
				'Time',
				'Nb Debit',
				'Total Debit',
				'Nb Credit',
				'Total Credit',
				'Debit',
				'Credit',
			]
		];

		// Add the summary to the output

		foreach ($summary as $row) {
			foreach ($row as $section => $value) {
				// Compute the net

				$net = number_format(abs($value['total_debit'] - $value['total_credit']), 2);

				// Add the line to the output array

				$output[] = [
					$value['date'],
					$value['section'],
					$value['nb_debit'],
					number_format($value['total_debit'], 2),
					$value['nb_credit'],
					number_format($value['total_credit'], 2),
					$value['total_debit'] >= $value['total_credit'] ? $net : '',
					$value['total_credit'] > $value['total_debit'] ? $net : '',
				];
			}
		}

		// Save the output file

		SimpleXLSXGen::fromArray($output)->saveAs($sumPath);

		// Create an output array with the title row

		$output = [
			[
				'Date',
				'Time',
				'Nb Debit',
				'Total Debit',
				'Nb Credit',
				'Total Credit',
				'Debit',
				'Credit',
			]
		];

		// Add the exceptions to the output

		foreach ($exceptions as $value) {
			// Compute the net

			$net = number_format(abs($value['total_debit'] - $value['total_credit']), 2);

			// Add the line to the output array

			$output[] = [
				$value['date'],
				$value['section'],
				$value['nb_debit'],
				number_format($value['total_debit'], 2),
				$value['nb_credit'],
				number_format($value['total_credit'], 2),
				$value['total_debit'] >= $value['total_credit'] ? $net : '',
				$value['total_credit'] > $value['total_debit'] ? $net : '',
			];
		}

		// Save the output file

		SimpleXLSXGen::fromArray($output)->saveAs($exPath);

		// Save the duplicates

		$output = [
			[
				'Reference',
				'ISS',
				'Card Number',
				'ACQ',
				'Merchant Description',
				'Terminal ID',
				'Type',
				'Tr Date',
				'Debit',
				'Crédit',
				'Duplicates'
			]
		];

		foreach($duplicates as $reference => &$v1) {
			foreach($v1 as &$arr) {
				if($arr[1] == 0) {
					continue;
				}

				$output[] = [
					$arr[0][0],
					$arr[0][1],
					$arr[0][2],
					$arr[0][3],
					$arr[0][4],
					$arr[0][5],
					$arr[0][6],
					$arr[0][7],
					$arr[0][8],
					$arr[0][9],
					$arr[1]
				];
			}
		}

		SimpleXLSXGen::fromArray($output)->saveAs($dupPath);

		// Show the time spent

		dump($begin_time->diffInSeconds() . ' seconds');
	}

	public function zeepay_1()
	{
		// Disable time limit

		set_time_limit(0);

		// Generate paths

		$sumPath = storage_path('app/tmp/zeepay-windows-summary.xlsx');

		// Get the transactions

		$transactions = DB::table('zp_transactions')
		->selectRaw('
			tr_timestamp,
			tr_date,
			debit,
			credit
		')
		->orderBy('tr_timestamp')
		->lazy(30000);

		$summary = [];

		// Create the 2 time sections

		$section1 = '00:00:00 - 12:00:00';
		$section2 = '12:00:01 - 23:59:59';

		// For each transaction

		$i = 0;

		foreach ($transactions as $tr) {
			// Check that the date is already existing or create it
	
			if(!array_key_exists($tr->tr_date, $summary)) {
				// Add section 1

				$summary[$tr->tr_date][$section1] = [
					'date' => $tr->tr_date,
					'section' => $section1,
					'nb_debit' => 0,
					'total_debit' => 0.00,
					'nb_credit' => 0,
					'total_credit' => 0.00,
					'net' => 0.00
				];

				// Add section 2

				$summary[$tr->tr_date][$section2] = [
					'date' => $tr->tr_date,
					'section' => $section2,
					'nb_debit' => 0,
					'total_debit' => 0.00,
					'nb_credit' => 0,
					'total_credit' => 0.00,
					'net' => 0.00
				];
			}

			// Get the first section time interval for the date

			$section_1_begin = Carbon::parse($tr->tr_date . ' 00:00:00');
			$section_1_end = Carbon::parse($tr->tr_date . ' 12:00:00');

			// Get the correct time section

			$section = Carbon::parse($tr->tr_date)->between($section_1_begin, $section_1_end) ? $section1 : $section2;

			// If the transaction is debit...

			if($tr->debit > 0) {
				$summary[$tr->tr_date][$section]['nb_debit']++;
				$summary[$tr->tr_date][$section]['total_debit'] += $tr->debit;
			}

			// If the transaction is credit...

			else if($tr->credit > 0) {
				$summary[$tr->tr_date][$section]['nb_credit']++;
				$summary[$tr->tr_date][$section]['total_credit'] += $tr->credit;
			}

			if($i++ % 100000 == 0) {
				Log::debug($i);
			}
		}

		// Create an output array with the title row

		$output = [
			[
				'Date',
				'Time',
				'Nb Debit',
				'Total Debit',
				'Nb Credit',
				'Total Credit',
				'Debit',
				'Credit',
			]
		];

		// Add the summary to the output

		foreach ($summary as $row) {
			foreach ($row as $section => $value) {
				// Compute the net

				$net = abs($value['total_debit'] - $value['total_credit']);

				// Add the line to the output array

				$output[] = [
					$value['date'],
					$value['section'],
					$value['nb_debit'],
					$value['total_debit'],
					$value['nb_credit'],
					$value['total_credit'],
					$value['total_debit'] >= $value['total_credit'] ? $net : '',
					$value['total_credit'] > $value['total_debit'] ? $net : '',
				];
			}
		}

		// Save the output file

		SimpleXLSXGen::fromArray($output)->saveAs($sumPath);
	}

	// ZEEPAY

	const REFERENCE_POS = 0;
    const ISS_POS = 1;
    const CARD_NUMBER_POS = 2;
    const ACQ_POS = 3;
    const MERCHANT_DESC_POS = 4;
    const TERMINAL_ID_POS = 5;
    const TYPE_POS = 6;
    const TR_DATE_POS = 7;
    const DEBIT_POS = 8;
    const CREDIT_POS = 9;

	private function validate_tr_data($data)
    {
        $errors = [];

        // 'reference' => 'bail|required|max:31',

        if($data[static::REFERENCE_POS] === '') {
            $errors[] = 'Reference required';
        }

        if(strlen($data[static::REFERENCE_POS]) > 31) {
            $errors[] = 'Reference can not be more than 31 characters';
        }

        // 'iss' => 'bail|required|max:31',

        if($data[static::ISS_POS] === '') {
            $errors[] = 'ISS required';
        }

        if(strlen($data[static::ISS_POS]) > 31) {
            $errors[] = 'ISS can not be more than 31 characters';
        }

        // 'card_number' => 'bail|required|max:31',

        if($data[static::CARD_NUMBER_POS] === '') {
            $errors[] = 'Card number required';
        }

        if(strlen($data[static::CARD_NUMBER_POS]) > 31) {
            $errors[] = 'Card number can not be more than 31 characters';
        }

		// 'acq' => 'bail|required|max:31',

        if($data[static::ACQ_POS] === '') {
            $errors[] = 'ACQ required';
        }

        if(strlen($data[static::ACQ_POS]) > 31) {
            $errors[] = 'ACQ can not be more than 31 characters';
        }

		// 'merchant_desc' => 'bail|required|max:31',

        if($data[static::MERCHANT_DESC_POS] === '') {
            $errors[] = 'Merchant description required';
        }

        if(strlen($data[static::MERCHANT_DESC_POS]) > 31) {
            $errors[] = 'Merchant description can not be more than 31 characters';
        }

		// 'terminal_id' => 'bail|required|max:31',

        if($data[static::TERMINAL_ID_POS] === '') {
            $errors[] = 'Terminal id required';
        }

        if(strlen($data[static::TERMINAL_ID_POS]) > 31) {
            $errors[] = 'Terminal id can not be more than 31 characters';
        }

		// 'type' => 'bail|required|max:31',

        if($data[static::TYPE_POS] === '') {
            $errors[] = 'Type required';
        }

        if(strlen($data[static::TYPE_POS]) > 31) {
            $errors[] = 'Type can not be more than 31 characters';
        }

        // 'tr_date' => 'bail|required|date_format:d/m/Y H:i:s',

        if($data[static::TR_DATE_POS] === '') {
            $errors[] = 'Transaction date required';
        }

        try {
            Carbon::createFromFormat('d/m/Y H:i:s', $data[static::TR_DATE_POS]);
        } catch (\Exception $e) {
            $errors[] = 'Invalid format for transaction date';
        }

		// 'debit' => 'bail|required|numeric',

        if($data[static::DEBIT_POS] === '') {
            $errors[] = 'Debit required';
        }

        if(is_numeric($data[static::DEBIT_POS]) === false) {
            $errors[] = 'Debit must be a number';
        }

		// 'credit' => 'bail|required|numeric',

        if($data[static::CREDIT_POS] === '') {
            $errors[] = 'Credit required';
        }

        if(is_numeric($data[static::CREDIT_POS]) === false) {
            $errors[] = 'Credit must be a number';
        }

        return $errors;
    }

	// Load zeepay files

	public function load_zeepay_files()
	{
		// Disable time limit

		set_time_limit(0);

		// Get begin time

		$begin_time = Carbon::now();

		// Open destination file for transactions

        $trPath = storage_path('app/tmp/'.uniqid(rand(), true).'.csv');

        $trFile = fopen($trPath, 'w');

        if($trFile === false) {
            return $this->error('Unable to open the destination CSV File');
        }

		// Open destination file for unreferenced

        $unrefPath = storage_path('app/tmp/'.uniqid(rand(), true).'.csv');

        $unrefFile = fopen($unrefPath, 'w');

        if($unrefFile === false) {
            return $this->error('Unable to open the destination CSV File');
        }

		// Generate source files paths

		$srcPaths = [];

		for($i = 1; $i <= 3; $i++) {
			$srcPaths[] = storage_path("app/tmp/GHIPSS COMPENSATION/GHIPSS-Book$i.xlsx");
		}

		// For each path

		foreach($srcPaths as $srcPath) {
			// Open the file

			if (!($srcFile = SimpleXLSX::parse($srcPath))) {
				return 'Unable to parse the source file: ' . SimpleXLSX::parseError();
			}

			Log::debug('File opened: '. $srcPath);
			
			// Read all rows
	
			$i = 0;
	
			foreach ($srcFile->readRows() as $data) {
				// Skip the first row
	
				if($i++ == 0) {
					continue;
				}

				// Sanitize data

				$data = [
					trim($data[static::REFERENCE_POS]  ?? ''),
					trim($data[static::ISS_POS] ?? ''),
					trim($data[static::CARD_NUMBER_POS] ?? ''),
					trim($data[static::ACQ_POS] ?? ''),
					trim($data[static::MERCHANT_DESC_POS] ?? ''),
					trim($data[static::TERMINAL_ID_POS] ?? ''),
					trim($data[static::TYPE_POS] ?? ''),
					trim($data[static::TR_DATE_POS] ?? ''),
					trim($data[static::DEBIT_POS] ?? ''),
					trim($data[static::CREDIT_POS] ?? ''),
				];

				// Get the reference

				$reference = $data[self::REFERENCE_POS];

				// If the reference is "Reference", go to the next line

				if($reference == 'Reference') {
					continue;
				}

				// If the reference does not exist, consider the transaction as unreferenced

				if(!$reference) {
					fputcsv($unrefFile, [
                        $reference,
                        $data[self::ISS_POS],
                        $data[self::CARD_NUMBER_POS],
                        $data[self::ACQ_POS],
                        $data[self::MERCHANT_DESC_POS],
                        $data[self::TERMINAL_ID_POS],
                        $data[self::TYPE_POS],
                        $data[self::TR_DATE_POS],
                        floatval($data[self::DEBIT_POS]),
                        floatval($data[self::CREDIT_POS]),
                    ]);

					// Go to the next transaction

					continue;
				}

				// Validate data

				if ($errors = $this->validate_tr_data($data)) {
					$message = "File $srcPath <br>";
					$message .= count($errors) . ' errror(s) detected at line ' . ($i + 1) . '<br>';
					$message .= implode('<br>', $errors);
	
					return $message;
				}

				// At this step, the line is a valid transaction
	
				// Get the date
	
				$date = Carbon::createFromFormat('d/m/Y H:i:s', $data[self::TR_DATE_POS]);

				// Add it to the destination file

				fputcsv($trFile, [
					$reference,
					$data[self::ISS_POS],
					$data[self::CARD_NUMBER_POS],
					$data[self::ACQ_POS],
					$data[self::MERCHANT_DESC_POS],
					$data[self::TERMINAL_ID_POS],
					$data[self::TYPE_POS],
					$date->format('Y-m-d H:i:s'),
					$date->format('Y-m-d'),
					floatval($data[self::DEBIT_POS]),
					floatval($data[self::CREDIT_POS]),
				]);
			}
		}

		// Close the files

		fclose($trFile);
		fclose($unrefFile);

		// Load the transaction file into the database

		$this->load_csv_data($trPath, 'zp_transactions', [
			'reference', 'iss', 'card_number', 'acq', 'merchant_desc', 'terminal_id', 'type', 'tr_timestamp', 'tr_date', 'debit', 'credit'
		]);

		// Load the unref file into the database

		$this->load_csv_data($unrefPath, 'zp_unref', [
			'reference', 'iss', 'card_number', 'acq', 'merchant_desc', 'terminal_id', 'type', 'tr_date', 'debit', 'credit'
		]);
 
		// Delete temp files

		unlink($trPath);
		unlink($unrefPath);

		// Output
 
		echo 'Files loaded successfully';

		dump($begin_time->diffInSeconds() . ' seconds');
	}

	// Get bank summary

	public function get_bank_summary()
	{
		$bank_summary = [];

		// Open bank statement file

		if (!($srcFile = SimpleXLSX::parse(storage_path("/app/tmp/Bank Files/1103002.xlsx")))) {
			return 'Unable to parse the source file: ' . SimpleXLSX::parseError();
		}

		// For all transactions

		$i = 0;
	
		foreach ($srcFile->readRows() as $data) {
			// Skip the first row

			if($i++ == 0) {
				continue;
			}

			// Sanitize data

			$date = Carbon::parse($data[0])->format('Y-m-d');
			$debit = floatval($data[4]);

			// Create the key if it does not exist

			if(!array_key_exists($date, $bank_summary)) {
				$bank_summary[$date] = (object)[
					'nb_bank_debit' => 0,
					'total_bank_debit' => 0,
					'nb_cashbook_credit' => 0,
					'total_cashbook_credit' => 0,
				];
			}

			// If it is a debit transaction

			if($debit) {
				$bank_summary[$date]->nb_bank_debit++;
				$bank_summary[$date]->total_bank_debit += $debit;
			}
		}

		// Open cashbook file

		if (!($srcFile = SimpleXLSX::parse(storage_path("/app/tmp/Bank Files/CB-ABSA Settlement Account _1103002.xlsx")))) {
			return 'Unable to parse the source file: ' . SimpleXLSX::parseError();
		}

		// For all transactions

		$i = 0;
	
		foreach ($srcFile->readRows() as $data) {
			// Skip the first row

			if($i++ == 0) {
				continue;
			}

			// Sanitize data

			$date = Carbon::parse($data[0])->format('Y-m-d');
			$credit = floatval($data[5]);
			
			// Create the key if it does not exist
			
			if(!array_key_exists($date, $bank_summary)) {
				$bank_summary[$date] = (object)[
					'nb_bank_debit' => 0,
					'total_bank_debit' => 0,
					'nb_cashbook_credit' => 0,
					'total_cashbook_credit' => 0,
				];
			}
			
			// If it is a credit transaction
			
			if($credit) {
				$bank_summary[$date]->nb_cashbook_credit++;
				$bank_summary[$date]->total_cashbook_credit += $credit;
			}
		}

		return $bank_summary;
	}

	// Compute the summary

	public function compute_summary()
	{
		// Disable time limit

		set_time_limit(0);

		// Hollydays

		$hollydays = [
			'2022-01-03' => 1,
			'2022-01-07' => 1,
			'2022-03-07' => 1,
			'2022-04-15' => 1,
			'2022-04-18' => 1,
			'2022-05-01' => 1,
			'2022-05-03' => 1,
			'2022-07-11' => 1,
			'2022-08-04' => 1,
			'2022-09-21' => 1,
			'2022-12-02' => 1,
			'2022-12-26' => 1,
			'2022-12-27' => 1
		];

		// Get all dates available

		$dates = DB::table('zp_transactions')
		->selectRaw('
			MIN(tr_date) AS min_date,
			MAX(tr_date) AS max_date
		')
		->first();

		$due_dates = [];
		$summary = [];

		$min = Carbon::parse($dates->min_date);
		$max = Carbon::parse($dates->max_date);

		for(; $min <= $max; $min->addDay()) {
			// Get the due date

			$due_date = clone($min);

			$date = $due_date->format('Y-m-d');

			// If the date is valid (not hollyday and not weekend)

			if(!array_key_exists($date, $hollydays) && !$due_date->isWeekend()) {
				// The first window can be treated the same day

				$due_dates[$date]['WIN1'] = $date;

				// Register the due date if not exist

				if(!isset($summary[$date])) {
					$summary[$date] = (object)[
						'nb_win' => 0,
						'total_debit' => 0,
						'total_credit' => 0
					];
				}

				// Add the window

				$summary[$date]->nb_win++;
			}

			// Find the next due date

			do {
				$due_date->addDay();
				$due_date_format = $due_date->format('Y-m-d');
			} while(array_key_exists($due_date_format, $hollydays) || $due_date->isWeekend());

			// Register the due date if not exist

			if(!isset($summary[$due_date_format])) {
				$summary[$due_date_format] = (object)[
					'nb_win' => 0,
					'total_debit' => 0,
					'total_credit' => 0
				];
			}

			// If the first window did not have a due date, add it a due date

			if(!isset($due_dates[$date]['WIN1'])) {
				$due_dates[$date]['WIN1'] = $due_date_format;
				
				// Add the window

				$summary[$due_date_format]->nb_win++;
			}

			// Add the due date found for the second window

			$due_dates[$date]['WIN2'] = $due_date_format;

			// Add the window

			$summary[$due_date_format]->nb_win++;
		}

		// Get the transactions

		$transactions = DB::table('zp_transactions')
		->selectRaw('
			tr_timestamp,
			tr_date,
			debit,
			credit
		')
		->orderBy('id')
		->lazy(30000);

		// For each transaction

		$i = 0;

		foreach ($transactions as $tr) {
			// Find the timestamp

			$tr_timestamp = Carbon::parse($tr->tr_timestamp);

			// Get the window the transaction date belongs to
	
			$win1_begin = Carbon::parse($tr->tr_date . ' 00:00:00');
			$win1_end = Carbon::parse($tr->tr_date . ' 12:00:00');

			$win = $tr_timestamp->between($win1_begin, $win1_end) ? 'WIN1' : 'WIN2';

			// Get the due date based on the date and the window

			$due_date = $due_dates[$tr->tr_date][$win];

			// Add total debit and total credit to the due date

			$summary[$due_date]->total_debit += $tr->debit;
			$summary[$due_date]->total_credit += $tr->credit;

			if($i++ % 100000 == 0) {
				Log::debug($i);
			}
		}

		$bank_summary = $this->get_bank_summary();

		// Save the summary

		$output = [
			[
				'Due date',
				'Number of windows',
				'Total debit',
				'Total credit',
				'Debit',
				'Credit',
				'Number of bank debit',
				'Total bank debit',
				'Number of cashbook credit',
				'Total cashbook credit',
			]
		];

		$last_due_date = null;

		foreach($summary as $due_date => $v) {
			$nb_bank_debit = 0;
			$total_bank_debit = 0;
			$nb_cashbook_credit = 0;
			$total_cashbook_credit = 0;

			// If there was a last due date

			if($last_due_date !== null) {
				$curr = Carbon::parse($last_due_date)->addDay();
				$int_end = Carbon::parse($due_date);

				// Summarize bank debit and cashbook credit from the day after the last due date to the current due date (included)

				while($curr <= $int_end) {
					$curr_format = $curr->format('Y-m-d');
	
					if(array_key_exists($curr_format, $bank_summary)) {
						$nb_bank_debit += $bank_summary[$curr_format]->nb_bank_debit;
						$total_bank_debit += $bank_summary[$curr_format]->total_bank_debit;
						$nb_cashbook_credit += $bank_summary[$curr_format]->nb_cashbook_credit;
						$total_cashbook_credit += $bank_summary[$curr_format]->total_cashbook_credit;
					}
					
					$curr->addDay();
				}
			}

			// The last due date becomes the current due date

			$last_due_date = $due_date;
			
			// Compute the net

			$net = abs($v->total_debit - $v->total_credit);

			// Save the line

			$output[] = [
				$due_date,
				$v->nb_win,
				$v->total_debit,
				$v->total_credit,
				$v->total_debit >= $v->total_credit ? $net : '',
				$v->total_credit > $v->total_debit ? $net : '',
				$nb_bank_debit,
				$total_bank_debit,
				$nb_cashbook_credit,
				$total_cashbook_credit,
			];
		}

		// Save the file

		$destPath = storage_path('app/tmp/zeepay-summary.xlsx');

		SimpleXLSXGen::fromArray($output)->saveAs($destPath);
	}

	// Test bank and cashbook

	public function test_files()
	{
		// Open bank statement file

		dump('Open bank statement file');

		if (!($srcFile = SimpleXLSX::parse(storage_path("/app/tmp/Bank Files/1103002.xlsx")))) {
			return 'Unable to parse the source file: ' . SimpleXLSX::parseError();
		}

		// Read the 5 first rows

		$i = 0;
	
		foreach ($srcFile->readRows() as $data) {
			// Skip the first row

			if($i++ == 0) {
				continue;
			}

			var_dump($data);

			if($i == 5) {
				break;
			}
		}

		// Open cashbook file

		dump('Open cashbook file');

		if (!($srcFile = SimpleXLSX::parse(storage_path("/app/tmp/Bank Files/CB-ABSA Settlement Account _1103002.xlsx")))) {
			return 'Unable to parse the source file: ' . SimpleXLSX::parseError();
		}

		// Read the 5 first rows

		$i = 0;
	
		foreach ($srcFile->readRows() as $data) {
			// Skip the first row

			if($i++ == 0) {
				continue;
			}

			var_dump($data);

			if($i == 5) {
				break;
			}
		}
	}

	// Get unreferenced

	public function get_unref()
	{
		// Get the unrefs

		$unrefs = DB::table('zp_unref')
		->orderBy('id')
		->lazy(30000);

		// Create the output array

		$output = [
			[
				'Reference',
				'ISS',
				'Card Number',
				'ACQ',
				'Merchant Description',
				'Terminal ID',
				'Type',
				'Tr Date',
				'Debit',
				'Crédit'
			]
		];

		// For each unref

		foreach ($unrefs as $unref) {
			// Save the line

			$output[] = [
				$unref->reference,
				$unref->iss,
				$unref->card_number,
				$unref->acq,
				$unref->merchant_desc,
				$unref->terminal_id,
				$unref->type,
				$unref->tr_date,
				$unref->debit,
				$unref->credit
			];
		}

		// Save the file

		$destPath = storage_path('app/tmp/zeepay-unreferenced.xlsx');

		SimpleXLSXGen::fromArray($output)->saveAs($destPath);
	}
}
