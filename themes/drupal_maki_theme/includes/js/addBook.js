jQuery(document).ready(function(e) {
  var a = 5;
  jQuery('#addBook').on('click',function(event){
    var title=jQuery('#bookTitle').val().trim();
    var price=jQuery('#bookPrice').val();
    var isbn=jQuery('#bookISBN').val().trim();
    var comment=jQuery('#bookComment').val().trim();
    console.log(title);
    jQuery.post('/books/add',{'title':title,'price':price,'isbn':isbn,'comment':comment},function(data) {
      if(data.status == "success") {
        alert("Book added");
      }
      });
    event.preventDefault();
    return false;
    });
});
