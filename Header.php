<?php

session_start();

class Header {

    /**
     * The title of the page
     * @var string
     */
    private $title;

    /**
     * The database variable used for queries
     * @var Database
     */
    private $db;

    /**
     * Construct a new Header object
     * @param string $title The page's title
     */
    function __construct($title="") {
        $this->title = $title;
        $this->db = Database::getInstance();
    }

    /**
     * Draw the header
     * @param string $title The page's title
     */
    function draw($title="") {
        $title = (empty($title)) ? $this->title : $title;
        $baseUrl = "http://" . rtrim(HTTP_ROOT, '/');

    ?>

<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $title; ?></title>

         <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
          <script src="<?php echo $baseUrl; ?>/includes/jquery.cookie.js"></script>
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/includes/chosen/chosen.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/includes/ladda/dist/ladda.min.css" />
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/style.css">
     <script>
            button_click = function(){
      cookie_save($( ".themes option:selected" ).text());
  }
  cookie_save = function(val){
      console.log(val);
      if ($.cookie('theme') == null){
        console.log("keine cookie");
        $.cookie('theme', val, {expires: 10, path:'/'});
      }else{
        $.removeCookie('theme')
        $.cookie('theme', val, {expires: 10, path: '/'});
        console.log("cookie ist");
      }
  }
      </script>
    </head>
    <script>
           on_load_cookie = function(){
  if ($.cookie('theme') !== null){
              //console.log( $.cookie("theme") );
              var theme_name = $.cookie("theme");
              var nstr = $.trim(theme_name)
              console.log(nstr);
             if (nstr == "Colorful"){
                var css_append_str = "<link rel='stylesheet' href='css/themes/colorful.css'>";
                $("head").append(css_append_str);
              }
            }else{
              console.log("cookie nicht! NEEIINNNN");
           }
    }
  </script>
    <body onload="on_load_cookie()">
    <div class="navbar">
    <div class="navmenu">
        <a href="<?php echo $baseUrl; ?>/" class="navbuttonicon left"><i class="icon-home"></i></a>
        <?php if (isset($_SESSION['username'])) {

        if (Group::hasNewMessage($_SESSION['bzid'])) {
            $new = "new_message";
        }
        ?>
        <a href="<?php echo $baseUrl; ?>/messages" class="navbuttonicon left <?php echo $new; ?>"><i class="icon-comments"></i></a>
        <?php } ?>
        <a href="<?php echo $baseUrl; ?>/news" class="navbuttonicon left"><i class="icon-pushpin"></i></a>
        <a href="<?php echo $baseUrl; ?>/teams" class="navbutton left">Teams</a>
        <a href="<?php echo $baseUrl; ?>/players" class="navbutton left">Players</a>
        <a href="<?php echo $baseUrl; ?>/matches" class="navbutton left">Matches</a>
        <?php

        $pages = Page::getPages();

        foreach ($pages as $key => $id) {
            $page = new Page($id);
            if (!$page->isHomePage())
                echo "<a href='" . $page->getURL() . "' class='navbutton left'>" . $page->getName() . "</a> ";
        }

        ?>
        <a href="<?php echo $baseUrl; ?>/bans" class="navbutton left">Bans</a>
        <a href="<?php echo $baseUrl; ?>/servers" class="navbutton left">Servers</a>
        <a href="<?php echo $baseUrl; ?>/themes" class="navbutton left"> Themes </a>
     <?php if (isset($_SESSION['username'])) { ?>
        <a href="<?php echo $baseUrl; ?>/logout.php" class="navbuttonicon right"><i class="icon-signout"></i></a>
        <a href="<?php echo $baseUrl; ?>/profile" class="navbuttonicon right"><i class="icon-user"></i></a>
        <a href="<?php echo $baseUrl; ?>/notifications" class="navbuttonicon right"><i class="icon-bell-alt"></i></a>
        <?php
    } else {
        $url = "http://my.bzflag.org/weblogin.php?action=weblogin&amp;url=";
        $url .= urlencode("http://" . rtrim(HTTP_ROOT, '/') . "/login.php?token=%TOKEN%&username=%USERNAME%");
        ?>
        <a href="<?php echo $url; ?>" class="navbuttonicon right"><i class="icon-signin"></i></a>
    <?php } ?>
    </div> <!-- end .navmenu -->

    </div> <!-- end .navbar -->

    <div class="notification">
        <i class="icon-ok"></i><span>Your message was sent successfully</span>
    </div>

    <div class="content">

    <?php

    }

    /**
     * Redirect the page using PHP's header() function
     * @param string $location The page to redirect to
     */
    public static function go($location = "/", $override = false) {
        $url = "http://" . rtrim(HTTP_ROOT, '/');
        if ($override) {
            header("Location: $location");
        } else if (strtolower($location) == "default" || strtolower($location) == "home" || strtolower($location) == "index.php" || strtolower($location) == "/") {
            header("Location: $url");
        } else {
            header("Location: $url" . $location);
        }

        die();
    }
}