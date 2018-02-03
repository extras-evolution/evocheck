    <div class="row">
        <div class="col-sm-6">
            <table class="table table-bordered">
                <tr><td>[%integrity_compare_files_integer%]</td><td>[+integer+]</td></tr>
                <tr><td>[%integrity_compare_files_changed%]</td><td>[+changed+]</td></tr>
                <tr><td>[%integrity_compare_files_notfound%]</td><td>[+notfound+]</td></tr>
                <tr><td>[%integrity_compare_files_new%]</td><td>[+new+]</td></tr>
                <tr><td>[%integrity_compare_files_notreadable%]</td><td>[+notreadable+]</td></tr>
            </table>
        </div>
        <div class="col-sm-6">
            <b>[%integrity_compare_dirs_excluded%]</b>
            <ul>
                [+dirs_excluded+]
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="tablist">
                <li class="active"><a href="#changed" data-toggle="tab">[%integrity_compare_files_changed%]</a></li>
                <li><a href="#notfound" data-toggle="tab">[%integrity_compare_files_notfound%]</a></li>
                <li><a href="#new" data-toggle="tab">[%integrity_compare_files_new%]</a></li>
                <li><a href="#notreadable" data-toggle="tab">[%integrity_compare_files_notreadable%]</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade in active" id="changed"><pre>[+files_changed+]</pre></div>
                <div class="tab-pane fade" id="notfound"><pre>[+files_notfound+]</pre></div>
                <div class="tab-pane fade" id="new"><pre>[+files_new+]</pre></div>
                <div class="tab-pane fade" id="notreadable"><pre>[+files_notreadable+]</pre></div>
            </div>
        </div>
    </div>
    
    <script>
        $('#tablist a').click(function (e) {
            e.preventDefault()
            $(this).tab('show')
        })
    </script>
    
    
    
    
    
    
    
    
    
    