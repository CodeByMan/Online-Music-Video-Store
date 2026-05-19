<?php 
include('header.php');

$sql = "SELECT * FROM categories
        ORDER BY category_id DESC";
        
$result = mysqli_query($conn, $sql);

?>
    <div class="container mt-5">
        <table class="table table-bordered">
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Artist</th>
                    <th>Album</th>
                    <th>Genre</th>
                    <th>language</th>
                    <th>Year</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>{$row['category_id']}</td>
                                <td>{$row['category_artist']}</td>
                                <td>{$row['category_album']}</td>
                                <td>{$row['category_genre']}</td>
                                <td>{$row['category_language']}</td>
                                <td>{$row['category_year']}</td>
                                <td>
                                    <a href='edit_category.php?category_id={$row['category_id']}' class='btn btn-primary'>Edit</a>
                                    <a href='delete.php?category_id={$row['category_id']}' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this category?\")'>Delete</a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='2' class='text-center'>No categories found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

<?php
include 'footer.php';
?>

