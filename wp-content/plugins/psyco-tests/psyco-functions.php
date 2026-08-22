<?php
function show_var($var, $prompt="") {
	//!!!return;  to disable or enable showing variables one time in here
	$len = 0;
	$type = gettype($var);
	switch ($type) {
	case 'string':
		$len = strlen($var);
		break;
	case 'array':
		$len = count($var);
		break;
	case 'object':
		$len = count((array)$var);
		break;
	}
	echo "<div dir= 'ltr' style='color:yellow;background-color:blue'><p>$prompt:"." length:($len)</p></div>";
	echo "<div dir='ltr' style='color:black;background-color:gray; border: 3px solid blue' ><p>";
	if (is_array ($var)){
		print_r ($var);
	}
	else if ($var === NULL) {
		echo 'NULL!!!';		
	}
	else {
		echo $var;
	}
	echo '</p></div>';
}

function add_scroll_to_question_script_to_footer(){ 
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			var startPoint = jQuery('#QuestionForm');
			if(startPoint.length) {
				// Scroll to the specific point
				//jQuery(window).scrollTop(startPoint.offset().top);
				jQuery('html, body').animate({
					scrollTop: startPoint.offset().top
				}, 500);
			}
		});
	</script>
<?php
}

function remove_what_for_consultant ($html){
	if (is_string ($html) === false)
	{
		return;
	}
	// Create a new DOMDocument object
	$doc = new DOMDocument();
	
	//show_var ($html, 'HTML before parsing for consultant:');
	// Load the html file as a string
	$doc->loadHTML($html);
	//show_var ($doc, "DOC AFTER LOADING HTML:");
	// Get the errors
	if ($doc->loadHTML($html)) {
		// Create a new DOMXPath object
		$xpath = new DOMXPath($doc);

		// Query the elements that have the class attribute "for_consultant"
		$elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' for_consultant ')]"); 

		// Loop through the query results
		foreach ($elements as $element) {
		// Remove the element from its parent node
			$element->parentNode->removeChild($element);
		}

		// Save the modified html file as a string
		$html = $doc->saveHTML();
		

		// Do something with the html file
		return  $html;	
	}
	return null;
}

function callService($service_name, $params)
{
    // Shared MARIS service configuration
    include plugin_dir_path(__FILE__) . 'wsdl.inc.php';

    $merchant_code = '1BCF9EF3-04C1-48AE-8709-5B6138045D75';
    // $merchant_code = 'E0157537-4535-4A65-81EB-EE98603FBB45'; // Dr. Sahebi Clinic

    $params = array('merchant_code' => $merchant_code) + $params;

    // Convert the shared SOAP/WSDL URL to the REST base URL
    $rest_url = preg_replace('/\?WSDL$/i', '', $wsdl);
    $rest_url .= '/' . $service_name;

    $response = wp_remote_post(
        $rest_url,
        array(
            'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
            ),
            'body' => wp_json_encode($params),
        )
    );

    if (is_wp_error($response)) {
        echo 'Error: ' . $response->get_error_message();
        return '';
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if ($status_code < 200 || $status_code >= 300) {
        echo '<p>REST API Error: ' . esc_html($status_code) . '</p>';
        echo '<pre>' . esc_html($body) . '</pre>';
        return '';
    }

    $result = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '<p>Invalid JSON response:</p>';
        echo '<pre>' . esc_html($body) . '</pre>';
        return '';
    }

    // ASP.NET REST response
    if (isset($result['d'])) {
        $result = $result['d'];
    }

    // Keep compatibility with the old SOAP response
    if (isset($result[$service_name . 'Result'])) {
        $res = $result[$service_name . 'Result'];
    } else {
        $res = $result;
    }

    // Preserve existing consultant filtering
    $restemp = $res;

    if (!current_user_can("consultant_capability")) {
        $ret = remove_what_for_consultant($restemp);

        if ($ret !== null) {
            $res = $ret;
        }
    }

    return $res;
}

