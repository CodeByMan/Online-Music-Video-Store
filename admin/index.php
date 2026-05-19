    <?php
    include 'header.php';
    ?>

<h3 class="text-gray-600 text-center">Hi, <?php echo $user_name; ?></h3>

<div class="container mt-5">
    <h5 class="mb-4">Data Summary</h5>
    <?php

    $sql = "
        SELECT 
            (SELECT COUNT(*) FROM roles) AS total_roles,
            (SELECT COUNT(*) FROM users) AS total_users,
            (SELECT COUNT(*) FROM audio) AS total_music,
            (SELECT COUNT(*) FROM videos) AS total_videos,
            (SELECT COUNT(*) FROM messages WHERE message_status = 'pending') AS total_pending_messages
    ";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $total_roles = $row['total_roles'];
            $total_users = $row['total_users'];
            $total_music = $row['total_music'];
            $total_videos = $row['total_videos'];
            $total_messages = $row['total_pending_messages'];
        }
    } else {
        echo "0 results";
    }
    ?> 
    <table class="table table-bordered">
        <thead class="bg-primary text-white">
            <tr>
                <th>Category</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
        <tr>
                <td>Total Roles</td>
                <td><?php echo $total_roles; ?></td>
            </tr>
            <tr>
                <td>Total Users</td>
                <td><?php echo $total_users; ?></td>
            </tr>
            <tr>
                <td>Total Music</td>
                <td><?php echo $total_music; ?></td>
            </tr>
            <tr>
                <td>Total Videos</td>
                <td><?php echo $total_videos; ?></td>
            </tr>
            <tr>
                <td>Pending Messages</td>
                <td><?php echo $total_messages; ?></td>
            </tr>
        </tbody>
    </table>
</div>


    <?php
    include 'footer.php';
    ?>