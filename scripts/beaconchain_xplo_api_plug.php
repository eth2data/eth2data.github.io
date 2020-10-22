<?php

	require_once "vendor/autoload.php";

	use getoptimised\db;

	const CALLING_API = 'bc_get_stats';
	
	const BC_BLOCK = 'bc_block';
	const BC_PPSLSH = 'bc_proposerslashings';
	const BC_ATRSLSH = 'bc_attesterslashings';
	const BC_BLKATTST = 'bc_block_attestations';
	const BC_BLKDEP = 'bc_block_deposits';
	const BC_VOLEXT = 'bc_voluntaryexits';
	
	const BC_VALIDATOR = 'bc_validator';
	const BC_VAL_PERF = 'bc_performance';
	const BC_VAL_BAL_HIST = 'bc_balancehistory';
	const BC_VALATTST = 'bc_val_attestations';
	const BC_VALDEP = 'bc_val_deposits';
	const BC_PRPSL = 'bc_proposals';
	
	const PULL_BLOCK = false;
	const PULL_VAL = false;
	
	$url_latest_stats = 'https://beaconcha.in/latestState';
	$url_validators_count = 'https://beaconcha.in/validators/data?filterByState=all&draw=0&start=0&length=0';
	$url_api_prefix = 'https://beaconcha.in/api/v1/';


	$dsn = 'sqlsrv:Server='.getenv('MSSQL_HOST').';Database='.getenv('DPC_DBNAME');
	$db = new db($dsn, getenv('DPC_USER'), getenv('DPC_PASS'));
	
	$apiKey = 'sw2Ea2X2WthX9OQ5VyO3EO9OONgGWB9vnU5t4gov';

	$last_slot = $db->getTableCol(BC_BLOCK, ['(isnull(max(slot), -1) + 1) AS slot'], [])['result'][0]['slot'];
	$current_slot = json_decode(curl_do_get($url_latest_stats), true)['currentSlot'];

	$last_slot = 0;
	$current_slot = 466335;
	$last_attempt = -1;