/*
function callService ($service_name, $params)
{
	
//  CREATE CLIENT TO CALL SERVICE 
	include plugin_dir_path( __FILE__ ) . 'wsdl.inc.php';
	$client = new nusoap_client($wsdl, true);
	$client->decode_utf8 = false;

	$err = $client->getError();
	if ($err) {
		echo '<h2>Soap Constructor error</h2>' . $err;
		// At this point, you know the call that follows will fail
		exit();
	}
	$merchant_code = '1BCF9EF3-04C1-48AE-8709-5B6138045D75';// Choice Scale
	//!!! $merchant_code = 'E0157537-4535-4A65-81EB-EE98603FBB45'; // Dr. Sahebi Clinic
	
	
	$params = array('merchant_code' => $merchant_code)+$params; 

	$res = "";
	$result = $client->call($service_name, $params);

//	show_var ($result, '$result:');
	
	
	if (isset ($result) and $result !== false)
	{
		if (isset ($result[$service_name."Result"]))
		{
			$res = $result[$service_name."Result"];	
			//show_var ($res, '$res1:');
			$restemp = $res;
			if (!current_user_can("consultant_capability")) {
				$ret = remove_what_for_consultant ($restemp);
				//show_var ($ret, 'RETURN AFTER CONSULTANT');
				if ($ret !== null) {
					//show_var ($ret, "RET HTML:");
					$res = $ret;
				}
			}
			//show_var ($res, '$res2:');
		}
		else if ($result["faultstring"])
		{
			echo '<p>'.$result["faultcode"].'</p>'.$result["faultstring"]."<br/>";
		}
	}
	else if ($result === false) {
		echo 'Error: '.$client->getError();
	}
	//show_var ($res, 'before returning $res2:');
	return $res;
}
*/
function get_exam_action($item_id, $item_id_row, &$action, &$exam_id)
{
	global $wpdb;
	$status = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT status, exam_id FROM psyexm_exams WHERE item_id = %d and item_id_row = %d",
			$item_id,
			$item_id_row
		)
	);
	//show_var_dump ($status, 'status');

	if (is_null($status)) {
		$caption = 'شروع تست';
		$action = 'StartExam';
		$exam_id = -1;
	} elseif ($status->status == '0') {
		$caption = 'ادامه تست';
		$action = 'GetNextQuestion';
		$exam_id = $status->exam_id;
	} elseif ($status->status == '1') {
		$caption = 'دریافت تحلیل تست';
		$action = 'GetExamResult';
		$exam_id = $status->exam_id;
	}
	return $caption;
}


function package_profile ($user_id, $package_name, $order_id, $profile_exam_ids)
{
	$service = "noname";
	if ($package_name == 'before_marriage_package'){
		$package_name = 'پکیج پیش از ازدواج';
		$service = "GetPreMarriageProfile";
	}
	elseif ($package_name == 'talent_identification_package'){
		$package_name = 'پکیج استعداد یابی';
		$service = "GetTalentIdentificationProfile";
	}
	elseif ($package_name == 'homogeneity_package'){
		$package_name = 'پکیج سنجش همگنی';
		$service = "GetHomogeneityProfile";
	}
	elseif ($package_name == 'mentalhealth_package'){
		$package_name = 'پکیج سلامت روان';
		$service = "GetMentalHealthProfile";
	}
	elseif ($package_name == 'basic_package'){
		$package_name = 'پکیج ارزیابی روانی پایه';
		$service = "GetBasicPsychoProfile";
	}
	echo '<tr><td><strong>' . $package_name . '</strong></td>'
	. '<td>'. $order_id.'</td>'
	. '<td>'
	. '<form  target=_self method="POST">'
	. '<input type="submit" name="' . 'ShowPackageResult' . '" class="button" value="' . 'مشاهده نتیجه کلی' . '"/>'
	. '<input type="hidden" name="'.$service.'" value = "'.$service.'" /> '
	. '<input type="hidden" name="profile_exam_ids" value = "'.implode (', ', $profile_exam_ids).'" />'
	. '<input type="hidden" name="uid" value = "'.$user_id.'" /> '
	. '</form>'
	. '</td><tr>';

}


