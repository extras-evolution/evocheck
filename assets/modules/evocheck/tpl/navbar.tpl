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
            </ul>
            <ul class="nav navbar-nav navbar-right" [+display_standalone_logout_btn+]>
                <li><a href="[+baseurl+]&ec_logout">[%navbar_logout%]</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>