/*
	//get block metrics
	for ($i=$last_slot; $i<=$current_slot; $i++) {
	//foreach ([  ] as $i) {
		echo $i."\n";
		if (PULL_BLOCK)
			$blocks = curl_do_get($url_api_prefix.str_replace('{slot}', $i, 'block/{slot}')."?api_key=$apiKey");
		else
			$blocks = json_encode([ 'data' => $db->getTableCol(BC_BLOCK, ['*'], [ 'slot' => $i ] )['result'] ]);

		if (strlen($blocks) > 0) {
			$blocks = json_decode($blocks, true)['data'];

			if (is_array($blocks) && count($blocks) > 0) {

				if (array_key_exists('slot', $blocks))
					$blocks = array($blocks);

				$first_item = true;

				foreach ($blocks as $block) {
					if (array_key_exists('attesterslashingscount', $block) && $block['attesterslashingscount'] > 0 && $first_item) {
						$attesterslashings = curl_do_get($url_api_prefix.str_replace('{slot}', $i, 'block/{slot}/attesterslashings')."?api_key=$apiKey");
						if (strlen($attesterslashings) > 0) {
							$attesterslashings = json_decode($attesterslashings, true)['data'];
				
							if (is_array($attesterslashings)) {
								if (array_key_exists('attestation1_index', $attesterslashings))
									$attesterslashings = array($attesterslashings);

								foreach ($attesterslashings as $attesterslashing) {
									if (count($attesterslashing) != 20) {
										print_r($attesterslashing);
										die();
									}

									$ret = $db->insert(BC_ATRSLSH, $attesterslashing);
									if (!$ret['success']) {
										api_log($db, 'DB Insert Error: '.$ret['message']."\n");
										die();
									}
								}
							}
						}
					}

					if (array_key_exists('proposerslashingscount', $block) && $block['proposerslashingscount'] > 0 && $first_item) {
						$proposerslashings = curl_do_get($url_api_prefix.str_replace('{slot}', $i, 'block/{slot}/proposerslashings')."?api_key=$apiKey");
						if (strlen($proposerslashings) > 0) {
							$proposerslashings = json_decode($proposerslashings, true)['data'];

							if (is_array($proposerslashings)) {
								if (array_key_exists('proposerindex', $proposerslashings))
									$proposerslashings = array($proposerslashings);

								foreach ($proposerslashings as $proposerslashing) {
									if (count($proposerslashing) != 13) {
										print_r($proposerslashing);
										die();
									}

									$ret = $db->insert(BC_PPSLSH, $proposerslashing);
									if (!$ret['success']) {
										api_log($db, 'DB Insert Error: '.$ret['message']."\n");
										die();
									}
								}
							}
						}
					}

					if (array_key_exists('attestationscount', $block) && $block['attestationscount'] > 0 && $first_item) {
						$attestations = curl_do_get($url_api_prefix.str_replace('{slot}', $i, 'block/{slot}/attestations')."?api_key=$apiKey");
						if (strlen($attestations) > 0) {
							$attestations = json_decode($attestations, true)['data'];

							if (is_array($attestations)) {
								if (array_key_exists('attestation1_index', $attestations))
									$attestations = array($attestations);

								foreach ($attestations as $attestation) {
									if (count($attestation) != 20) {
										print_r($attestation);
										die();
									}

									$ret = $db->insert(BC_BLKATTST, $attestation);
									if (!$ret['success']) {
										api_log($db, 'DB Insert Error: '.$ret['message']."\n");
										die();
									}
								}
							}
						}
					}

					if (array_key_exists('depositscount', $block) && $block['depositscount'] > 0 && $first_item) {
						$deposits = curl_do_get($url_api_prefix.str_replace('{slot}', $i, 'block/{slot}/deposits')."?api_key=$apiKey");
						if (strlen($deposits) > 0) {
							$deposits = json_decode($deposits, true)['data'];

							if (is_array($deposits)) {
								if (array_key_exists('block_slot', $deposits))
									$deposits = array($deposits);

								$toInsert = [];
								foreach ($deposits as $deposit) {
									if (count($deposit) != 7) {
										print_r($deposit);
										die();
									}

									$toInsert[] = $deposit;
									
									if (count($toInsert) > 99) {
										$ret = $db->insert(BC_BLKDEP, $toInsert);
										if (!$ret['success']) {
											api_log($db, 'DB Insert Error: '.$ret['message']."\n");
											die();
										}
										
										$toInsert = [];
									}
								}
								
								if (count($toInsert) > 0) {
									$ret = $db->insert(BC_BLKDEP, $toInsert);
									if (!$ret['success']) {
										api_log($db, 'DB Insert Error: '.$ret['message']."\n");
										die();
									}
									
									$toInsert = [];
								}
							}
						}
					}

					if (array_key_exists('voluntaryexitscount', $block) && $block['voluntaryexitscount'] > 0 && $first_item) {
						$voluntaryexits = curl_do_get($url_api_prefix.str_replace('{slot}', $i, 'block/{slot}/voluntaryexits')."?api_key=$apiKey");
						if (strlen($voluntaryexits) > 0) {
							$voluntaryexits = json_decode($voluntaryexits, true)['data'];

							if (is_array($voluntaryexits)) {
								if (array_key_exists('block_slot', $voluntaryexits))
									$voluntaryexits = array($voluntaryexits);

								foreach ($voluntaryexits as $voluntaryexit) {
									if (count($voluntaryexit) != 5) {
										print_r($voluntaryexit);
										die();
									}

									$ret = $db->insert(BC_VOLEXT, $voluntaryexit);
									if (!$ret['success']) {
										api_log($db, 'DB Insert Error: '.$ret['message']."\n");
										die();
									}
								}
							}
						}
					}


					if (PULL_BLOCK) {
						$ret = $db->insert(BC_BLOCK, $block);
						if (!$ret['success']) {
							api_log($db, 'DB Insert Error: '.$ret['message']."\n");
							die();
						}
					}
					
					$first_item = false;
				}
			} else {
				if (PULL_BLOCK) {
					print_r($blocks);
					die();
				}
			}
		} else {
			print_r($blocks);
			die();
		}
	}
*/

	$last_validator = $db->getTableCol(BC_VALIDATOR, ['(isnull(max(validatorindex), -1) + 1) AS validatorindex'], [])['result'][0]['validatorindex'];
	$current_validator = json_decode(curl_do_get($url_validators_count), true)['recordsTotal'];

	$last_validator = 0;
	

	//get validator metrics
	$i = $last_validator;
	while ($i <= $current_validator) {
		echo $i."\n";
		if (PULL_VAL)
			$validators = curl_do_get($url_api_prefix.str_replace('{index}', $i, 'validator/{index}')."?api_key=$apiKey");
		else
			$validators = json_encode([ 'data' => $db->getTableCol(BC_VALIDATOR, ['*'], [ 'validatorindex' => $i ] )['result'] ]);

		if (strlen($validators) > 0) {
			$validators = json_decode($validators, true)['data'];

			if (is_array($validators) && count($validators) > 0) {

				if (array_key_exists('validatorindex', $validators))
					$validators = array($validators);
				
				$first_item = true;

				foreach ($validators as $validator) {/*
					//commented out as doing so only returns the last 100 rows across both index and epoch
					//calling one index at a time returns last 100 epoch

					//make 100 csv
					//$indices = [];
					//for ($j=0; $j<100; $j++) {
					//	$indices[] = $i;
					//	$i++;
					//}
					//$pull_indices = implode(",", $indices);

					//performance
					if ($first_item) {
						$validator_performance = curl_do_get($url_api_prefix.str_replace('{index}', $i, 'validator/{index}/performance')."?api_key=$apiKey");
						if (strlen($validator_performance) > 0) {
							$validator_performance = json_decode($validator_performance, true)['data'];

							if (count($validator_performance) > 0) {
								if (!array_key_exists('validatorindex', $validator_performance)) {
									print_r($validator_performance);
									die('invalid perf');
								}
								
								$ret = $db->insert(BC_VAL_PERF, $validator_performance);
								if (!$ret['success']) {
									api_log($db, 'DB Insert Error: '.$ret['message']."\n");
									die();
								}
							}
						}
					}
					
					//balancehistory
					if ($first_item) {
						$validator_balancehistory = curl_do_get($url_api_prefix.str_replace('{index}', $i, 'validator/{index}/balancehistory')."?api_key=$apiKey");
						if (strlen($validator_balancehistory) > 0) {
							$validator_balancehistory = json_decode($validator_balancehistory, true)['data'];

							if (count($validator_balancehistory) > 0) {
								$ret = $db->insert(BC_VAL_BAL_HIST, $validator_balancehistory);
								if (!$ret['success']) {
									api_log($db, 'DB Insert Error: '.$ret['message']."\n");
									die();
								}
							}
						}
					}
*/
					//deposits
					if ($first_item) {
						$deposits = curl_do_get($url_api_prefix.str_replace('{index}', $i, 'validator/{index}/deposits')."?api_key=$apiKey");
						if (strlen($deposits) > 0) {
							$deposits = json_decode($deposits, true)['data'];

							if (is_array($deposits)) {
								if (array_key_exists('block_number', $deposits))
									$deposits = array($deposits);

								$toInsert = [];
								foreach ($deposits as $deposit) {
									if (count($deposit) != 13) {
										print_r($deposit);
										die();
									}
									
									$deposit['validatorindex'] = $i;

									$toInsert[] = $deposit;
									
									if (count($toInsert) > 99) {
										$ret = $db->insert(BC_VALDEP, $toInsert);
										if (!$ret['success']) {
											api_log($db, 'DB Insert Error: '.$ret['message']."\n");
											die();
										}
										
										$toInsert = [];
									}
								}
								
								if (count($toInsert) > 0) {
									$ret = $db->insert(BC_VALDEP, $toInsert);
									if (!$ret['success']) {
										api_log($db, 'DB Insert Error: '.$ret['message']."\n");
										die();
									}
									
									$toInsert = [];
								}
							}
						}
					}

					if (PULL_VAL) {
						$ret = $db->insert(BC_VALIDATOR, $validator);
						if (!$ret['success']) {
							api_log($db, 'DB Insert Error: '.$ret['message']."\n");
							die();
						}
					}
				
					$first_item = false;
				}
			}
		}
		
		$i++;
	}

	api_log($db, 'Completed successfully!');


	function curl_do_get($url) {
		$ch = null;
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data = curl_exec($ch);

		if ($data !== false) {
			curl_close($ch);
			return $data;
		} else {
			$data = curl_error($ch);
			curl_close($ch);
			api_log($db, $data);
			return '';
		}
	}


	function api_log($db, $desc) {
		$ret = $db->insert('api_log', [
										"calling_api" => CALLING_API,
										"description" => $desc,
									]);
	}
	
	
	