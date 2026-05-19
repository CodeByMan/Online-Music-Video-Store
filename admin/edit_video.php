<?php  
include('header.php');

if (isset($_GET['video_id'])) {
    $video_id = $_GET['video_id'];

    $sql = "SELECT * FROM videos WHERE video_id = '$video_id'";
    $result = mysqli_query($conn, $sql);
    $video = mysqli_fetch_assoc($result);


    $category_sql = "SELECT * FROM categories";
    $category_result = mysqli_query($conn, $category_sql);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $video_name = $_FILES['video_file']['name'];
        $file_temp = $_FILES['video_file']['tmp_name'];
        $file_path = "uploads/videos/" . ($video_name);
        $category_id = $_POST['category_id'];

        if (!empty($video_name)) {
            if (move_uploaded_file($file_temp, $file_path)) {
                $update_sql = "UPDATE videos SET video_name = '$video_name', category_id = '$category_id' WHERE video_id = '$video_id'";

                if (mysqli_query($conn, $update_sql)) {
                    echo "<script>alert('Video Updated Successfully'); window.location.href='view_video.php';</script>";
                } else {
                    echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
                }
            } else {
                echo "<script>alert('Error uploading video');</script>";
            }
        }

        if (empty($video_name)) {
            $update_sql = "UPDATE videos SET category_id = '$category_id' WHERE video_id = '$video_id'";

            if (mysqli_query($conn, $update_sql)) {
                echo "<script>alert('Video Updated Successfully'); window.location.href='view_video.php';</script>";
            } else {
                echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
            }
        }
    }
}

?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Edit Video</h2>

    <form action="edit_video.php?video_id=<?php echo $video['video_id']; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Video File</label>
            <input type="file" class="form-control" name="video_file">
            <small class="form-text text-muted">Leave this blank if you don't want to change the video file.</small>
        </div>

        <div class="form-group">
            <label for="category">Select Category</label>
            <select name="category_id" id="category" class="form-control" required>
                <?php
                while ($category = mysqli_fetch_assoc($category_result)) {
                    $selected = ($category['category_id'] == $video['category_id']) ? 'selected' : '';
                    echo "<option value='" . $category['category_id'] . "' $selected>Artist: " . $category['category_artist'] . ", Album: " . $category['category_album'] . ", Genre: " . $category['category_genre'] . ", Language: " . $category['category_language'] . ", Year: " . $category['category_year'] . "</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary d-block w-100">Update Video</button>
    </form>
</div>

<?php
include 'footer.php';
?>
