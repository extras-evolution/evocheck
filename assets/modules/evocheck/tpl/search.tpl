<div class="container-fluid h100">
    <div id="search-form">
        <form action="[+baseurl+]" method="get" target="results" class="ecform" id="ec_searchform">
            <br/>
            <input type="hidden" name="a" value="[+action_id+]"/>
            <input type="hidden" name="id" value="[+module_id+]"/>
            <input type="hidden" name="ec_action" value="searchresults"/>
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-btn bs-dropdown-to-select-group">
                                <button type="button" class="btn btn-default dropdown-toggle as-is bs-dropdown-to-select" data-toggle="dropdown">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="setsearchterm" href="#">(base64_decode\h*\(|eval\h*\(|system\h*\(|shell_exec\h*\(|<\?php[^\n]{200,}|\$GLOBALS\[\$GLOBALS\[|;\h*\$GLOBALS|\$GLOBALS\h*;)</a></li>
                                    <li><a class="setsearchterm" href="#">(base64_decode\h*\(|eval\h*\(|system\h*\(|shell_exec\h*\(|<\?php[^\n]{200,}|\$GLOBALS\[\$GLOBALS\[|;\h*\$GLOBALS|\$GLOBALS\h*;|\${.?\\\x47\\\x4c\\\x4f\\\x42\\\x41\\\x4c\\\x53.?})</a></li>
                                    <li><a class="setsearchterm" href="#">((\d+)\h*\/\h*(\d+)|base64_decode\h*\(|eval\h*\(|system\h*\(|shell_exec\h*\(|<\?php[^\n]{200,}|\$GLOBALS\[\$GLOBALS\[|;\h*\$GLOBALS|\$GLOBALS\h*;)</a></li>
                                </ul>
                            </div>
                            <input type="text" class="form-control console" id="ec_term" name="ec_term" value="[+search_term+]" placeholder="[%search_term%] [%search_term_add%]"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-2">
                    <div class="form-group">
                        <label>[%summary_length%]</label>
                        <div class="row">
                            <div class="col-xs-6">
                                <input type="text" class="form-control console" name="ec_summary" value="[+summary_length+]"/>
                            </div>
                            <div class="col-xs-6">
                                <button id="ec_searchform_submit" type="submit" class="btn btn-primary">[%btn_search%]</button>
                            </div>
                            <div class="col-xs-12">
                                <small>[%zero_to_disable%]</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-3">
                    <div class="form-group">
                        <label>[%search_db%]</label><br/>
                        [+criteria_db+]
                    </div>
                </div>
                <div class="col-xs-3">
                    <label>[%search_files%]</label><br/>
                    [+criteria_f+]
                </div>
                <div class="col-xs-4">
                    <div class="form-group">
                        <label>[%changed_after%]</label>&nbsp;<a href="#" class="btn btn-xs btn-info" data-toggle="popover" data-content="[%changed_after_msg%]">!</a>
                        <div class="input-group datetimepicker">
                            <input type="text" name="ec_changed_after" class="form-control"/>
                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                        </div>
                        <small>Server time: [+server_time+]</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div id="results-wrap" class="h99">
        <iframe id="results" name="results" src="" class="embed-responsive-item h99 w100"></iframe>
    </div>
</div>
<script>
    // Allow shift to open search-results in pop-up
    var shiftKey = false;
    $("#ec_searchform_submit").click(function(e) {
        if (e.shiftKey) shiftKey = true;
        else shiftKey = false;
    });
    $("#ec_searchform").submit(function() {
        if (shiftKey) {
            targetFrame = Math.floor((Math.random() * 999999) + 1);
            $(this).attr('target', targetFrame);
            window.open('about:blank', targetFrame, "width=960,height=720,top=" + ((screen.height - 720) / 2) + ",left=" + ((screen.width - 960) / 2) + ",toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no")
        } else {
            $(this).attr('target', 'results');
        }
    });
    
    // Searchterm
    $('.setsearchterm').click(function(e) {
        $('#ec_term').val($(this).html());
    });    
    
    // Fix top height
    function setFrameSizes() {
        var top = $('#search-form').height();
        var remaining_height = parseInt($(window).height() - top);
        $('#results-wrap').height(remaining_height);
    }
    $(window).resize(function() {
        setFrameSizes();
    });
    setFrameSizes();
</script>