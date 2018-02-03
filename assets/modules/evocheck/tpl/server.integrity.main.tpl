<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <h3>[%integrity_title%]</h3>
            <p>[%integrity_introtext%]</p>
            <p>[%integrity_images_directory%]: <b>[+images_directory+]</b></p>
            <hr/>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <h3>[%integrity_compare%]</h3>
            <p>[%integrity_compare_intro%]</p>
            <form action="[+baseurl+]" method="get" class="ecform" onsubmit="return checkImageSelected(this);">

                <input type="hidden" name="a" value="[+action_id+]"/>
                <input type="hidden" name="id" value="[+module_id+]"/>
                <input type="hidden" name="ec_action" value="integrity"/>
                <input type="hidden" name="ec_subaction" value="compare"/>
                
                [+images_list+]

                <button type="submit" class="btn btn-primary">[%btn_compare%]</button>
            </form>
        </div>
        
        <div class="col-sm-6">
            <h3>[%integrity_create%]</h3>
            <p>[%integrity_create_intro%]</p>
            <form action="[+baseurl+]" method="get" class="ecform">
                
                <input type="hidden" name="a" value="[+action_id+]"/>
                <input type="hidden" name="id" value="[+module_id+]"/>
                <input type="hidden" name="ec_action" value="integrity"/>
                <input type="hidden" name="ec_subaction" value="create"/>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>[%integrity_create_filename%]</label><br>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="ec_imagename" placeholder="Image-name" value="[+image_name+]" />
                                    <div class="input-group-addon">.json</div>
                                </div>
                            </div>

                            <label>[%integrity_excluded_directories%]</label>
                            <div class="input_fields_wrap">
                                [+inputs_excluded+]
                            </div>
                            
                            <!--@-
                            <div class="radio">
                                <label><input name="ec_option" value="add" checked="checked" type="radio"> [%integrity_option_add%]</label>
                                <p class="help-block">[%integrity_option_add_msg%]</p>
                            </div>
                            -@-->
                        </div>
                    </div>
                    
                </div>

                <button type="submit" class="btn btn-primary">[%btn_create_integrity%]</button>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var wrapper         = $(".input_fields_wrap");
        
        $(wrapper).on("click",".add_field", function(e) {
            e.preventDefault();
            $(wrapper).append(
                '<div class="input-group">\n' +
                    '<input type="text" name="excluded[]" class="form-control" />\n' +
                    '<span class="input-group-btn"><button class="btn btn-primary add_field">+</button></span>\n' +
                    '<span class="input-group-btn"><button class="btn btn-primary remove_field">-</button></span>\n' +
                '</div>\n'
            );
        });

        $(wrapper).on("click",".remove_field", function(e) {
            e.preventDefault();
            $(this).parent().parent('.input-group').remove();
        });
    });
    
    function checkImageSelected(form) {
        if (form.ec_imagecompare.value == '')
        {
            alert ('[+alert_choose_image+]');
            return false;
        } else {
            return true;
        }
    }
</script>