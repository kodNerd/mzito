<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['add_service'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);

   $image_01 = $_FILES['image_01']['name'];
   $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   $image_02 = $_FILES['image_02']['name'];
   $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
   $image_size_02 = $_FILES['image_02']['size'];
   $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
   $image_folder_02 = '../uploaded_img/'.$image_02;

   $image_03 = $_FILES['image_03']['name'];
   $image_03 = filter_var($image_03, FILTER_SANITIZE_STRING);
   $image_size_03 = $_FILES['image_03']['size'];
   $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
   $image_folder_03 = '../uploaded_img/'.$image_03;

   $select_services = $conn->prepare("SELECT * FROM `services` WHERE name = ?");
   $select_services->execute([$name]);

   if($select_services->rowCount() > 0){
      $message[] = 'Service name already exists!';
   }else{

      $insert_services = $conn->prepare("INSERT INTO `services`(name, details, price, image_01, image_02, image_03) VALUES(?,?,?,?,?,?)");
      $insert_services->execute([$name, $details, $price, $image_01, $image_02, $image_03]);

      if($insert_services){
         if($image_size_01 > 2000000 OR $image_size_02 > 2000000 OR $image_size_03 > 2000000){
            $message[] = 'image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name_01, $image_folder_01);
            move_uploaded_file($image_tmp_name_02, $image_folder_02);
            move_uploaded_file($image_tmp_name_03, $image_folder_03);
            $message[] = 'new Service added!';
         }

      }

   }  

};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_service_image = $conn->prepare("SELECT * FROM `services` WHERE id = ?");
   $delete_service_image->execute([$delete_id]);
   $fetch_delete_image = $delete_service_image->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_img/'.$fetch_delete_image['image_01']);
   unlink('../uploaded_img/'.$fetch_delete_image['image_02']);
   unlink('../uploaded_img/'.$fetch_delete_image['image_03']);
   $delete_product = $conn->prepare("DELETE FROM `services` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:services.php');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Services</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">

   <h1 class="heading">Add Service</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>Service <span style="color:red;">*</span></span>
            <input type="text" class="box" required maxlength="100" placeholder="Enter service name" name="name">
         </div>
         <div class="inputBox">
            <span>Service Price <span style="color:red;">*</span></span>
            <input type="number" min="0" class="box" required max="9999999999" placeholder="Enter service price" onkeypress="if(this.value.length == 10) return false;" name="price">
         </div>
        <div class="inputBox">
            <span>Image 1 <span style="color:red;">*</span></span>
            <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
        <div class="inputBox">
            <span>Image 2 <span style="color:red;">*</span></span>
            <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
        <div class="inputBox">
            <span>Image 3 <span style="color:red;">*</span></span>
            <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
         <div class="inputBox">
            <span>Service description <span style="color:red;">*</span></span>
            <textarea name="details" placeholder="Enter service details" class="box" required maxlength="500" cols="30" rows="10"></textarea>
         </div>
      </div>
      
      <input type="submit" value="add Service" class="btn" name="add_service">
   </form>

</section>

<section class="show-products">

   <h1 class="heading">Services added</h1>

   <div class="box-container">

   <?php
      $select_services = $conn->prepare("SELECT * FROM `services`");
      $select_services->execute();
      if($select_services->rowCount() > 0){
         while($fetch_services = $select_services->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      <img src="../uploaded_img/<?= $fetch_services['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_services['name']; ?></div>
      <div class="price">Ksh.<span><?= $fetch_services['price']; ?></span>/-</div>
      <div class="details"><span><?= $fetch_services['details']; ?></span></div>
      <div class="flex-btn">
         <a href="update_service.php?update=<?= $fetch_services['id']; ?>" class="option-btn">update</a>
         <a href="services.php?delete=<?= $fetch_services['id']; ?>" class="delete-btn" onclick="return confirm('delete this service?');">delete</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no service added yet!</p>';
      }
   ?>
   
   </div>

</section>








<script src="../js/admin_script.js"></script>
   
</body>
</html>