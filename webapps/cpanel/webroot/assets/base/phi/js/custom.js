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

  // to top
  var topBtn = $('#page-top');
  topBtn.hide();
  // スクロールが100に達したらボタン表示
  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      topBtn.fadeIn();
    } else {
      topBtn.fadeOut();
    }
  });
  // スクロールしてトップ
  topBtn.click(function () {
    $('body,html').animate({
      scrollTop: 0
    }, 500);

    return false;
  });
});