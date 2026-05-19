<?php 
include('header.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_artist = $_POST['artist'];
    $category_album = $_POST['album'];
    $category_genre = $_POST['genre'];
    $category_language = $_POST['language'];
    $category_year = $_POST['year'];
    $category_image = $_FILES["category_image"]["name"];
    $target_file = "uploads/images/" . $category_image;

    if (move_uploaded_file($_FILES["category_image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO `categories`(`category_artist`, `category_album`, `category_genre`, `category_language`, `category_year`, `category_image`)
                VALUES ('$category_artist','$category_album','$category_genre','$category_language','$category_year', '$category_image')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Category Added Successfully'); window.location.href='view_category.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Error uploading the image.');</script>";
    }
}
?>

<div class="container mt-5">
    <form action="add_category.php" method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group col-md-12">
                <label>Artist</label>
                <input type="text" class="form-control" name="artist" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Album</label>
                <input type="text" class="form-control" name="album" required>
            </div>
        
            <div class="form-group col-md-6">
                <label>Genre</label>
                <input type="text" class="form-control" name="genre" required>
            </div>
        </div>
            
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Language</label>
                <input type="text" class="form-control" name="language" required>
            </div>

            <div class="form-group col-md-6">
                <label>Year</label>
                <input type="text" class="form-control" name="year" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
                <label>Category Image</label>
                <input type="file" class="form-control" name="category_image" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary d-block w-100">Add Category</button>
    </form>
</div>

<?php
include 'footer.php';
?>
