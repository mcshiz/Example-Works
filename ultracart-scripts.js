
$(document).ajaxStop(function() {
  $('#loading').hide();
});

$(document).ready(function(){
// if ultracart returns $0.00 for an items price it is replaced by 'CALL' and the add to cart
// button is removed... most likely means that product wasnt added to ultracart and therefore cant checkout with it.
  $.each($('.uc-item-cost-value'), function(){
    var price = $(this).text();
    var newPrice = $(this);
    if (!price.match(/\d+/) || price.match (/\$0.00/)) {
      newPrice.html('CALL').css('text-align', 'center');
      newPrice.siblings('td').remove();
    };
  });
// this is a small helper function responsible for closing the modal
  $('.b-close').on('click', function(e){
    e.preventDefault();
    $('#add-to-cart-success').bPopup().close();
  });
});
// the following functions are responsible for populating the modals that popup after an item has been added to the cart.
// an ajax call is made to get_cart_info in the ultracart/inc folder
// get_cart_info is responsible for grabbing the items info from mySql and returning it as json object.
// itemInfo[0] = item link
// itemInfo[1] = item title
// itemInfo[2] = item price
// itemInfo[3] = item image link
// itemInfo[4] = 1 or 0 ( call or allow in cart)
// itemInfo[5] = 1 or 0 ( 1 for item is marked quote)
// Prevent the form from submitting via the browser.
  $(function() {
  $('form.ultracart-form').submit(function(event) {
    event.preventDefault();
    $('#loading').show();
    $('#add-to-cart-success').bPopup({
      onOpen: function() {
        $(this).css({
          'left': '0px',
          'position': 'absolute',
          'top': '273.5px',
          'z-index': '9999',
          'opacity': '1',
          'width': '40%',
          'height': 'auto',
          'background-color': 'white',
          'padding': '30px',
          'margin': 'auto',
          'border-radius': '4px',
          'display': 'block',
        });
      },
      onClose: function(){
        $('.added-items').empty();
        $('.item-pic').attr('src', "/images/coming_soon_product.gif");
      },
    });



   
    var form = $(this);
    var data = form.serialize();
    $.ajax({
      headers: {'X-UC-Merchant-Id': '*****',
      "Content-Type": 'text/javascript; charset=UTF-8'}, 
      dataType: "script",
      crossDomain: true,
      type: form.attr('method'),
      url: form.attr('action')+"?"+data,
    }).done(function() {

      var qty = data.split("quantity="),
        itemNum = data.split("&"),
        itemNum = itemNum[1].split('add=');
        countRaw = $('.uc-cart-item-count-label').text(),
        count = countRaw.split(" ");
        var a = isNaN(count[0]);
        if (a === true) {
          var number = 0;
        }else{
          var number = parseInt(count[0]);
        };
         var totalItems = number + parseInt(qty[1]);
        $.ajax({
          url: '/ultracart/inc/get_cart_info.php',
          type: 'POST',
          data: {'itemNum': itemNum[1]},
        })
        .done(function(data2) {
          var itemInfo = jQuery.parseJSON(data2);
          

              var offScreenImg = new Image();
              offScreenImg.src = itemInfo[3];
              offScreenImg.onload = function() {
                var imageHeight = offScreenImg.height;
                if (imageHeight < 160) {
                  $('.item-pic').attr('height', '');
                }
              };
              $('.item-link').attr('href', itemInfo[0]);
              $('.item-name').text(itemInfo[1]);
              $('.item-price').text(itemInfo[2]);
              if (itemInfo[3] != "" ) {
                $('.item-pic').attr('height', '160px');
                $('.item-pic').attr('src', itemInfo[3]);
              };

              if (itemInfo[4] == 1){
                $('.added-items').html("Sorry, This Item Is Currently Unavailable<br /><span class='Redsmal'>Please Call Us at ***-***-**** for more information</span>");
                $('.total-items').text("Total Items In Cart: "+number)
              } else if (itemInfo[5] == 1) {
                $('.added-items').html("This item requires communication from a Sales Technician prior to sale.<br />You may proceed to a quote and we will contact you before processing order.</span>");
                $('.total-items').text("Total Items In Cart: "+totalItems);
                $('.uc-cart-item-count-label').text(number + parseInt(qty[1]) + " Items");                  
              } else {
                $('.uc-cart-item-count-label').text(number + parseInt(qty[1]) + " Items");
                $('.added-items').text("We Just Added "+ parseInt(qty[1])+" Items To Your Cart");
                $('.total-items').text("Total Items In Cart: "+totalItems)
              };

        })
        .fail(function() {
          console.log("error in DB connect");
          location.reload();
        });  
    }).fail(function() {
      console.log("Something went wrong. Sorry.");
      location.reload();
      // Optionally alert the user of an error here...
    });
  });
});  






