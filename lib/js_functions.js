var username_test;
var prename_test;
var lastname_test;
var email_test;
var pw_test;
var pw2_test;
var done;
var url;
var showmembersdone = 0;
var searchclass = 0;
var msg;

/* User Agent (Browserkennung) auf einen bestimmten Browsertyp prüfen */
 function checkBrowserName(name){
   var agent = navigator.userAgent.toLowerCase();
   if (agent.indexOf(name.toLowerCase())>-1) {
     return true;
   }
   return false;
 }

function showClassField(){
	$("#class").attr('disabled', false);
	document.getElementById('pers').onchange = hideClassField;
}

function hideClassField(){
	$("#class").attr('disabled', true);
	document.getElementById('pers').onchange = showClassField;
}

function showResults(data, highlight){
   var resultHtml = '<h2>Gefundene Klassen</h2><ul>';
	$.each(data, function(i,item){
		resultHtml+='<li class=result>';
		resultHtml+='<a href=class.php?n='+item.class+'>'+item.class+'</a>';
		resultHtml+='</li>';
	});

	$('div#results').html(resultHtml);
	}
	$('form').submit(function(e){
		e.preventDefault();
	});
	
function showResultsClasses(data, highlight){
   var resultHtml = '<h2>Gefundene F&auml;cher</h2><ul>';
	$.each(data, function(i,item){
		if(item.has == "yes"){
			resultHtml+='<li class="result"><input type=checkbox class=subject-true>';
		} else {
			resultHtml+='<li class="result"><input type=checkbox class=subject-false>';
		}
		resultHtml+=item.subject;
		resultHtml+='</li>';
	});

	$('div#results').html(resultHtml);
	}
	$('form').submit(function(e){
		e.preventDefault();
	});



function searchClass(name){
	if(searchclass == 0){
	$.ajax({
		type: "POST",
		url: "searchClass.php",
		data: "s="+name,
		success: function(msg){
			  document.getElementById('layout2-col-3').innerHTML += msg;
			  $('#search_result').fadeIn('slow', function() {
				// Animation complete.
			  });
			  searchclass = 1;
			}
		});
	} 
}

function showMembers(class_id){
	if(showmembersdone == 0){
		$.ajax({
		   type: "POST",
		   url: "showMembers.php",
		   data: "id="+class_id,
		   success: function(msg){
				  document.getElementById('layout2-col-1').innerHTML += msg;
				   $('#div_'+class_id).fadeIn('slow', function() {
					// Animation complete.
				  });
				  showmembersdone = 1;
			}
		   });
	} else {
		$('#div_'+class_id).fadeOut('slow', function() {
		    // Animation complete.
		  });
		showmembersdone = 0;
	}
}

