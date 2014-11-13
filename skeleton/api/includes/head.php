<meta charset="utf-8">
<title><?php echo $title ?></title>
<meta name="description" content="Drunken Parrot UI Kit is a Twitter Bootstrap Framework design and Theme." />
<meta name="viewport" content="width=1000, initial-scale=1.0, maximum-scale=1.0">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
<link rel="apple-touch-icon-precomposed" href="/assets/images/icon.png" />
<link rel="shortcut icon" type="image/x-icon" href="/assets/images/favicon.ico" />
<link href="<?php echo $relativeIndexPath; ?>assets/css/bootstrap.css" rel="stylesheet">
<link href="<?php echo $relativeIndexPath; ?>assets/css/font-awesome.min.css" rel="stylesheet">
<link href="<?php echo $relativeIndexPath; ?>assets/css/carousel.css" rel="stylesheet">
<link href="<?php echo $relativeIndexPath; ?>assets/css/drunken-parrot.css" rel="stylesheet">
<link href="<?php echo $relativeIndexPath; ?>assets/css/hoarrd/all.css" rel="stylesheet">
<link href="<?php echo $relativeIndexPath; ?>assets/css/hoarrd/style.css" rel="stylesheet">
<!--<link href='http://fonts.googleapis.com/css?family=Righteous' rel='stylesheet' type='text/css'>-->
<link href="<?php echo $relativeIndexPath; ?>assets/css/google-fonts-righteous.css" rel="stylesheet">
<!-- <link rel="shortcut icon" href="images/favicon.ico"> -->
<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
<script src="<?php $relativeIndexAPIPath; ?>assets/js/html5shiv.js"></script>
<![endif]-->
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>-->
<script src="<?php echo $relativeIndexPath; ?>assets/js/jquery-2.1.1.min.js"></script>
<script src="<?php echo $relativeIndexPath; ?>assets/js/bootstrap.min.js"></script>
<script src="<?php echo $relativeIndexPath; ?>assets/js/checkbox.js"></script>
<script src="<?php echo $relativeIndexPath; ?>assets/js/radio.js"></script>
<script src="<?php echo $relativeIndexPath; ?>assets/js/bootstrap-switch.js"></script>
<script src="<?php echo $relativeIndexPath; ?>assets/js/toolbar.js"></script>
<script src="<?php echo $relativeIndexPath; ?>assets/js/application.js"></script>
<script src="<?php echo $relativeIndexPath; ?>assets/js/hoarrd/jquery.main.js"></script>



<style>
  html,#header,footer {
    min-width: 990px;
  }

  @font-face {
    font-family: 'UtsukushiMincho'; /* お好きな名前にしましょう */
    src: url('<?php echo $relativeIndexPath; ?>assets/fonts/UtsukushiMincho-FONT/02UtsukushiMincho.eot');
    /* IE9以上用 */
    src:
    url('<?php echo $relativeIndexPath; ?>assets/fonts/UtsukushiMincho-FONT/02UtsukushiMincho.eot?#iefix')
    format('embedded-opentype'), /* IE8以前用 */



    url('<?php echo $relativeIndexPath; ?>assets/fonts/UtsukushiMincho-FONT/02UtsukushiMincho.woff')
    format('woff'), /* モダンブラウザ用 */



    url('<?php echo $relativeIndexPath; ?>assets/fonts/UtsukushiMincho-FONT/02UtsukushiMincho.ttf')
    format('truetype'); /* iOS, Android用 */
    font-weight: normal; /* 念の為指定しておきます */
    font-style: normal;
  }

  #wrapper {
    padding: 74px 0 0;
  }

  #header {
    margin: 0;
  }

  .navbar {
    border-radius: 0;
  }

  body {
    padding: 0;
    background: #5e696d;
  }

  body,h1,h2,h3,h4,h5,h6,.h1,.h2,.h3,.h4,.h5,.h6,.btn {
    font-family: 'UtsukushiMincho';
  }

  .btn {
    font-weight: bold;
  }

  .logo {
    font-family: 'Righteous', cursive;
  }

  .footer-holder p {
    color: #5e696d;
  }

  #search-form {
    float: right;
  }

  #search-input {
    width: 100px;
    border-radius: 20px;
    color: #b4bdc4;
    border: none;
  }

  #search-input:focus {
    outline: none
  }

  #search-icon {
    color: #fff;
    padding: 10px;
  }

  #remove-icon {
    left: -10px;
    margin-top: -28px;
    color: #b4bdc4;
    font-size: 16px;
    display: none;
    float: right;
    cursor: pointer;
  }

  .business-header {
    height: 400px;
    background: url('<?php echo $relativeIndexPath; ?>assets/images/bg02.png') center -100px no-repeat
    scroll;
    -webkit-background-size: cover;
    -moz-background-size: cover;
    background-size: cover;
    -o-background-size: cover;
    border-bottom: #d6e1e5 solid 1px;
  }

  .featurette-heading {
    font-size: 36px;
  }

  .lead {
    font-size: 18px;
  }

  ul.features {
    margin-top: 20px;
  }

  ul.features li {
    list-style: initial;
  }

  #side-nav h2 {
    font-size: 20px;
    color: #f3bc65;
  }

  #side-nav ul li {
    display: block;
  }

  #side-nav ul li a {
    border: 2px solid #5e696d;
    font-size: 16px;
    padding: 3px;
  }
  #article h1 {
    font-size: 24px;
  }
  #article h2 {
    font-size: 20px;
    border-bottom: 1px solid #eee;
  }
  #article dl dt {
    float: none;
    padding-top: 10px;
  }
  #article a {
    color: #3eb5ac;
    /*font-weight: bold;*/
  }
  #article a:hover { text-decoration: none; color: #28948c; }
  #article a:active { background-color: transparent; }
</style>


<script>
  $(function(){
    //フォーカス時にアニメーションエフェクトでサイズを広げる
    $('#search-icon').click(function(){
      $('#search-input').show();
      if($('#search-input').val() != ''){
        $('#remove-icon').show();
      }
      //animate the box
      $('#search-input').animate({width: '300px'},400);
      $('#search-icon').hide();
      $('#search-input').focus();
    });

    $('#remove-icon').click(function(){
      $('#search-input').val('');
    });

    //外れたときはサイズを縮める
    $('#search-input').blur(function(){
      $(this).animate({width: '40px'},400);
      $.when(
        $('#search-input').fadeOut(50)
      ).done(function() {
          $('#search-icon').fadeIn(50);
        });
      $('#remove-icon').hide();
    });

    $('#search-input').keyup(function() {
      if($('#search-input').val() === '') {
        $('#remove-icon').hide();
      } else {
        $('#remove-icon').show();
      }
    });
  });
</script>