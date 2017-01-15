<nav class="navbar navbar-ec navbar-fixed-top">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="[+baseurl+]">[+brand+] <small>v[+version+]</small></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li><a href="[+baseurl+]&ec_action=search">[%navbar_search%]</a></li>
                <!-- <li><a href="[+baseurl+]&ec_action=adminer">Adminer</a></li> -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">[%navbar_server%] <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <!-- <li><a href="[+baseurl+]&ec_action=crc">[%navbar_check_crc%]</a></li> -->
                        <li><a href="[+baseurl+]&ec_action=indexhtm">[%navbar_check_indexhtm%]</a></li>
                    </ul>
                </li>
            </ul>
            
            <ul class="nav navbar-nav navbar-right" [+display_standalone_logout_btn+]>
                <li><a href="[+baseurl+]&ec_logout">[%navbar_logout%]</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
<div class="fspacer"></div>