function show_exams ($user_id){
	//!!! echo $user_id;
	echo '<div class = "myexams">';
	// Get orders by user
	$orders = get_user_orders($user_id);
	
	if (empty($orders))
	{
		echo '<h2>آزمونی برای شما ثبت نشده است.</h2>';
	}
	else {
		echo '<h3>آزمونهای خریداری شده:</h3>';
		
		echo '<table id="ExamList"><thead><tr><th>نام تست</th><th>سفارش</th><th> </th></tr></thead><tbody>';
		$order_id = 0;
		$profile_exam_ids_before_marriage = [];
		$profile_exam_ids_talent_identification = [];
		$profile_exam_ids_mentalhealth = [];
		$profile_exam_ids_basic = [];
		
		foreach ($orders as $order) {
			// Get items in order 
			$items = $order->get_items();
			$list_talent_identification = array ('neo_240', 'basic_needs', 'eq_baron', 'mbti','gardner_multiple_intelligences');
			$list_before_marriage = array ('neo_240', 'basic_needs', 'eq_baron', 'mbti', 'irrational_beliefs_about_marriage', 'attitudes_about_marriage','five_love_languages_receive','five_love_languages_give','thomas_conflict_management','attachment_hazan');
			$list_mentalhealth = array ('neo_240', 'basic_needs', 'eq_baron', 'mbti', 'rpwb18', 'ghq');
			$list_basic = array ('neo_240', 'basic_needs', 'eq_baron', 'mbti');
			
			$order_id = $order->get_id();
			
			
			foreach ($items as $item_id => $item_data) {
				$item_count = wc_get_order_item_meta($item_id, '_qty', true);


				$post = get_post($item_data['product_id']);
				$slug = $post->post_name;

				for ($i = 0; $i < $item_count; $i++) {
					$test_status = get_exam_action($item_id, $i, $action, $exam_id);
					// پکیج استعداد یابی
					if (in_array ($slug, $list_talent_identification ) and $test_status == 'دریافت تحلیل تست'){
						array_splice  ($list_talent_identification, array_search ($slug, $list_talent_identification) , 1);
						$profile_exam_ids_talent_identification[] = $exam_id;
					}
					// پکیج پیش از ازدواج
					if (in_array ($slug, $list_before_marriage ) and $test_status == 'دریافت تحلیل تست'){
						array_splice  ($list_before_marriage, array_search ($slug, $list_before_marriage) , 1);
						$profile_exam_ids_before_marriage[] = $exam_id;
					}
					// پکیج سلامت روان
					if (in_array ($slug, $list_mentalhealth ) and $test_status == 'دریافت تحلیل تست'){
						array_splice  ($list_mentalhealth, array_search ($slug, $list_mentalhealth) , 1);
						$profile_exam_ids_mentalhealth[] = $exam_id;
					}
					// پکیج پایه
					if (in_array ($slug, $list_basic) and $test_status == 'دریافت تحلیل تست'){
						array_splice  ($list_basic, array_search ($slug, $list_basic) , 1);
						$profile_exam_ids_basic[] = $exam_id;
					}
					$order_id = $item_data['order_id'];

					echo '<tr><td>' . $item_data['name'] . '</td>'
//						. '<td><a href = "' . esc_url($order->get_view_order_url()) . '">#' . $item_data['order_id'] . '</a></td>'
						. '<td>'. $item_data['order_id'].'</td>'
						. '<td>'
						. '<form target=_self method="POST">';
						$order_status = $order->get_status();
						
					if (!($order_status === "completed" or $order_status === "processing"))
					{
						echo esc_html( wc_get_order_status_name( $order->get_status() ) );
					}
					else {
						echo '<input type="submit" name="' . $action . '" class="button" value="' . $test_status . '"/>'
						. '<input type="hidden" name = "TestType" value ="' . $slug . '"\/>'
						. '<input type="hidden" name = "ExamID" value ="' . $exam_id . '"\/>'
						. '<input type="hidden" name = "ItemID" value="' . $item_id . '"\/>'
						. '<input type="hidden" name="uid" value = "'.$user_id.'" /> ';
						
					}
					echo '</form>'
					. '</td></tr>';
				}
			}
			//echo '<tr><td colspan="3">Order ID:'.$order_id.'BM Count: '. count ($list_before_marriage).'TI Count:'.count ($list_talent_identification).'</td></tr>';
	
			if ( count ($list_talent_identification) == 0) {
				package_profile ($user_id,'talent_identification_package', $order_id, $profile_exam_ids_talent_identification);
			}

			if ( count ($list_before_marriage) == 0) {
				package_profile ($user_id,'before_marriage_package', $order_id, $profile_exam_ids_before_marriage);
			}
			
			if ( count ($list_before_marriage) == 0) {
				package_profile ($user_id,'homogeneity_package', $order_id, $profile_exam_ids_before_marriage);
			}
			if ( count ($list_mentalhealth) == 0) {
				package_profile ($user_id,'mentalhealth_package', $order_id, $profile_exam_ids_mentalhealth);
			}
			if ( count ($list_basic) == 0) {
				package_profile ($user_id,'basic_package', $order_id, $profile_exam_ids_basic);
			}

		}
		echo '</tbody></table>';
	}
	echo '</div>';
}
function show_tests($user_id){
	// Setup what should we do 
	$start_exam_sat = isset($_POST["StartExam"]);
	$save_get_next_question_sat = isset($_POST["SaveGetNextQuestion"]);
	$save_get_prev_question_sat = isset($_POST["SaveGetPrevQuestion"]);
	$get_next_question_sat = isset($_POST["GetNextQuestion"]);
	$get_exam_result_sat = isset($_POST["GetExamResult"]);
	$get_talent_profile =  isset($_POST["GetTalentIdentificationProfile"]);
	$get_premarriage_profile = 	isset($_POST["GetPreMarriageProfile"]);
	$get_homogeneity_profile = 	isset($_POST["GetHomogeneityProfile"]);
	$get_mentalhealth_profile = 	isset($_POST["GetMentalHealthProfile"]);
	$get_basic_profile = 	isset($_POST["GetBasicPsychoProfile"]);
	
	if ($start_exam_sat || $save_get_next_question_sat || $save_get_prev_question_sat || $get_next_question_sat || $get_exam_result_sat 
		|| $get_talent_profile || $get_premarriage_profile || $get_homogeneity_profile || $get_mentalhealth_profile || $get_basic_profile) {
		//		include get_theme_root().'/piechart.php';

		
		$remain_questions = -1;
		$params=[];
		if ($start_exam_sat && isset($_POST['TestType'])) {
			$test_type = $_POST['TestType'];
			$params = array( 'test_type' => $test_type);
		} elseif ($get_next_question_sat) {
			$exam_id = $_POST['ExamID'];
			$params = array( 'exam_id_str' => $exam_id, 'last_question' => -2, 'nextQ' => 1);
		} elseif ($save_get_next_question_sat) {
			$exam_id = $_POST["ExamID"];
			$selected_item = $_POST["Answer"];
			if (is_null($selected_item))
				$selected_item = -1;
			$QNo = $_POST["QuestionNo"];
			$params = array( 'exam_id' => $exam_id, 'question_no' => $QNo, 'answer' => $selected_item, 'nextQ' => 1);
		} elseif ($save_get_prev_question_sat) {
			$exam_id = $_POST["ExamID"];
			$selected_item = $_POST["Answer"];
			if (is_null($selected_item))
				$selected_item = -1;

			$QNo = $_POST["QuestionNo"];
			$params = array( 'exam_id' => $exam_id, 'question_no' => $QNo, 'answer' => $selected_item, 'nextQ' => -1);

		} elseif ($get_exam_result_sat) {
			$exam_id = $_POST["ExamID"];
			$params = array( 'exam_id_str' => $exam_id);
		} elseif ($get_talent_profile) {
			$params = array( 'exam_id_str' => $_POST["profile_exam_ids"] );
		} elseif ($get_premarriage_profile) {
			$params = array( 'exam_id_str' => $_POST["profile_exam_ids"] );
		} elseif ($get_homogeneity_profile) {
			$params = array( 'exam_id_str_m' => $_POST["profile_exam_ids"], 'exam_id_str_f' => $_POST["profile_exam_ids"] );
		} elseif ($get_mentalhealth_profile) {
			$params = array( 'exam_id_str' => $_POST["profile_exam_ids"] );
		} elseif ($get_basic_profile) {
			$params = array( 'exam_id_str' => $_POST["profile_exam_ids"] );
		}

		try {
			//3. CALL SERVICE FUNCTIONS
			$res = NULL;
			$servic_name = "";
			if ($get_talent_profile)
			{
				$res = callService ('GetTalentIdentificationProfile', $params);
			}
			if ($get_premarriage_profile){
				$res = callService ('GetPreMarriageProfile', $params);
			}
			if ($get_homogeneity_profile){
				$res = callService ('GetHomogeneityProfile', $params);
			}
			if ($get_mentalhealth_profile){
				$res = callService ('GetMentalHealthProfile', $params);
			}
			if ($get_basic_profile){
				$res = callService ('GetBasicPsychoProfile', $params);
			}

			if ($start_exam_sat) {
				$res = callService('StartExam', $params);
				/*
				show_var($res, "After Service Return:"); //!!!
				wp_die();
				// */
				if (isset ($res["ReturnStatus"]) && $res["ReturnStatus"]=="succeed") {
					$exam_id = $res["ExamID"];
					$item_id = $_POST['ItemID'];
					if ($exam_id) {
						save_exam($item_id, $exam_id, 0);
					}
				}
				else {
						if (isset ($res["ReturnStatus"])) {
							wc_add_notice (_e($res["ReturnStatus"]));
						}
						
						return;
				}
			} elseif ($save_get_next_question_sat) {
				$res = callService('SaveAnswer', $params);

			} elseif ($save_get_prev_question_sat) {
				$res = callService('SaveAnswer', $params);

			} elseif ($get_next_question_sat) {
				//show_var_dump ($params,'params');
				$res = callService('GetNextQuestion', $params);
			}
			if ($get_talent_profile)
			{
				if (is_null($res)){
					echo 'تست های پاسخ داده شده جهت تعیین پروفایل روانی استعداد یابی کافی نیستند.';
					return;
				}
				echo $res;
			}
			else if ($get_premarriage_profile) {
				if (is_null($res)){
					echo 'تست های پاسخ داده شده جهت تعیین پروفایل روانی پیش از ازدواج کافی نیستند.';
					return;
				}
				echo $res;
			} else if ($get_homogeneity_profile) {
				if (is_null($res)){
					echo 'تست های پاسخ داده شده جهت تعیین پروفایل روانی همگنی کافی نیستند.';
					return;
				}
				echo $res;
			}
			else if ($get_mentalhealth_profile) {
				if (is_null($res)){
					echo 'تست های پاسخ داده شده جهت تعیین پروفایل سلامت روان کافی نیستند.';
					return;
				}
				echo $res;
			} 
			else if ($get_basic_profile) {
				if (is_null($res)){
					echo 'تست های پاسخ داده شده جهت تعیین پروفایل ارزیابی روانی پایه';
					return;
				}
				echo $res;
			}
			elseif ($get_exam_result_sat) //Exam Result
			{
				echo '<form target = _self method="POST">';
	
				$res = callService('GetExamResult', $params);
				$user = wp_get_current_user();
				$res = str_replace ('{user_name}', $user->display_name, $res);

				//echo '<form target = _self method="POST">';
				//!!!	echo 'تحلیل تست در اختیار سازمان شما قرار می گیرد';
				echo $res;
								
				if (isset ($res["PsycoScale"]) ) {
					echo '<table>';
					if (array_key_exists("PsycoScaleDesc", $res["PsycoScale"])) {
						foreach ($res as $item) {
							//show_var_dump($item, '$item');
							echo '<tr><td>' . $item['PsycoScaleDesc'] . '</td><td>' . $item['PsycoScaleScore'] . '</td></tr>';
						}
					}
					else
					{
						foreach ($res["PsycoScale"] as $item) {
							//show_var_dump($item, '$item');
							echo '<tr><td>' . $item['PsycoScaleDesc'] . '</td><td>' . $item['PsycoScaleScore'] . '</td></tr>';
						}

					}
					echo '</table>';
				}
				
				
			} elseif (!is_null($res)){// Exam questions
				//show_var_dump($res, '$res');
				$remain_questions = $res['RemainQuestionCnt'];
				$TestQuestionCnt = $res['TestQuestionCnt'];
				
				//$CurNo = $QNo + 1;
				$CurNo = $res["QuestionNo"];

				if ($remain_questions <= 0 && $TestQuestionCnt > 0) {
					update_exam_status($exam_id, 1);
				}
				echo '<div id="QuestionForm" class="ExamManager"><form   target=_self method="POST" >';
				echo "<p>تست $CurNo از $TestQuestionCnt </p>";
				foreach ($res as $key => $value) {

					if ($key == 'QuestionNo') {
						$QNo = $value;
						echo '<div class="QuestionDescription" >'.$value . '. ';
					} elseif ($key == 'QuestionDescription') {
						//	show_var_dump($value, 'Question Description value');
						echo convert_to_utf8($value) . '</div>';
						//					echo mb_detect_encoding($value).'<br>';
					} elseif ($key == "RemainQuestionCnt") {
						//show_var_dump($value, 'Remain Question Count');

					} elseif ($key == 'Answers') {
						//var_dump ($value['Answer'][0]);

						foreach ($value['Answer'] as $answer_item) {
							//	show_var_dump($answer_item, 'answer_item');
							$checked = ($answer_item['AnsweredItemNo'] == $answer_item['ItemNo']) ? 'checked' : '';
							$ans_desc = convert_to_utf8($answer_item['Description']);
							$encoding = mb_detect_encoding($answer_item['Description']);
							$item_no = $answer_item['ItemNo'];
							echo '<label for="itm' . $answer_item['ItemNo'] . '">'
								.'<input id = itm' . $answer_item['ItemNo'] . ' type="radio" name="Answer" value=' . $answer_item['ItemNo'] . ' ' . $checked . '  required onclick1="document.getElementById(\'btnSaveAndNext\').click();">'
								. $answer_item['ItemNo'] . " ." . $ans_desc.'</label>';
								;
							//echo '<label for="itm' . $answer_item['ItemNo'] . '">' . $answer_item['ItemNo'] . " ." . $ans_desc .'</label><br>';
						}
					}
				}

			
				echo '<div class="button-holder">';
				if ($QNo > 1) {
					echo '<input id="btnSaveAndPrevious" type="submit" name="SaveGetPrevQuestion" class="button" '
						. 'value= "قبلی"/>';
				}
				if ($QNo < $TestQuestionCnt) {
					echo '<input id="btnSaveAndNext" type="submit" name="SaveGetNextQuestion" class="button" '
						. 'value= "بعدی"/>';
				}
			
				if ($remain_questions <= 0 && $TestQuestionCnt > 0) {
					echo '<input id="btnGetExamResult" type="submit" name="GetExamResult" class="button" '
						. 'value= "دریافت تحلیل تست"/>';
				}
				else {
			
				echo '<input id="btnSave" type="submit" name="SaveGetNextQuestion" class="button" '
						. 'value= "ثبت"/>';
				}

				echo '<input type="hidden" name = "QuestionNo" value="' . $QNo . '" />'
					. '<input type="hidden" name = "ExamID" value= ' . $exam_id . ' />';
				echo '<div class="button-holder">';
				echo '<input type="hidden" name="uid" value = "'.$user_id.'" /> ';
				echo '</form></div><br><br>';
				add_action('wp_footer', 'add_scroll_to_question_script_to_footer'); 
			} 
		} catch (Exception $e) {
			echo 'Caught exception: ', $e->getMessage(), "\n";
		}

	}
	else { //اگر در حال تست گرفتن نباشد نمایش لیست آزمونهای خریداری شده
		show_exams ($user_id);
	}

}
//General Tools
function findFunction($func) {
  if ( is_array($func) ) {
    $rf = is_object($func[0]) ? new ReflectionObject($func[0]) : new ReflectionClass($func[0]);
    $rf = $rf->getMethod($func[1]);
  }
  else {
    $rf = new ReflectionFunction($func);
  }
  printf("%s %d-%d\n", $rf->getFileName(), $rf->getStartLine(), $rf->getEndLine());
  $c = file($rf->getFileName());
  for ($i=$rf->getStartLine(); $i<=$rf->getEndLine(); $i++) {
    printf('%04d %s', $i, $c[$i-1]);
  }
}
function save_exam($item_id, $exam_id, $status){
	global $wpdb;


	if (is_numeric ($item_id) && is_numeric ($exam_id) && is_numeric ($status))
	{
		if (!$wpdb->insert('psyexm_exams', array('item_id' => $item_id, 'exam_id' => $exam_id, 'status' => $status))) 
		{

		}
	}
	else {
		echo ("exam id:'$exam_id' item id:$item_id status: $status");
	}
}
function update_exam_status($exam_id, $status){
	global $wpdb;
	//	show_var_dump ($item_id ,'item_id');
//	show_var_dump ($exam_id ,'exam_id');
//	show_var_dump ($status ,'status');

	if (!$wpdb->update('psyexm_exams', array('status' => $status), array('exam_id' => $exam_id))) {

	}
}