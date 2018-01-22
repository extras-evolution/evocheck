<div class="container-fluid">
    <div class="row">
        <div class="col-sm-6">
            <h3>[%indexhtm_directories_containing%]</h3>
            <p>[%indexhtm_introtext%]</p>
            <br/>
            <table class="table table-bordered table-hover">
                <tr><th colspan="2">[%scan_results%]</th></tr>
                <tr><td>[%indexhtm_files_found%]</td><td><b>[+totalFound+]</b></td></tr>
                <tr><td>[%indexhtm_files_missing%]</td><td><b>[+totalMissing+]</b></td></tr>
                <tr><td colspan="2">
                        <b>[%excluded_directories%]</b><br/>
                        [+excluded_directories+]
                </td></tr>
            </table>
            <br/>
            <a href="[+baseurl+]&ec_action=indexhtm" class="btn btn-primary">[%btn_update%]</a>
        </div>
        <div class="col-sm-6">
            <h3>[%indexhtm_title%]</h3>
            [+create_results+]
            <form action="[+baseurl+]" method="get" class="ecform" onsubmit="return confirm('[%are_you_sure%]');">
                <br/>
                <input type="hidden" name="a" value="[+action_id+]"/>
                <input type="hidden" name="id" value="[+module_id+]"/>
                <input type="hidden" name="ec_action" value="indexhtm"/>
                <input type="hidden" name="ec_create" value="1"/>
                <label>[%indexhtm_content%]</label><br>
                <textarea id="indexhtm_content" name="ec_content" class="form-control">[%indexhtm_default%]</textarea>
                <span class="pull-right"><span id="count"></span> [%bytes%]</span>
                <br/>
                <br/>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>[%indexhtm_create_options%]</label><br>
                            <div class="radio">
                                <label><input name="ec_option" value="add" checked="checked" type="radio"> [%indexhtm_option_add%]</label>
                                <p class="help-block">[%indexhtm_option_add_msg%]</p>
                            </div>
                            <div class="radio">
                                <label><input name="ec_option" value="overwrite" type="radio"> [%indexhtm_coption_overwrite%]</label>
                                <p class="help-block">[%indexhtm_coption_overwrite_msg%]</p>
                            </div>
                            <div class="radio">
                                <label><input name="ec_option" value="remove" type="radio"> [%indexhtm_option_remove%]</label>
                                <p class="help-block">[%indexhtm_option_remove_msg%]</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label>Filter by File-Size</label>
                        <div class="checkbox">
                            <label><input name="ec_filter" value="1" type="checkbox" checked="checked"> [%indexhtm_option_filter_size%]</label>
                        </div>
                        <div class="form-group">
                            <div class="input-group col-sm-6 col-xs-12">
                                <input type="text" class="form-control" name="ec_size" value="200" placeholder="File-Size" />
                                <div class="input-group-addon">Bytes</div>
                            </div>
                            <p class="help-block">[%indexhtm_filter_size_msg%]</p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">[%btn_create_indexhtm%]</button>
            </form>
        </div>
    </div>
</div>

<script>
    var content = $("#indexhtm_content");
    var count = $("#count"); 
    content.keyup(function(){
        count.text($(this).val().length);
    });
    count.text(content.val().length);
</script>