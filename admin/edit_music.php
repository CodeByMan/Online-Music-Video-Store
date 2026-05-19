<?php  
include('header.php');

if (isset($_GET['audio_id'])) {
    $audio_id = $_GET['audio_id'];

    $sql = "SELECT * FROM audio WHERE audio_id = '$audio_id'";
    $result = mysqli_query($conn, $sql);
    $audio = mysqli_fetch_assoc($result);
    
    $sql_categories = "SELECT * FROM categories";
    $result_categories = mysqli_query($conn, $sql_categories);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $audio_name = $_FILES['music_file']['name'];
        $file_temp = $_FILES['music_file']['tmp_name'];
        $file_path = "uploads/music/" . ($audio_name);

        $category_id = $_POST['category_id'];

        if ($audio_name != "") {
            move_uploaded_file($file_temp, $file_path);
        } else {
            $audio_name = $audio['audio_name'];
        }

        $update_sql = "UPDATE audio SET 
                        audio_name = '$audio_name', 
                        category_id = '$category_id'
                        WHERE audio_id = '$audio_id'";

        if (mysqli_query($conn, $update_sql)) {
            echo "<script>alert('Music Updated Successfully'); window.location.href='view_music.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    }
}

?>

<div class="container mt-5">
    <form action="edit_music.php?audio_id=<?php echo $audio_id; ?>" method="POST" enctype="multipart/form-data">

        <!-- Music File -->
        <div class="form-group">
            <label>Music File</label>
            <input type="file" class="form-control" id="music_file" name="music_file">
            <small>Leave this blank if you do not want to change the music file.</small>
        </div>

        <!-- Category Dropdown -->
        <div class="form-group">
            <label for="category">Select Category</label>
            <select name="category_id" id="category" class="form-control" required>
                <?php
                while ($category = mysqli_fetch_assoc($result_categories)) {
                    $selected = $audio['category_id'] == $category['category_id'] ? 'selected' : '';
                    echo "<option value='" . $category['category_id'] . "' $selected>
                            Artist: " . $category['category_artist'] . ", 
                            Album: " . $category['category_album'] . ", 
                            Genre: " . $category['category_genre'] . ", 
                            Language: " . $category['category_language'] . ", 
                            Year: " . $category['category_year'] . "
                          </option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary d-block w-100">Update Music</button>
    </form>
</div>

<?php
include 'footer.php';
?>
