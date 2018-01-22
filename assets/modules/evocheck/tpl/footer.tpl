<script>
    var translations = [+json_lang+];

    $(".datetimepicker").datetimepicker({format: "DD-MM-YYYY HH:MM:SS"});
    
    jQuery(".popup").click(function(e) {
        e.preventDefault();
        var randomNum = "gener1";
        if (e.shiftKey) {
            randomNum = Math.floor((Math.random()*999999)+1);
        }
        window.open($(this).attr("href"),randomNum,"width=960,height=720,top="+((screen.height-720)/2)+",left="+((screen.width-960)/2)+",toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no")
    });
    
    $(document).ready(function(){
        // Init Popovers
        $('[data-toggle="popover"]').popover();
        
        // Prepare AJAX-Buttons
        $('.ajax').click(function(e) {
            e.preventDefault();
            
            var type, id;
            var action = $(this).data('action');
            
            switch(action) {
                case 'delete': 
                    break;
                default:
                    action = false;
            }
            
            if(action && confirm(translations['confirm_'+action])) {
                var data = {
                    ec_action: action,
                    ec_type: $(this).data('type'),
                    ec_id: $(this).data('id')
                };
                $.post($(this).attr('href'), data)
                    .done(function (data, status) {
                        if (status == 'success') {
                            var result = JSON.parse(data);
                            
                            // Show optional alerts
                            if(result.alert) alert(result.alert);
                            
                            // Fade out single results if requested
                            if (result.success && result.removeResultID) {
                                $('#' + result.removeResultID).fadeOut();
                            }
                        } else {
                            alert("Status: " + status + "\n" + "Error: " + data);
                        }
                    });
            }
        });
    });
    
</script>
</body>
</html>