<?php 
include('header.php');

if (isset($_GET['role_id'])) {
    $role_id = $_GET['role_id'];

    $sql = "SELECT * FROM roles WHERE role_id = '$role_id'";
    $result = mysqli_query($conn, $sql);
    $role = mysqli_fetch_assoc($result);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $role_type = $_POST['role_type'];
        
        $update_sql = "UPDATE roles SET 
                        role_type = '$role_type'
                        WHERE role_id = '$role_id'";

        if (mysqli_query($conn, $update_sql)) {
            echo "<script>alert('Role Updated Successfully!'); window.location.href='view_role.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!-- Edit Role Form -->
<div class="container-fluid pt-4 px-4">  
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">
                <form class="row" action="#" method="post">
                    <div class="col-md-12">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="role_type" class="form-control" value="<?php echo $role['role_type']; ?>" required>
                    </div>

                    <div class="col-md-12 mt-3">
                        <button class="btn btn-primary d-block w-100" type="submit">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>
