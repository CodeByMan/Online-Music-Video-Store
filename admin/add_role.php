<?php
include("header.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role_type = $_POST['role_type'];

    $sql = "INSERT INTO `roles`(`role_type`) VALUES ('$role_type')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Role Added Successfully'); window.location.href='view_role.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<div class="container mt-5">
    <form action="add_role.php" method="POST">
        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group">
                    <label>Role Type:</label>
                    <input type="text" class="form-control" id="role_name" name="role_type" required>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary d-block w-100">Add Role</button>
    </form>
</div>

<?php
include 'footer.php';
?>
