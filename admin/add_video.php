<?php  
include('header.php');

$sql = "SELECT * FROM categories";
$result = mysqli_query($conn, $sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $video_name = $_FILES['video_file']['name'];
    $file_temp = $_FILES['video_file']['tmp_name'];
    $file_path = "uploads/videos/" . ($video_name);

    $category_id = $_POST['category_id'];

    if (move_uploaded_file($file_temp, $file_path)) {
        
        $sql = "INSERT INTO videos (video_name, category_id) 
                VALUES ('$video_name', '$category_id')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Video Added Successfully'); window.location.href='view_video.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Error uploading video: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Add Video</h2>
    <form action="add_video.php" method="POST" enctype="multipart/form-data">

        <!-- Video File -->
        <div class="form-group">
            <label>Video File</label>
            <input type="file" class="form-control" id="video_file" name="video_file" required>
        </div>

        <!-- Category Dropdown -->
        <div class="form-group">
            <label for="category">Select Category</label>
            <select name="category_id" id="category" class="form-control" required>
                <option value="">Select a Category</option>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['category_id'] . "'>Artist: " . $row['category_artist'] . ", Album: " . $row['category_album'] . ", Genre: " . $row['category_genre'] . ", Language: " . $row['category_language'] . ", Year: " . $row['category_year'] . "</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary d-block w-100">Add Video</button>
    </form>
</div>

<?php
include 'footer.php';
?>
