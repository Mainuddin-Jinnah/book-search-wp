<?php
/*
Plugin Name: Library Book Search Plugin
Description: Customer can easily search book by name,autor,price,rating etc.
* Version: 1.0
Author: Mainuddin Jinnah
* Author URI: #
*/


register_activation_hook(__FILE__,'createbook');
register_deactivation_hook(__FILE__,'deletebook');


global $wpdb;
global $table_name;
$table_name = $wpdb->prefix . "book";
add_action('wp_ajax_changedata','changedata');


function createbook()
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    global $table_name;
    global $wpdb;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {
     $sql = "CREATE TABLE " . $table_name . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	bookname varchar(255) NOT NULL,
	bookdescription nvarchar(255) NOT NULL,
	author varchar(255) NOT NULL,
	publisher varchar(255) NOT NULL,
	rating int(25) NOT NULL,
	price int(200) NOT NULL,
	UNIQUE KEY id (id))";
       dbDelta($sql);
    }
}



function deletebook()
{
    global $table_name;
    global $wpdb;
    $table_name = $wpdb->prefix . "book";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}



add_action('admin_menu', 'book_menu' );
function book_menu()
{
    ?>
    <?php
    add_menu_page('My Book menu', 'Book Search', 'manage_options', 'my-book-slug','searchbook','dashicons-book');
    add_submenu_page( 'my-book-slug', 'Add Book Here', 'Add Book','manage_options', 'add_book_Parts','addbook');
	add_submenu_page( 'my-book-slug', 'Edit Book Here', '','manage_options', 'edit_book_Parts','editbook');
    add_submenu_page( 'my-book-slug', 'Shortcode', 'Use Shortcode','manage_options', 'add_book_shortcode','shortcodes');
}




//----------------------------------------SEARCH BOOK-------------------------------------//

