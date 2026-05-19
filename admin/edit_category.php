<?php 
include('header.php');

if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];

    $sql = "SELECT * FROM categories WHERE category_id = '$category_id'";
    $result = mysqli_query($conn, $sql);
    $category = mysqli_fetch_assoc($result);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $category_artist = $_POST['artist'];
        $category_album = $_POST['album'];
        $category_genre = $_POST['genre'];
        $category_language = $_POST['language'];
        $category_year = $_POST['year'];
        $category_image = $category['category_image'];

        if (!empty($_FILES['category_image']['name'])) {
            $target_dir = "uploads/images/";
            $category_image = $_FILES["category_image"]["name"];
            $target_file = $target_dir . basename($category_image);

            if (move_uploaded_file($_FILES["category_image"]["tmp_name"], $target_file)) {
            } else {
                echo "<script>alert('Error uploading the image.');</script>";
            }
        }

        $update_sql = "UPDATE categories SET 
                        category_artist = '$category_artist',
                        category_album = '$category_album',
                        category_genre = '$category_genre',
                        category_language = '$category_language',
                        category_year = '$category_year',
                        category_image = '$category_image'
                        WHERE category_id = '$category_id'";

        if (mysqli_query($conn, $update_sql)) {
            echo "<script>alert('Category Updated!'); window.location.href='view_category.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

?>

<!-- Edit Category Form -->
<div class="container mt-5">
    <form action="edit_category.php?category_id=<?php echo $category_id; ?>" method="POST" enctype="multipart/form-data">
        <!-- Artist -->
        <div class="form-row">
            <div class="form-group col-md-12">
                <label>Artist</label>
                <input type="text" class="form-control" name="artist" value="<?php echo $category['category_artist']; ?>" required>
            </div>
        </div>

        <!-- Album and Genre -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Album</label>
                <input type="text" class="form-control" name="album" value="<?php echo $category['category_album']; ?>" required>
            </div>
        
            <div class="form-group col-md-6">
                <label>Genre</label>
                <input type="text" class="form-control" name="genre" value="<?php echo $category['category_genre']; ?>" required>
            </div>
        </div>

        <!-- Language and Year -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Language</label>
                <input type="text" class="form-control" name="language" value="<?php echo $category['category_language']; ?>" required>
            </div>

            <div class="form-group col-md-6">
                <label>Year</label>
                <input type="text" class="form-control" name="year" value="<?php echo $category['category_year']; ?>" required>
            </div>
        </div>

        <!-- Current Image and Image Upload -->
        <div class="form-row">
            <div class="form-group col-md-12">
                <label>Current Image</label><br>
                <img src="uploads/images/<?php echo $category['category_image']; ?>" alt="Current Category Image" width="200"><br><br>
                <label>Category Image</label>
                <input type="file" class="form-control" name="category_image">
            </div>
        </div>

        <button type="submit" class="btn btn-primary d-block w-100">Update Category</button>
    </form>
</div>

<?php
include 'footer.php';
?>
