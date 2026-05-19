<?php 
include('header.php');

$sql = "SELECT * FROM categories";
$result = mysqli_query($conn, $sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $audio_name = $_FILES['music_file']['name'];
    $file_temp = $_FILES['music_file']['tmp_name'];
    $file_path = "uploads/music/" . ($audio_name);
    $category_id = $_POST['category_id'];

    if (move_uploaded_file($file_temp, $file_path)) {
        $sql = "INSERT INTO audio (audio_name, category_id) 
                VALUES ('$audio_name', '$category_id')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Music Added Successfully'); window.location.href='view_music.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Audio Adding Error: " . mysqli_error($conn) . "');</script>";
    }
}

?>

<div class="container mt-5">
    <form action="add_music.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Music File</label>
            <input type="file" class="form-control" id="music_file" name="music_file" required>
        </div>

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

        <button type="submit" class="btn btn-primary d-block w-100">Add Music</button>
    </form>
</div>

<?php
include 'footer.php';
?>