function changedata()
{
    global $wpdb;
    $param0=$_REQUEST['operation'];
    if($param0=='search')
    {
		$bname=$_REQUEST['bname'];
        $aname=$_REQUEST['aname'];
		$pname=$_REQUEST['pname'];
		$rname=$_REQUEST['rname'];
		$lprice=$_REQUEST['lprice'];
		$mprice=$_REQUEST['mprice'];
		
  $search=$wpdb->get_results("select * from wp_book where bookname like '%$bname%' and author like '%$aname%' and publisher like '%$pname%' and rating like '%$rname%' and price between 
  '$lprice' and '$mprice'");
  ?>

  
<table  border=1 style="width:100%;">
<tr> 
<td>Id</td><td>Book Name</td><td>Price</td><td>Author</td><td>Publisher</td><td>Rating</td>
</tr>

  <?php
 foreach($search as $searchresult)
 {
	 $rating=$searchresult->rating;
?>	 
<tr> 
<td><?php echo $searchresult->id; ?></td>
<td><a href="?page=my-book-slug&sid=<?php echo $searchresult->id; ?>"><?php echo $searchresult->bookname; ?></a></td>
<td><?php echo $searchresult->price; ?></td>
<td><?php echo $searchresult->author; ?></td>
<td><?php echo $searchresult->publisher; ?></td>
<td>
<?php 
    for ($i = 1; $i <= 5; $i++)
	{
      if ($i <= $rating) 
	  {
	  ?>
      <i class="icon fa fa-star" style="font-size:15px;color:red"></i>
     <?php
      }
	  else 
	  {
     ?>
      <i class="fa fa-star-half-empty"></i>
     <?php
      }
	}	  
?>

</td>
</tr>
 
<?php	 
 }
 ?>
 </table>
 

 <?php

    }
  
  die();

}


function searchbook()
{
?>

<h1 style="text-align:center;text-decoration:underline;"> Book Search </h1>
<table style="width:100%" border=1>
<tr><td>Book Name</td><td><input type="text" id="bname"></td>
<td>Author</td><td><input type="text" id="aname"></td></tr>
<tr><td>Publisher</td><td><input type="text" id="pname"></td>
<td>Rating</td>
<td><select id="rname">
<option value="chooserating">---Select Your Rating-- </option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
</select></td>
</tr>

<tr><td> Price From</td><td><input type="text" id="lprice"></td>
<td>Price  To</td><td><input type="text" id="mprice"></td>
</tr>
<tr><td colspan=4 style="padding-left:40%;">
<a class="button" id="search" style="background-color: green; color: #fff;text-align:center;">Search Book</a>
</td></tr>
</table>
<div style="margin-bottom:4%;"> </div>
<div id="finalresult"></div>
<div style="margin-bottom:4%;"> </div>
<?php
  global $wpdb;
  $sid=$_GET['sid'];
  $search2=$wpdb->get_results("select * from wp_book where id=$sid");
  foreach($search2 as $searchresult2)
 {
	 
?>
<table  border="2" style="width:100%;">
<tr> 
<td>Book Name</td><td><?php echo $searchresult2->bookname; ?></td></tr>
<td>Price</td><td><?php echo $searchresult2->price; ?></td></tr>
<td>Author</td>
<td><a href="?page=my-book-slug&sid=<?php echo $searchresult2->id; ?>&author=<?php echo $searchresult2->author; ?>"><?php echo $searchresult2->author; ?></a></td></tr>
<td>Publisher</td><td><a href="?page=my-book-slug&sid=<?php echo $searchresult2->id; ?>&publisher=<?php echo $searchresult2->publisher; ?>"><?php echo $searchresult2->publisher; ?></a></td></tr>
<td>Rating</td><td><?php echo $searchresult2->rating; ?></td></tr>
<td>Book Details</td><td><?php echo $searchresult2->bookdescription; ?></td></tr>
</table>
<?php
 }
 ?>


<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
<script>
var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
$(document).ready(function (){
$('#search').click(function(){
	
    var bname= $("#bname").val();
	var aname= $("#aname").val();
	var pname= $("#pname").val();
	var rname= $("#rname").val();
	var lprice= $("#lprice").val();
	var mprice= $("#mprice").val();
	
	if (bname== "" || aname=="" || pname=="" || rname=="chooserating" || lprice=="" || mprice=="")
	{
		alert("Please fill up all fields");
	}
	
	else
	{
		       
		
		             $.ajax({
                            url: ajaxurl,
                            data: {
                                'action':'changedata',
                                'bname' : bname,
								'aname' : aname,
								'pname' : pname,
								'rname' : rname,
								'lprice' :lprice,
								'mprice' :mprice,
                                'operation':'search'
                            },
                            success:function(data) {
                           $("#finalresult").html(data);
                            },
                            error: function(errorThrown){

                                console.log(errorThrown);
                            }
                        });
		
	}
	
	
});
});


</script>



<?php 
}
 
 
 
//----------------------------------------ADD BOOK-------------------------------------//

function addbook()
{   
    global $wpdb;
    $deleteid=$_GET['did'];	
    $delete=$wpdb->query('delete  from wp_book where id='.$deleteid .'');
	if($delete)
	{
		echo ("<SCRIPT LANGUAGE='JavaScript'> window.location.href='admin.php?page=add_book_Parts';
       </SCRIPT>");
	}	
?>
<p style="text-align:left;text-decoration:underline;font-weight:300;font-size:24px;">Add Book</p>
<form action="" method="post">
<table  border=1 style="font-weight:600;">
<tr> 
<td>Book Name</td><td><input type="text" name="bname" required></td>
</tr>
<tr> 
<td>Short Description</td><td><textarea rows="3" cols="21" name="description" required></textarea></td>
</tr>
<tr>
<td>Author</td><td><input type="text" name="aname" required></td>
</tr>
<tr>
<td>Publisher</td><td><input type="text" name="pname" required></td>
</tr>
<tr>
<td>Rating</td><td><input type="text" name="rname" required></td>
</tr>
<tr>
<td>Price</td><td><input type="text" name="price" required></td>
</tr>
<tr>
<td>Action</td><td><input type="submit" value="Submit"></td> 
</tr>
</table>
</form>

<?php
 if((isset($_POST['bname'])) && (!empty($_POST['bname'])))
    {
	
	$bookname = $_POST['bname'];
	$bookdesp = $_POST['description'];
    $author = $_POST['aname'];
    $publisher = $_POST['pname'];
    $rating = $_POST['rname'];
    $price = $_POST['price'];	
	
	 $bookdata=array(
            'bookname'=> $bookname,
			'bookdescription'=> $bookdesp,
            'author'=>  $author,
            'publisher'=>$publisher,
            'rating'=>$rating,
            'price'=>$price
                        );
	 global $wpdb;
     $wpdb->insert( 'wp_book', $bookdata );	
	}
?>

       
         <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
         <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
		  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"> 
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css">
       <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>
		
		<script>
          
			   $(document).ready(function() {
              $('#example').DataTable();
                 } );
		</script>
		 <div class="wrap">
        <div class="col-md-12">
           <h1 style="text-align:center;text-decoration:underline;"> Book List </h1><br>
        </div>
        </div>
	    <div class="row">
        <div class="col-md-12">
   

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive" >
                       <table id="example" class="table table-striped table-bordered" style="width:100%">

                            <thead>
                            <tr>

                                <th>ID</th>
                                <th>Book Name</th>
                                <th>Book Description</th>
                                <th>Author</th>
                                <th>Publisher</th>
                                <th>Rating</th>
                                <th>Price</th>
                                <th>Options</th>
                            </tr>
                            </thead>	
						    <tbody>	
	
 	<?php
	global $wpdb;
	$result=$wpdb->get_results('select * from wp_book');
	foreach($result as $rs)
	{
	?>
<tr style="text-align:center;">
<td><?php echo $rs->id; ?></td>
<td><?php echo $rs->bookname; ?></td>
<td style="width:30%"><?php echo $rs->bookdescription; ?></td>
<td><?php echo $rs->author; ?></td>
<td><?php echo $rs->publisher; ?></td>
<td><?php echo $rs->rating; ?></td>
<td><?php echo $rs->price; ?></td>
<td>
<a class="button" style="background-color: green; color: #fff;" href="<?php echo admin_url(); ?>admin.php?page=edit_book_Parts&editid=<?php echo $rs->id; ?>">Edit Book</a>
<a class="button" style="background-color: red; color: #fff;" href="<?php echo admin_url(); ?>admin.php?page=add_book_Parts&did=<?php echo $rs->id; ?>">Delete Book</a>
</td>
</tr>
   <?php
	}
   ?> 
                   </tbody>
                </table>
             </div>
           </div>
        </div>
     </div>
</div> 



<?php
}



//----------------------------------------EDIT BOOK-------------------------------------//

function editbook()
{  

$editid=$_GET['editid'];
	global $wpdb;
	$editresultt=$wpdb->get_results('select * from wp_book where ID='.$editid.'');
	foreach($editresultt as $editresultt1)
	{
		$bookname=$editresultt1->bookname;
		$bookdesp=$editresultt1->bookdescription;
		$author=$editresultt1->author;
		$publisher=$editresultt1->publisher;
		$rating=$editresultt1->rating;
		$price=$editresultt1->price;
	}
?>
<?php 
if($editid!= "")
{
?>
<h1 style="text-align:center;text-decoration:underline;">Edit Book</h1>
<form action="" method="post">
<table style="width:100%" border=1>
<tr style="text-align:center;"> 
<td>Book Name</td><td>Short Description</td><td>Author</td><td>Publisher</td><td>Rating</td><td>Price</td><td>Action</td> </tr>

<tr>
<td><input type="text" name="bname" value="<?php echo $bookname;?>"></td>
<td><textarea rows="2" cols="40" name="description"><?php echo $bookdesp;?></textarea></td>
<td><input type="text" name="aname" value="<?php echo $author;?>"></td>
<td><input type="text" name="pname" value="<?php echo $publisher;?>"></td>
<td><input type="text" name="rname" value="<?php echo $rating;?>"></td>
<td><input type="text" name="price" value="<?php echo $price;?>"></td>
<td><input type="submit" value="Submit"></td>
<tr>

</table>
</form>
<?php
}
?>

<?php
 if (isset($_POST['bname']) && (!empty($_POST['bname'])))
{
	 $editid=$_GET['editid'];
	 $bookname=$_POST['bname'];
	 $bookdesp=$_POST['description'];
	 $author=$_POST['aname'];
	 $publisher=$_POST['pname'];
	 $rating=$_POST['rname'];
	 $price=$_POST['price'];
	 
	 global $wpdb;
	 $wpdb->update( 
    'wp_book', 
     array( 
	      'bookname'=> $bookname,
          'bookdescription'=>$bookdesp,
          'author'=> $author,
          'publisher'=> $publisher,
          'rating'=> $rating,
          'price'=> $price,
          ), 
    array( 'id' => $editid)
);
    echo "<script>location.href='admin.php?page=add_book_Parts'</script>";
}
?>

<?php
}



//-------------------------------------Use This Shortcode-----------------------------------//
function shortcodes()
{
?>
<h3 style="text-align:center;color:blue;">
For Searching your favourite Book on Website Frontend You can use this shortcode in any page or post . Please Copy The below Shortcode is <p style="color:#000;font-size:22px;"> [Book_Search] </p></h3>
<?php
}

add_shortcode('Book_Search','searchbook');
?>