function login(){
	var email = document.getElementById('email').value;
	var password = document.getElementById('password').value;
	$.ajax({
	   type: "POST",
	   url: "login.php",
	   data: "email="+email+"&password="+password,
	   success: function(msg){
	     if(msg=="Eingeloggt"){
			$('#login').fadeOut('slow', function() {
		    // Animation complete.
		  });
		  
		}
	   }
	 });
}
function bind_datepicker(){
$( ".until_datepicker").datepicker({
								showOn: "button", 
								buttonImage: "images/icons/clock.png", 
								buttonImageOnly: true,  
								dateFormat: "yy-mm-dd", 
								onSelect: function(date, instance) {   
									var id = $(this).attr('id')
									$.ajax({
										type: "POST",
										url: "changeUntil.php",
										data: "id="+id+"&date="+date,
										success: function(data){
											var array = date.split("-");
											var array2 = data.split(" ");
											data = array2[0];
											var newid = 'date_'+data;
											var newdate = array[2]+"."+array[1]+"."+array[0];
											var test = document.getElementById(newid).innerHTML;
											document.getElementById(newid).innerHTML = newdate;
										}
									});  
								} 
	});

}
function addTask(type){
	var subject = $('#subject').val();
	var clas = $('#class').val();
	var content = $('#content').val();
	var date = $('#date').val();
	// var pers = document.getElementById('pers').checked;
	
	if (content == "Beschreibung ..." || content == "") {
		alert('Die Aufgabe braucht eine Beschreibung');
		return false;
	}
	
	$.ajax({
	   type: "POST",
	   url: "addTask.php",
	   data: "subject="+subject+"&class="+clas+"&content="+content+"&date="+date,
	   success: function(msg){
	     $.ajax({
			type: "POST",
			url: "refreshTasks.php",
			data: "subject="+subject+"&class="+clas+"&id="+msg,
			success: function(msg_2){
				$('#tasklist').html(msg_2);
				$('#li_'+msg).slideDown('fast');
				$.ajax({
					type: "POST",
					url: "refreshTaskCounts.php",
					data: "type=all",
					dataType: 'json', 
					success: function(data){
						 $.each(data, function(i,item){
							$('#num_all').html('Alle ('+item.all+')');
							$('#num_pers').html('Pers&ouml;nlich ('+item.pers+')');
							$('#num_nosubject').html('Kein Fach ('+item.nosubject+')');
							$('#num_done').html('Erledigt ('+item.done+')');
							$('#num_today').html('Heute ('+item.today+')');
							$('#num_tomorrow').html('Morgen ('+item.tomorrow+')');
							$('#num_nextseven').html('N&auml;chsten 7 Tagen ('+item.nextseven+')');
							$('#num_later').html('Sp&auml;ter ('+item.later+')');
							$('#num_nodate').html('Ohne Datum ('+item.nodate+')');
						});
					}
				});
				$.ajax({
					type: "POST",
					url: "refreshTaskCounts.php",
					data: "type=subjects",
					dataType: 'json', 
					success: function(data){
						 $.each(data, function(i,item){
								var ind = i+1;
								var content = $('#num_sub_'+ind).html().split("(");
								content[0] += "(";
								content[0] += item;
								content[0] += ")";
								$('#num_sub_'+ind).html(content[0]);
						});
					}
				});
				$.ajax({
					type: "POST",
					url: "refreshTaskCounts.php",
					data: "type=classes",
					dataType: 'json', 
					success: function(data){
						 $.each(data, function(i,item){
								var ind = i;
								var content = $('#num_class_'+ind).html().split("(");
								content[0] += "(";
								content[0] += item;
								content[0] += ")";
								$('#num_class_'+ind).html(content[0]);
						});
					}
				});
				bind_datepicker();
				
				var numRand = Math.floor(Math.random()*1001)
				$('.notification').append("<li id="+numRand+"><img src=images/icons/tick.png class=icon> Aufgabe erstellt (<a href=tasks.php>Alle</a>)</li>");
				$('#'+numRand).fadeIn().delay(5000).slideUp();
				
				$("#content").val('Beschreibung ...');
			}
		});
	   }
	 });
}

function changeTaskStatus(task_id){
	if(document.getElementById(task_id).checked == true){
		done = 1;
	} else {
		done = 0;
	}
	$.ajax({           
	type: "POST",           
	url: "changeTaskStatus.php",           
	data: "id="+task_id+"&done="+done,          
	success: function(msg){                      
		if(msg==1){
		//document.getElementById("li_"+task_id).parentNode.removeChild(document.getElementById("li_"+task_id));
		$('#li_'+task_id).slideUp('fast');
				$.ajax({
					type: "POST",
					url: "refreshTaskCounts.php",
					data: "type=all",
					dataType: 'json', 
					success: function(data){
						 $.each(data, function(i,item){
							$('#num_all').html('Alle ('+item.all+')');
							$('#num_pers').html('Pers&ouml;nlich ('+item.pers+')');
							$('#num_nosubject').html('Kein Fach ('+item.nosubject+')');
							$('#num_done').html('Erledigt ('+item.done+')');
							$('#num_today').html('Heute ('+item.today+')');
							$('#num_tomorrow').html('Morgen ('+item.tomorrow+')');
							$('#num_nextseven').html('N&auml;chsten 7 Tagen ('+item.nextseven+')');
							$('#num_later').html('Sp&auml;ter ('+item.later+')');
							$('#num_nodate').html('Ohne Datum ('+item.nodate+')');
						});
					}
				});
				$.ajax({
					type: "POST",
					url: "refreshTaskCounts.php",
					data: "type=subjects",
					dataType: 'json', 
					success: function(data){
						 $.each(data, function(i,item){
								var ind = i+1;
								var content = $('#num_sub_'+ind).html().split("(");
								content[0] += "(";
								content[0] += item;
								content[0] += ")";
								$('#num_sub_'+ind).html(content[0]);
						});
					}
				});
				$.ajax({
					type: "POST",
					url: "refreshTaskCounts.php",
					data: "type=classes",
					dataType: 'json', 
					success: function(data){
						 $.each(data, function(i,item){
								var ind = i;
								var content = $('#num_class_'+ind).html().split("(");
								content[0] += "(";
								content[0] += item;
								content[0] += ")";
								$('#num_class_'+ind).html(content[0]);
						});
					}
				});
		
			
		}
	}     
	}      
	); 
	
}

