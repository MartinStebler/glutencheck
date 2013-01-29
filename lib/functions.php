<?php
session_start();
// Funktionen

$num_messages = 0;
$return = "";
$idx = 0;

function search() {
	$q = mysql_real_escape_string($_GET['q']);
	$sql = mysql_query("
		SELECT name, class_id FROM class WHERE name like  '".$q."%'
		");
	//Create an array with the results
	$results=array();
	while($v = mysql_fetch_object($sql)){
		$results[] = array(
			'class'=>$v->name
			);
	}
	//using JSON to encode the array
	echo json_encode($results);
}

function searchSubject() {
	$q = mysql_real_escape_string($_GET['q']);
	$sql = mysql_query("
		SELECT name, subject_id FROM subject WHERE name like  '".$q."%'
		");
	//Create an array with the results
	$results=array();
	while($v = mysql_fetch_object($sql)){
		$query = "SELECT user_id FROM user_has_subject WHERE user_id = ".$_SESSION['user_id']." AND subject_id = ".$v->subject_id;
		$result = mysql_query($query);
		if(mysql_num_rows($result)==1){
			$results[] = array(
			'subject'=>$v->name,
			'has'=>"yes"
			);
		} else {
		$results[] = array(
			'subject'=>$v->name,
			'has'=>"no"
			);
		}
	}
	//using JSON to encode the array
	echo json_encode($results);
}

function login($email, $password) {
	$email = mysql_real_escape_string($email);
	$passwort = mysql_real_escape_string($password);
	$query = "SELECT user_id FROM user WHERE email='".$email."' AND password='".$password."' AND activated=1 LIMIT 1";
	$result = mysql_query($query);
	while ($row = mysql_fetch_object($result)){
		$_SESSION['user_id'] = $row->user_id;
	}
	if (mysql_num_rows($result)>0){
		return $_SESSION['user_id'];
	} else {
		return false;
	}

}

function register($prename, $lastname, $email, $pw){
	$prename = mysql_real_escape_string($prename);
	$lastname = mysql_real_escape_string($lastname);
	$email = mysql_real_escape_string($email);
	$pw = md5(mysql_real_escape_string($pw));
	$query = "INSERT INTO user (prename, lastname, email, password, role_id, activated) VALUES ('".$prename."', '".$lastname."', '".$email."', '".$pw."', 1, 0)";
	$result = mysql_query($query);
	$date = date("Y-m-d H:i:s"); 
	$code = rand(1, 99999999);
	$result_act = mysql_query ("INSERT INTO activation (code, date, email, activated) VALUES ('$code', '$date', '$email', 0)");
	$absender = "skillmill.ch";
	mail($email, "Aktivierung - skillmill.ch", "Hallo,\n\num den Registierungsprozess abzuschlieﬂen, klicken Sie auf den folgenden Link:\n\nhttp://www.skillmill.ch/activate.php?c=$code", "FROM: $absender"); 
	return "Um die Registrierung abzuschlieﬂen, rufen Sie Ihr eMail-Postfach ab und klicken Sie auf den Aktivierungslink.";
}

function loggedin(){
	if(isset($_SESSION['user_id'])){
		return true;
	} else {
		return false;
	}
}

function message($message){
	$num_messages++;
	$return .= $message."<br/>";
}

function getMessages(){
	return $return;
}

function getMessageCount(){
	return $num_messages;
}

function logout(){
	session_start();
	session_unset();
	session_destroy();
	header('Location: index.php');
}

function getRoleName(){
	$user_id = $_SESSION['user_id'];
	$query = "SELECT name FROM role WHERE role_id = (SELECT role_id FROM user WHERE user_id = ".$user_id." LIMIT 1)";
	$result = mysql_query($query);
	while ($row = mysql_fetch_object($result)){
		$rolename = $row->name;
	}
	return $rolename;
}

function getClassName(){
	$user_id = $_SESSION['user_id'];
	$query = "SELECT name FROM class WHERE class_id = (SELECT class_id FROM class_has_user WHERE user_id = ".$user_id.")";
	$result = mysql_query($query);
	$i = 0;
	while ($row = mysql_fetch_object($result)){
		$classnames[$i] = $row->name;
		$i++;
	}
	return $classnames;
}

function getClassMembers($classname){
	$query = "SELECT name FROM user WHERE user_id = (SELECT user_id FROM class_has_user WHERE class_id = ".$classname.")";
	$result = mysql_query($query);
	$i = 0;
	while($row = mysql_fetch_object($result)){
		$classmembers[$i] = $row->name;
		$i++;
	}
	return $classmembers;
}

function getCurrentSite() {
	$url = $_SERVER['SCRIPT_NAME'];
	// Muss auf die Domain angepasst werden
	preg_match('@^(?:/skillmill/)?([^/]+)@i', $url, $matches);
	return $script = $matches[1];
}

function getCurrentSort($sort, $type='normal') {
	if (isset($_GET['s'])) {
		if ($_GET['s'] == $sort) {
			return 'class=active';
			} elseif ($_GET['s'] == 'sub' && $_GET['n'] == $sort) {
				return 'class=active';
				} elseif ($_GET['s'] == 'class' && $_GET['n'] == $sort) {
					return 'class=active';
				}
				} elseif (empty($_GET['s']) && $sort == 'all') {
					return 'class=active';
				}
			}

			function getUserPrename($user_id) {
				$query = "SELECT prename FROM user WHERE user_id = '$user_id' LIMIT 1";
				$result = mysql_query($query);
				while ($row = mysql_fetch_object($result)){
					$prename = $row->prename;
				}
				return $prename;
			}

			function getUserLastname($user_id) {
				$query = "SELECT lastname FROM user WHERE user_id = '$user_id' LIMIT 1";
				$result = mysql_query($query);
				while ($row = mysql_fetch_object($result)){
					$lastname = $row->lastname;
				}
				return $lastname;
			}

			function getUserEmail($user_id) {
				$query = "SELECT email FROM user WHERE user_id = '$user_id' LIMIT 1";
				$result = mysql_query($query);
				while ($row = mysql_fetch_object($result)) {
					$email = $row->email;
				}
				return $email;
			}

			function getUserPassword($user_id) {
				$query = "SELECT password FROM user WHERE user_id = '$user_id' LIMIT 1";
				$result = mysql_query($query);
				while ($row = mysql_fetch_object($result)) {
					$password = $row->password;
				}
				return $password;
			}

			function getTaskDate($until)	{
				$timestamp = strtotime($until);
				if ($timestamp != '') {
					$date = date('d.m.Y',$timestamp);
				}
				return $date;
			}

			function getNotificationCount($user_id) {
				$query = "SELECT task_id FROM user_has task WHERE user_id = ".$_SESSION['user_id']. " AND unread = 1";
				$result = mysql_query($query);
				return mysql_num_rows($result);
			}

			function getNotificationIcon($user_id) {
				$count = getNotificationCount($user_id);
				$note = '<img src=images/icons/inbox-';
				if ($count > 0) {
					$note .= 'full';
				} elseif ($count == 0) {
					$note .= 'empty';
				}
				$note .= '.png class="icon app-link" title="'.$count.' ';
				if ($count == 1) {
					$note .= 'Benachrichtigung';
				} else {
					$note .= 'Benachrichtigungen';
				}
				$note .= '">';
				return $note;
			}

			function writeTaskArray(){
				$tasks = array();
				$task_index = 0;
				$query = "SELECT task_id, done FROM user_has_task WHERE done = 0 AND user_id = '".$_SESSION['user_id']."'";
				$result = mysql_query($query);
				while($row = mysql_fetch_object($result)){
					$tasks[$task_index] = $row->task_id;
					$task_index++;
				}
				$query = "SELECT * FROM task WHERE (";
				$first = true;
				for ($i = 0;$i<count($tasks);$i++){
					if($first){
						$query .= "task_id = '$tasks[$i]'";
						$first = false;
					} else {
						$query .= " OR task_id = '$tasks[$i]'";
					}
				}
				return $query;
			}

			function getCountAll(){
				$query = writeTaskArray();
				if(strlen($query)>30){
					$query .= ") AND done = 0";
					$result = mysql_query($query);
					return mysql_num_rows($result);
				} else {
					return 0;
				}

			}
			
			function getCountPers() {
				$query = "SELECT task_id FROM user_has_task WHERE done = 0 AND user_id = " . $_SESSION['user_id'];
				$result = mysql_query($query);
				$temp_num = 0;
				while ($row = mysql_fetch_object($result)) {
					$subquery = "SELECT * FROM task WHERE class_id = 0 AND task_id = $row->task_id";
					$subresult = mysql_query($subquery);
					if (mysql_num_rows($subresult)>0) {
						$temp_num++;
					}
				}
				$num = $temp_num;
				return $num;
			}
			
			function getCountNoSubject() {
				$query = "SELECT task_id FROM user_has_task WHERE done = 0 AND user_id = " . $_SESSION['user_id'];
				$result = mysql_query($query);
				$temp_num = 0;
				while ($row = mysql_fetch_object($result)) {
					$subquery = "SELECT * FROM task WHERE subject_id = 0 AND task_id = $row->task_id";
					$subresult = mysql_query($subquery);
					if (mysql_num_rows($subresult)>0) {
						$temp_num++;
					}
				}
				$num = $temp_num;
				return $num;
			}

			function getCountDone(){
				$query = "SELECT task_id, done FROM user_has_task WHERE done = 1 AND user_id = ".$_SESSION['user_id'];
				$result = mysql_query($query);
				if(mysql_num_rows($result)!=0){
					$query = "SELECT * FROM task WHERE (";
					$first = true;
					while($row = mysql_fetch_object($result)){
						if($first){
							$query .= "task_id = ".$row->task_id;
							$first = false;
						} else {
							$query .= " OR task_id = ".$row->task_id;
						}
					}
					$query .= ")";
					$result = mysql_query($query);
					return mysql_num_rows($result);
				} else {
					return 0;
				}
			}

			function getCountToday(){
				$timestamp = time();
				$date = date("Y-m-d", $timestamp);
				$query = writeTaskArray();
				if(strlen($query)>30){
					$query .= ") AND until = '".$date."'  AND done = 0";
					$result = mysql_query($query);
					return mysql_num_rows($result);
				} else {
					return 0;
				}
			}

			function getCountTomorrow(){
				$timestamp = time();
				$year=date("Y",$timestamp);
				$month=date("m",$timestamp);
				$day=date("d",$timestamp)+1;
				$date = $year."-".$month."-".$day;
				$query = writeTaskArray();
				if(strlen($query)>30){
					$query .= ") AND until = '".$date."'  AND done = 0";
					$result = mysql_query($query);
					return mysql_num_rows($result);
				} else {
					return 0;
				}
			}

			function getCountNextSeven(){
				$timestamp = time();
				$year=date("Y", $timestamp);
				$month=date("m", $timestamp);
				$day=date("d", $timestamp);
				$date = date('Y-m-d',mktime(0,0,0,$month,$day+1,$year));
				$date2 = date('Y-m-d',mktime(0,0,0,$month,$day+8,$year));
				$query = writeTaskArray();
				if(strlen($query)>30){
					$query .= ") AND until between '".$date."' AND '".$date2."'  AND done = 0";
					$result = mysql_query($query);
					return mysql_num_rows($result);
				} else {
					return 0;
				}
			}

			function getCountLater(){
				$timestamp = time();
				$year=date("Y", $timestamp);
				$month=date("m", $timestamp);
				$day=date("d", $timestamp);
				$date = date('Y-m-d',mktime(0,0,0,$month,$day+8,$year));
				$query = writeTaskArray();
				if(strlen($query)>30){
					$query .= ") AND until > '".$date."'  AND done = 0";
					$result = mysql_query($query);
					return mysql_num_rows($result);
				} else {
					return 0;
				}
			}

			function getCountNodate(){
				$query = writeTaskArray();
				if(strlen($query)>30){
					$query .= ") AND until = '0000-00-00'  AND done = 0";
					$result = mysql_query($query);
					return mysql_num_rows($result);
				} else {
					return 0;
				}
			}	

			function getCommonSubject() {
				$query = "SELECT subject_id, COUNT( subject_id ) 
					FROM user_has_subject
					GROUP BY subject_id
				ORDER BY COUNT( subject_id ) DESC 
				LIMIT 10";
			$result = mysql_query($query);
			while ($row1 = mysql_fetch_object($result)) {
				$subquery = "SELECT name FROM subject WHERE subject_id = '$row1->subject_id'";
				$subresult = mysql_query($subquery);
				while ($row2 = mysql_fetch_object($subresult)) {
					$subject = $row2->name;
					$user_query = "SELECT user_id FROM user_has_subject WHERE user_id = ".$_SESSION[user_id]." AND subject_id = ".$row1->subject_id;
					$user_result = mysql_query($user_query);
					if(mysql_num_rows($user_result)!=0){
						echo "<li class='subject'><input type=checkbox id=".$row1->subject_id." class=subject-true  onclick=changeSubjectStatus(".$row1->subject_id.") checked><a href=tasks.php?s=sub&n=$subject>$subject</a></li>";
					} else {
						echo "<li class='subject'><input type=checkbox id=".$row1->subject_id." class=subject-false onclick=changeSubjectStatus(".$row1->subject_id.") ><a href=tasks.php?s=sub&n=$subject>$subject</a></li>";
					}
					
				
				}
			}
		}

		function updateMessage($content, $type = 'success') {
			$message = "<img src=images/icons/";
			if ($type == 'success') {
				$message .= "tick";
				$colour = "green";
				} elseif ($type == 'error') {
					$message .= "delete";
					$colour = "red";
				}
				$message .= ".png class=icon> <span class=$colour>$content</span>";
				return $message;
			}

			function updateSettings($type, $value) {
				$user_id = $_SESSION['user_id'];
				$query = "UPDATE user SET $type = '$value' WHERE user_id = '$user_id'";
				$result = mysql_query($query);

				if ($result) {
					return updateMessage('gespeichert');
				} else {
					return updateMessage('fehler beim speichern', 'error');
				}
			}

			function checkEmailDuplication($email) {
				$query = "SELECT email FROM user WHERE email = '$email' LIMIT 1";
				$result = mysql_query($query);
				if (mysql_num_rows($result)>0) {
					return false;
				} else {
					return true;
				}
			}
			
			function checkBetaMail($email) {
				$query = "SELECT email FROM beta_email WHERE email = '$email' LIMIT 1";
				$result = mysql_query($query);
				if (mysql_num_rows($result)>0) {
					return true;
				} else {
					return false;
				}
			}

			function lockEmail($email) {
				$query = "UPDATE user SET email_activation = '1'";
				$result = mysql_query($query);
			}

			function unlockEmail($email) {
				$query = "UPDATE user SET email_activation = '0'";
				$result = mysql_query($query);
			}

			function getGravatar( $email, $s = 80, $class = 'gravatar', $d = 'mm', $r = 'g', $img = true, $atts = array() ) {
				$url = 'http://www.gravatar.com/avatar/';
				$url .= md5( strtolower( trim( $email ) ) );
				$url .= "?s=$s&d=$d&r=$r";
				if ( $img ) {
					$url = '<img src="' . $url . '"';
					foreach ( $atts as $key => $val )
						$url .= ' ' . $key . '="' . $val . '"';
					$url .= " class=$class>";
				}
				return $url;
			}

			function getUserClasses($user_id) {
				$query = "SELECT class_id FROM class_has_user WHERE user_id = '$user_id'";
				$result = mysql_query($query);
				
				if (mysql_num_rows($result)>0) {
					
				while ($row1 = mysql_fetch_object($result)) {
					$class_id = $row1->class_id;
					$subquery = "SELECT name FROM class WHERE class_id = '$class_id'";
					$subresult = mysql_query($subquery);
					while ($row2 = mysql_fetch_object($subresult)) {
						$class = $row2->name;
						$classes[] = $class;
					}
				}
				$i = 0;
				foreach ($classes as $class) {
					if ($i > 0) {
						echo ', ';
					}
					echo "<a href=class.php?n=$class>$class</a>";
					$i++;
				}
			} else {
				if ($user_id == $_SESSION['user_id']) {
					echo 'Du bist noch in keiner Klasse, <a href=classes.php>hier</a> kannst du deine Klassen suchen oder erstellen falls sie noch nicht vorhanden ist.';
				} else {
					echo getUserPrename($user_id) . " ist noch in keiner Klasse";
				}
			}
			}

			function getUserSubjects($user_id, $type) {
				$query = "SELECT subject_id FROM user_has_subject WHERE user_id = '$user_id'";
				$result = mysql_query($query);

				if (mysql_num_rows($result)>0) {

					while ($row1 = mysql_fetch_object($result)) {
						$subject_id = $row1->subject_id;
						$subquery = "SELECT name FROM subject WHERE subject_id = '$subject_id'";
						$subresult = mysql_query($subquery);
						while ($row2 = mysql_fetch_object($subresult)) {
							$subject = $row2->name;
							$subjects[] = $subject;
						}
					}
					if ($type == 'profile') {
						$i = 0;
						foreach ($subjects as $subject) {
							if ($i > 0) {
								echo ', ';
							}
							echo "<a href=tasks.php?s=sub&n=$subject>$subject</a>";
							$i++;
						}
					} elseif ($type == 'subjects') {
						echo "<ul>";
						foreach ($subjects as $subject) {
							echo "<li class='subject'><input type=checkbox class=subject-true checked><a href=tasks.php?s=sub&n=$subject>$subject</a></li>";
						}
						echo "</ul>";
					}
				} else {
					if ($type == 'profile') {
						if ($user_id == $_SESSION['user_id']) {
							echo 'Du hast noch keine F&auml;cher ausgew&auml;hlt, <a href=subjects.php>hier</a> kannst du deine F&auml;cher suchen und hinzuf&uuml;gen.';
						} else {
							echo getUserPrename($user_id) . " hat noch keine F&auml;cher ausgew&auml;hlt.";
						}
					} elseif ($type == 'subjects') {
						echo 'Du hast noch keine F&auml;cher gew&auml;hlt.';
					}
				}
			}
			
			function checkUser($user_id) {
				$query = "SELECT user_id FROM user WHERE user_id = '$user_id'";
				$result = mysql_query($query);
				
				if (mysql_num_rows($result)>0) {
					return true;
				} else {
					return false;
				}
			}
			
			function truncate($string, $number=38, $sign="...") {
				if (strlen($string) > $number) {
					$string = substr($string, 0, $number);
					$string = "$string $sign";
				}
				return $string;
			}
			
			function taskString($id, $content, $subject, $until, $new = false) {
				if ($new) {
					$hide = " style='display: none;'";
				} else {
					$hide = "";
				}
				if ($subject != "Kein Fach") {
					$sortlink = "tasks.php?s=sub&n=$subject";
				} else {
					$sortlink = "tasks.php?s=nosubject";
				}
				$task = "<li id=li_$id class=task$hide><input type=checkbox id=$id onclick=changeTaskStatus($id)><div class=task-content><table><tr><td><a href=$sortlink>$subject</a></td><td>$content</td></tr></table></div> <span class=meta><span class=date id=date_$id>" . getTaskDate($until) . "</span> <span class=options><a href=# class=until><input type=hidden id=until_$id class=until_datepicker size=2></a> <a href=task.php?id=$id class=details><img class=icon src=images/icons/details.png></a> <a href=# class=delete><img class=icon src=images/icons/delete.png></a></span></span></li>";
				return $task;
			}
			
			function notification($string, $type='notice') {
				$message = "<img src=images/icons/";
				if ($type == 'notice') {
					$message .= "tick";
				} elseif ($type == 'error') {
					$message .= "error";
				}
				$message .= ".png class=icon> $string";
				return $message;
			}
			
			function parse_feed($usernames, $limit=5) {
				$usernames = str_replace("www.", "", $usernames);
				$usernames = str_replace("http://twitter.com/", "", $usernames);
			        $username_for_feed = str_replace(" ", "+OR+from%3A", $usernames);
				$feed = "http://api.twitter.com/1/statuses/user_timeline/".$username_for_feed.".atom?callback=?";
				$cache_rss = file_get_contents($feed);
				if (!$cache_rss) {
					// we didn't get anything back from twitter
					echo "<!-- ERROR: Twitter feed was blank! Using cache file. -->";
				}

				// clean up and output the twitter feed
			    $feed = str_replace("&amp;", "&", $cache_rss);
			    $feed = str_replace("&lt;", "<", $feed);
			    $feed = str_replace("&gt;", ">", $feed);
			    $clean = explode("<entry>", $feed);
			    $clean = str_replace("&quot;", "'", $clean);
			    $clean = str_replace("&apos;", "'", $clean);
				$clean = str_replace("$usernames: ", "", $clean);
			    $amount = count($clean) - 1;
				if($amount > $limit) $amount=$limit;
				$tweets = array();
			    if ($amount) { // are there any tweets?
			        for ($i = 1; $i <= $amount; $i++) {
			            $entry_close = explode("</entry>", $clean[$i]);
			            $clean_content_1 = explode("<content type=\"html\">", $entry_close[0]);
			            $clean_content = explode("</content>", $clean_content_1[1]);
			            $clean_name_2 = explode("<name>", $entry_close[0]);
			            $clean_name_1 = explode("(", $clean_name_2[1]);
			            $clean_name = explode(")</name>", $clean_name_1[1]);
			            $clean_user = explode(" (", $clean_name_2[1]);
			            $clean_lower_user = strtolower($clean_user[0]);
			            $clean_uri_1 = explode("<uri>", $entry_close[0]);
			            $clean_uri = explode("</uri>", $clean_uri_1[1]);
			            $clean_time_1 = explode("<published>", $entry_close[0]);
			            $clean_time = explode("</published>", $clean_time_1[1]);
			            $unix_time = strtotime($clean_time[0]);
			            $pretty_time = date("d.m.Y H:i:s",$unix_time);
						$tweets[] = $clean_content[0];
						#$tweets[] = array('content' => $clean_content[0], 'time' => $pretty_time);
			        }
			    }
				return $tweets;
			}
			?>
