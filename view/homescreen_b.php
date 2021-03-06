<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="../../favicon.ico">

        <title>Voting Machine</title>

        <!-- Bootstrap core CSS -->
        <link href="../bootstrap-3.3.1/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="voting.css" rel="stylesheet">

        <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
        <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
        <script src="../bootstrap-3.3.1/assets/js/ie-emulation-modes-warning.js">
        </script>
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <!-- <script src="./script/number_click.js"> </script> -->
        <script src="js/jquery-min.js"></script>
        <script language="javascript" type="text/javascript">
            var stateModule = (function () {
                var state = {}; // Private Variable
                    state["cursor_position"] = 0;
                    state["events_stack"] = new Array();
                    state["vote_input"] = "";
                    state["race_name"] = "Burger";
                    // Party Info: whether party exists, party name, party num, party pic, party alt text
                    state["party_info"] = [false, "Angry party", "94", "angry.jpg", "Angry Candidate"];
                    // Candidate Info: whether candidate exists, candidate name, candidate num, candidate pic
                    state["candidate_info"] = [false, "Mario", "94001", "candidate.jpg"];
                    state["vote_warning_flag"] = false;
                    state["candidate_info"] = [false, "Mario", "94700", "candidate.jpg"];
                var pub = {};// public object - returned at end of module
                pub.changeState = function (state_key, new_state_value) {
                    state[state_key] = new_state_value;
                };
                pub.getState = function(state_key) {
                    return state[state_key];
                }
                pub.getStates = function() {
                    return state;
                }
                return pub; // expose externally
            }());
            stateModule.changeState("cursor_position", "0");
            $(document).ready(function(){
                udpate_view();
                function number_press(num_str) {
                    var theState = stateModule.getStates();
                    if (parseInt(theState["cursor_position"]) >= 5) { // if 5 digits already typed
                        return;
                    }
				
                    $("#box".concat(theState["cursor_position"])).append(num_str);
                    stateModule.changeState("cursor_position", (parseInt(theState["cursor_position"])+1).toString());
                    theState["events_stack"].push("keypadnumber"+num_str);
                    console.log(theState);
                    udpate_data()
                    udpate_view();                    
                }
                $("#keypadnumber1").click(function(){
                    number_press("1");
                });
                $("#keypadnumber2").click(function(){
                    number_press("2");
                });
                $("#keypadnumber3").click(function(){
                    number_press("3");
                });
                $("#keypadnumber4").click(function(){
                    number_press("4");
                });
                $("#keypadnumber5").click(function(){
                    number_press("5");
                });
                $("#keypadnumber6").click(function(){
                    number_press("6");
                });
                $("#keypadnumber7").click(function(){
                    number_press("7");
                });
                $("#keypadnumber8").click(function(){
                    number_press("8");
                });
                $("#keypadnumber9").click(function(){
                    number_press("9");
                });
                $("#keypadnumber0").click(function(){
                    number_press("0");
                });
                $("#keypadUndo").click(function(){
                    location.reload();
                });
                $("#keypadConfirm").click(function(){
                    var theState = stateModule.getStates();
                    if (!theState["vote_warning_flag"]) {
                        theState["events_stack"].push("setVoteWarningFlag");
                        stateModule.changeState("vote_warning_flag", true);
                        stateModule.changeState("events_stack", theState["events_stack"]);
                        console.log(theState);
                        udpate_view();
                    } else {
            			var voted_candidate = false; //change it. Figure out whether they voted for candidate
                        var candidate_num = "";
            			var race = "";
                        var voter_input = "";
            			if (voted_candidate){
            			    candidate_num = "91005"; //change with the real one when submitting
            			}else {// I don't have option for voted_party, but this condition works for any input
            			    race = stateModule.getState("race_name");
                            var cursor_position = stateModule.getState("cursor_position");
                            voter_input = getVoterInput(cursor_position);
            			}
            			data = {"voted_candidate":voted_candidate, "candidate_num":candidate_num, "race":race, "voter_input":voter_input};
            			$.ajax({
                            type: "POST",
                            url: '../controller/save_vote.php',
                            data: data,
                            //dataType: 'JSON',
                            success: function(data)
                            {
                                $("#display").html("<h1>END</h1>" + "<p>Vote successfully Cast.</p>");
                            },
                            error: function()
                            {
                                $("#display").html("<h1>END</h1>" + "<p>Error in casting vote.</p>");
                            }
                        }); 


                    }
                    console.log(theState);
                    udpate_view();
                });
                function udpate_data() {
                    var cursor_position = parseInt(stateModule.getState("cursor_position"));
                    var race = stateModule.getState("race_name");
                    var voterInput = getVoterInput(cursor_position);
                    data = {"Race":race, "VoterInput":voterInput}; 
                    $.ajax({
                        type: "POST",
                        url: '../controller/query_candidates.php',
                        data: data,
                        dataType: 'JSON',
						async: false,
                        success: function(data)
                        {
                            parse_search_data(data);
							udpate_view();
                        },
                        error: function()
                        {
                            console.log("ERROR in udpate_data request");
                        }
                    }); 
                    cursor_position = parseInt(stateModule.getState("cursor_position"));
                    var voterInput = getVoterInput(cursor_position);
                    // updating party selected data
                    party_info_data = stateModule.getState("party_info");
					console.log(voterInput);
					console.log("Party Info: " +party_info_data);
                    party_info_data[0] = startsWith(voterInput, party_info_data[2]);
                    stateModule.changeState("party_info", party_info_data);
                    // updating candidate selected data
                    candidate_info_data = stateModule.getState("candidate_info");
                    candidate_info_data[0] = startsWith(voterInput, candidate_info_data[2]);
                    stateModule.changeState("candidate_info", candidate_info_data);
                }
                function udpate_view() {
                    cursor_position = parseInt(stateModule.getState("cursor_position"));
                    var voterInput = getVoterInput(cursor_position);
                    // Displaying Party Info
                    if (parseInt(stateModule.getState("cursor_position")) >= 2) { // if party selected
                        var party_info = stateModule.getState("party_info");
                        if (party_info[0]) { // party exists
                            var party_selected_str = "<div class='col-xs-2'>" + 
                                                        "<img src= "+ party_info[3] + " alt= '" + party_info[4] + "'style='width:75px;height:75px'>" +
                                                    "</div>" + 
                                                    "<div class='col-xs-4'>" +
                                                        "<h3> Party: " + party_info[1] + " </h3>" + 
                                                    "</div>" +
                                                    "<div class='col-xs-4'>" +
                                                        "<h3> Number: " + party_info[2] + "</h3>" +
                                                    "</div>";
                            $("#party_selected").html(party_selected_str);
                        } else { // party does not exist
                            var no_party_str = "<div class='alert alert-danger' role='alert'>" + 
                                                    "No PARTY with this number!" + 
                                                "</div>";
                            $("#party_selected").html(no_party_str);
                        }
                    } else { // if party not selected
                        $("#party_selected").html("");
                    }
                    // Displaying Candidates Info
                    if (parseInt(stateModule.getState("cursor_position")) == 5) { // if candidate selected
                        var candidate_info = stateModule.getState("candidate_info");
                        if (candidate_info[0]) { // candidate exists
                            var candidate_selected_str =    "<div class='col-xs-2'>" + 
                                                        "<img src= "+ candidate_info[3] + " style='width:75px;height:75px'>" +
                                                    "</div>" + 
                                                    "<div class='col-xs-4'>" +
                                                        "<h3> Candidate: " + candidate_info[1] + " </h3>" + 
                                                    "</div>" +
                                                    "<div class='col-xs-4'>" +
                                                        "<h3> Number: " + candidate_info[2] + "</h3>" +
                                                    "</div>";
                            $("#candidate_selected").html(candidate_selected_str);
                        } else { // candidate does not exist
                            var no_candidate_str = "<div class='alert alert-danger' role='alert'>" + 
                                                    "No CANDIDATE with this number!" + 
                                                "</div>";
                            $("#candidate_selected").html(no_candidate_str);
                        }
                    } else { // if candidate not selected
                        $("#candidate_selected").html("");
                    }
                    // Displaying Vote Warning
                    if (stateModule.getState("vote_warning_flag")){
                        var party_info = stateModule.getState("party_info");
                        var candidate_info = stateModule.getState("candidate_info");
                        if (cursor_position == "0") {
                            $("#vote_warning").html("<div class='alert alert-danger' role='alert'>" +"Current Selection: BLANK VOTE"+ 
                                                "</div>");
                        }
                        else if (party_info[0]) { // party exists
                            if (candidate_info[0]) { // candidate exists
                                $("#vote_warning").html("<div class='alert alert-danger' role='alert'>" +"Current Selection: PARTY AND CANDIDATE VOTE"+ 
                                                "</div>");
                            } else {
                                $("#vote_warning").html("<div class='alert alert-danger' role='alert'>" + "Current Selection: PARTY VOTE"+ 
                                                "</div>");
                            }
                        } else {
                            $("#vote_warning").html("<div class='alert alert-danger' role='alert'>" + 
                                                    "Current selection: NULL VOTE" + 
                                                "</div>");
                        }
                    } else {
                        $("#vote_warning").html("");
                    }
                    for (var i = 0; i < 9; i++) {
                        var candNum = $("#search_result_candNum"+i.toString()).text();
                        if (startsWith(candNum, voterInput)) {
                            $("#search_result"+i.toString()).removeClass("candidateHidden");
                            $("#search_result"+i.toString()).addClass("candidateVisible");
                        } else {
                            $("#search_result"+i.toString()).removeClass("candidateVisible");
                            $("#search_result"+i.toString()).addClass("candidateHidden");
                        }
                    }
                }

        		//returns rows from database selecting distinct parties.. if a party is duplicated in the input data, it 
        		//only chooses one of the rows
        		function get_party_data(data){
        		    var retrieved_party_nums = new Array(); //array keeping distinct party numbers retrieved from search

        		    var new_data = new Array();

        		    for (var i = 0; i<data.length; i++) {
                                var row = data[i];
                                var partyNum = row["PartyNumber"];
        			if ($.inArray(partyNum, retrieved_party_nums) == -1) { // if this party number not seen before
        			    retrieved_party_nums.push(partyNum);
        			    new_data.push(row);
        			}
        		    }
        		    return new_data;
        		}


				//taken from stackoverflow
				function shuffle(array) {
					var currentIndex = array.length, temporaryValue, randomIndex ;

					  // While there remain elements to shuffle...
					while (0 !== currentIndex) {

						// Pick a remaining element...
						randomIndex = Math.floor(Math.random() * currentIndex);
						currentIndex -= 1;

						// And swap it with the current element.
						temporaryValue = array[currentIndex];
						array[currentIndex] = array[randomIndex];
						array[randomIndex] = temporaryValue;
					}

					return array;
				}
				
				
                function parse_search_data(data){
                    console.log("Inside parse_search_data")
					var html_str = "";
                    var unique_party_info = [true, "partyName", "partyNum", "imgSrc", "alt_text"];
                    var unique_candidate_info = [true, "candName", "candNum", "imgSrc"];
                    if (data.length == 0) {
						html_str = "<p>No matching results to display.</p>";
                        unique_party_info[0] = false;
                        unique_candidate_info[0] = false;
                    } else {
                        console.log("Inside interesting part of parse_search_data")
                        unique_party_info[1] = data[0]["PartyName"];
                        unique_party_info[2] = data[0]["PartyNumber"];
                        unique_party_info[3] = data[0]["PartyImageSrc"];
                        unique_candidate_info[1] = data[0]["CandidateName"];
                        unique_candidate_info[2] = data[0]["CandidateNumber"];
                        unique_candidate_info[3] = data[0]["ImageSrc"];
                        // TODO randomize data...
					
						//get distinct parties from the input data
						if (parseInt(stateModule.getState("cursor_position")) < 2) { //party search
							data = get_party_data(data);
						}
						
						//create index array from 0 to data length-1 to shuffle
						var index_array = new Array();
						for (var i=0; i<data.length; i++){
							index_array.push(i);
						}
						
						if (data.length <= 9) {
							pi = shuffle(index_array);
							html_str = "";
							for (var i = 0; i<data.length; i++)
							{
								var row = data[pi[i]];
								//var row = data[i];
								var partyName = row["PartyName"];
								var partyNum = row["PartyNumber"];
								var partyImgSrc = row["PartyImageSrc"];
								var candName = row["CandidateName"];
								var candNum = row["CandidateNumber"];
								var imgSrc = row["ImageSrc"];
								var race = row["Race"];
								var imgHtml = "<img src='"+imgSrc+"'> </img>";
								if (parseInt(stateModule.getState("cursor_position")) < 2) { //party search 
									html_str += "<div class='col-xs-4 candidateVisible' style='width:245px;height:150px;' " + 
														"id='search_result"+i.toString()+"'> " +
														"<img src='" + partyImgSrc + "' style='width:70px;height:70px'>" + 
														"<h4> " + partyName + "</h4>" +
														"<h4 id='search_result_candNum"+i.toString()+"'>" + partyNum + "</h4>" +
													"</div>";
								} else { // candidate search
									html_str += "<div class='col-xs-4 candidateVisible' style='width:245px;height:150px'" + 
																"id='search_result"+i.toString()+"'> " +
																"<img src='" + imgSrc + "' style='width:70px;height:70px'>" + 
																"<h4> " + candName + "</h4>" +
																"<h4 id='search_result_candNum"+i.toString()+"'>" + candNum + "</h4>" +
															"</div>";
								}
								if (partyName != unique_party_info[1]) {
									unique_party_info[0] = false;
									unique_candidate_info[0] = false;
								} if (candName != unique_candidate_info[1]) {
									unique_candidate_info[0] = false;
								}
							
							}
										
						} else {
							html_str = "<p>Too many results to display</p>";
						}
						stateModule.changeState("party_info", unique_party_info);
						stateModule.changeState("candidate_info", unique_candidate_info); 									
					}
                    return html_str;
				}
				
                $("#keypadSearch").click(function(){
                    var theState = stateModule.getStates();
                    if (!theState["vote_warning_flag"]) {
                        theState["events_stack"].push("setVoteWarningFlag");
                        stateModule.changeState("vote_warning_flag", true);
                        stateModule.changeState("events_stack", theState["events_stack"]);
                        console.log(theState);
                        udpate_view();
                    } else {
            			var voted_candidate = false; //change it. Figure out whether they voted for candidate
                        var candidate_num = "";
            			var race = "";
                        var voter_input = "";
            			if (voted_candidate){
            			    candidate_num = "91005"; //change with the real one when submitting
            			}else {// I don't have option for voted_party, but this condition works for any input
            			    race = stateModule.getState("race_name");
                            var cursor_position = stateModule.getState("cursor_position");
                            voter_input = getVoterInput(cursor_position);
            			}
            			data = {"voted_candidate":voted_candidate, "candidate_num":candidate_num, "race":race, "voter_input":voter_input};
            			$.ajax({
                            type: "POST",
                            url: '../controller/save_vote.php',
                            data: data,
                            //dataType: 'JSON',
                            success: function(data)
                            {
                                $("#display").html("<h1>END</h1>" + "<p>Vote successfully Cast.</p>");
                            },
                            error: function()
                            {
                                $("#display").html("<h1>END</h1>" + "<p>Error in casting vote.</p>");
                            }
                        }); 


                    }
                    console.log(theState);
                    udpate_view();   
                });
				function startsWith(str, prefix) {
					return str.lastIndexOf(prefix, 0) === 0;
				}
				function getVoterInput(cursor_position) {
				
					var voterInput = "";
					for (i = 0; i < cursor_position; i++) {
						voterInput += $("#box".concat(i.toString())).text();
					}
					voterInput = voterInput.replace(/\D/g,'');  // trim away any non digits
					return voterInput;
				}
			
			});
        </script>
    </head>
    <body>
        <!-- Begin page content -->
        <div class="container" style="width:1100px; margin-top:30px">
            <div class="row" style="max-width:1100px;">
                <div class="col-xs-12" style="max-width:760px;">
		 
                    <div class="container" id="display" style="border:solid; width:750px; height:900px;">
                        <div class="page-header">
                            <h1 id="race_name">Race: Favourite Burger</h1>
                            <div class="row">
                                <div class="col-xs-2">
                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <p id="box0">  </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-2">
                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <p id="box1">  </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-2">
                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <p id="box2" >  </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-2">
                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <p id="box3">  </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-2">
                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <p id="box4">  </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="party_selected">
                                <!-- only display if single party option-->
                            </div>
                            <div class="row" id="candidate_selected">
                                <!-- only display if single candidate option-->
                            </div>
                            <div class="row">
                                <p id="vote_warning"></p>
                                <p class="text-muted">
                                    Press CONFIRM to cast vote <br> 
                                    Press BLANK to cast a blank vote <br>
				    Press CLEAR to restart
                                </p>
                            </div>
                            <div></div>
                        </div>

                        <div class="row" id="search_results" style="height: 600px">
                            <!--  only display if search button pressed, check State-->
                        </div>
                    </div>
                </div> 
           
            
            <div class="col-xs-4" style="width:320px;height:400px">
              
                <div class="container" style="border:solid;border-width:2px; width:300px;">
                <div class="row" style="height:75px;margin-top:20px;">
                    <div class="col-xs-3" style="width:320px; margin-left: 50px">  
                        <div class="btn-group-horizontal" role="group">
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber1">1</button>
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber2">2</button>
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber3">3</button>
                        </div>
                    </div>
                </div>
                <div class="row" style="height:75px">
                    <div class="col-xs-3" style="width:320px; margin-left: 50px">
                        <div class="btn-group-horizontal" role="group">
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber4">4</button>
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber5">5</button>
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber6">6</button>
                        </div>
                    </div>
                </div>
                <div class="row" style="height:75px">
                    <div class="col-xs-3" style="width:320px; margin-left: 50px">
                        <div class="btn-group-horizontal" role="group">
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber7">7</button>
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber8">8</button>
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber9">9</button>
                        </div>
                    </div>
                </div>
                <div class="row" style="height:75px">
                    <div class="col-xs-3" style="width:320px;margin-left: 50px">
                        <div class="btn-group-horizontal" role="group">
                            <button type="button" class="btn btn-default btn-lg" id="keypad*">*</button>
                            <button type="button" class="btn btn-default btn-lg" id="keypadnumber0">0</button>
                            <button type="button" class="btn btn-default btn-lg" id="keypad#">#</button>
                        </div>
                    </div>
                </div>
                <div class="row" style="height:75px">
                    <div class="col-xs-3" style="width:320px">
                        <div class="btn-group-horizontal" role="group">
                            <button type="button" class="btn btn-default btn-lg" style="background-color:blue; color:white" id="keypadSearch">Blank</button>
                            <button type="button" class="btn btn-default btn-lg" style="background-color:orange; color:black" id="keypadUndo">Clear</button>
                            <button type="button" class="btn btn-default btn-lg" style="background-color:green; color:white"  id="keypadConfirm">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="../bootstrap-3.3.1/assets/js/ie10-viewport-bug-workaround.js"></script>
    
    </body>

</html>