function changeSubjectStatus(subject_id){
	if(document.getElementById(subject_id).checked == true){
		done = 1;
	} else {
		done = 0;
	}
	$.ajax({           
	type: "POST",           
	url: "changeSubjectStatus.php",           
	data: "id="+subject_id+"&done="+done,          
	success: function(msg){                      
		if(msg==1){
		//document.getElementById("li_"+task_id).parentNode.removeChild(document.getElementById("li_"+task_id));
		$('#li_'+subject_id).fadeOut('slow');
				
		}
	}     
	}      
	); 
	
}



function checkUsername(){
	username_test = true;
	var error = 0;
	var errorMsg = "";
	var accepted = 'abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVQXYZÄÖÜ1234567890_';
	var username = document.getElementById("username").value;
	var email = document.getElementById("email").value;
	if(username.length<4){
		error = 1;
		errorMsg += "Der Benutzername muss mindestens 5 Zeichen enthalten.\n";
	}
	for (var i = 0; i < username.length; i++){
		var letter = username.charAt(i); 
		if (accepted.indexOf(letter) == -1){ 
			error = 1;
			
			errorMsg += "Der Benutzername darf nur Gross-, Kleinbuchstaben, Zahlen oder \"_\" enthalten.\n";
		} 
	}
	
	if(error!=1){
		$.ajax({           
		type: "GET",           
		url: "checkUsernameEmail.php",           
		data: "mode=name&name=" + username,          
		success: function(msg){                      
			if(msg==1){
				
				error = 1;
				errorMsg + "Der Benutzername ist bereits vergeben.\n";
				username_test = false;
				
			}
			else {
				username_test = true;	
			}
		}     
			
		     
		}      
		); 
	}

	
	if(error==1){
		document.getElementById("username").focus();
		document.getElementById("username").style.background = 'red';
		document.getElementById("error").innerHTML = errorMsg;
		username_test = false;
	} else {
	document.getElementById("username").style.background = 'white';
	username_test = true;

	//checkAll();
	}
}

function checkPrename(){
	prename_test = true;
	var error = 0;
	var errorMsg = "";
	var accepted = 'abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVQXYZÄÖÜ';
	var prename = document.getElementById("prename").value;
	if(prename==""){
		error = 1;
		errorMsg += "Bitte geben Sie Ihren Vorname aus.\n";
	}
	for (var i = 0; i < prename.length; i++){
		var letter = prename.charAt(i); 
		if (accepted.indexOf(letter) == -1){ 
			error = 1;
			errorMsg += "Der Vorname darf nur Gross- und Kleinbuchstaben enthalten.\n";
		}
	}
	if(error==1){
		document.getElementById("prename").focus();
		document.getElementById("prename").style.background = 'red';
		document.getElementById("error").innerHTML = errorMsg;
		prename_test = false;
		return false;
	} else {
	error = 0;
	document.getElementById("prename").style.background = 'white';
	prename_test = true;
	
	//checkAll();
	}
}

function checkLastname(){
	lastname_test = true;
	var error = 0;
	var errorMsg = "";
	var accepted = 'abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVQXYZÄÖÜ';
	var lastname = document.getElementById("lastname").value;
	if(lastname==""){
		error = 1;
		errorMsg += "Bitte geben Sie Ihren Nachnamen ein.\n";
	}
	for (var i = 0; i < lastname.length; i++){
		var letter = lastname.charAt(i); 
		if (accepted.indexOf(letter) == -1){ 
			error = 1;
			errorMsg += "Der Nachname darf nur Gross- und Kleinbuchstaben enthalten.\n";
		}
	}
	if(error==1){
		document.getElementById("lastname").focus();
		document.getElementById("lastname").style.background = 'red';
		document.getElementById("error").innerHTML = errorMsg;
		lastname_test = false;
		return false;
	} else {
	error = 0;
	
	document.getElementById("lastname").style.background = 'white';
	lastname_test = true;
	
	//checkAll();
	}
}		

function checkEmail(){
	email_test = true;
	var error = 0;
	var errorMsg = "";
	var email = document.getElementById("email").value;
	var accepted = "^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$";
	var regex = new RegExp(accepted);
	if(!regex.test(email)){
		error = 1;
		errorMsg += "Bitte geben Sie eine gültige Email-Adresse ein.\n";
	}
	$.ajax({           
		type: "GET",           
		url: "checkUsernameEmail.php",           
		data: "mode=email&email=" + email,          
		success: function(msg){                      
			if(msg==1){
				error = 1;
				errorMsg + "Die Email-Adresse ist bereits vergeben.\n";
				email_test = false;
				return false;
			} else {
				email_test = true;
			}
		}     
		});
	
	
	
	if(error==1){
		document.getElementById("email").focus();
		document.getElementById("email").style.background = 'red';
		document.getElementById("error").innerHTML = errorMsg;
		email_test = false;
		return false;
	} else {
	error = 0;
	document.getElementById("email").style.background = 'white';
	email_test = true;
	
	//checkAll();
	
	}
}

function checkPw(){
	pw_test = true;
	var error = 0;
	var errorMsg = "";
	var pw = document.getElementById("password").value;
	if(pw.length<5){
		error = 1;
		errorMsg += "Das Passwort muss mindestens 5 Zeichen lang sein.\n";
	}
	if(error==1){
		document.getElementById("password").focus();
		document.getElementById("password").style.background = 'red';
		document.getElementById("error").innerHTML = errorMsg;
		pw_test = false;
		return false;
	} else {
	error = 0;
	document.getElementById("password").style.background = 'white';
	pw_test = true;
	
	//checkAll();
	
	}
}

function checkPw2(){
	pw2_test = true;
	var error = 0;
	var errorMsg = "";
	var pw = document.getElementById("password").value;
	var pw2 = document.getElementById("password2").value;
	if(pw!=pw2){
		error = 1;
		errorMsg += "Die Passwörter stimmen nicht überein.\n";
	}
	if(pw.length<5) {
		error = 1;
		errorMsg += "Das Passwort ist zu kurz.\n";
	}
	if(error==1){
		document.getElementById("password").focus();
		document.getElementById("password").style.background = 'red';
		document.getElementById("password2").style.background = 'red';
		document.getElementById("error").innerHTML = errorMsg;
		pw2_test = false;
		return false;
	} else {
	error = 0;
	document.getElementById("password").style.background = 'white';
	document.getElementById("password2").style.background = 'white';
	pw2_test = true;
	
	//checkAll();
	
	}
	
}

function checkAll(){
	//alert (document.getElementById("submit").disabled);
    //alert (username_test.toString() + prename_test + lastname_test + email_test + pw_test + pw2_test);
	if(username_test && prename_test && lastname_test && email_test && pw_test && pw2_test){
		
		document.getElementById("submit").disabled = false;
	//	alert(document.getElementById("submit").outerHTML + "richtig");
	} else { 
		
		document.getElementById("submit").disabled = true;
	//	alert(document.getElementById("submit").disabled);
		
	}
}