<?php
include("database.php");

// Function to delete Role
if (isset($_GET['role_id'])) {
    $role_id = $_GET['role_id'];

    $delete_sql = "DELETE FROM roles WHERE role_id = '$role_id'";

    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Role Deleted Successfully!'); window.location.href='view_role.php';</script>";
    } else {
        echo "Error Deleting Role: " . mysqli_error($conn);
    }
}

// Function to delete user
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $delete_sql = "DELETE FROM users WHERE user_id = '$user_id'";

    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('User Deleted Successfully!'); window.location.href='view_user.php';</script>";
    } else {
        echo "Error Deleting User: " . mysqli_error($conn);
    }
}

// Function to delete Audio
if (isset($_GET['audio_id'])) {
    $audio_id = $_GET['audio_id'];

    $delete_sql = "DELETE FROM audio WHERE audio_id = '$audio_id'";

    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Audio Deleted Successfully!'); window.location.href='view_music.php';</script>";
    } else {
        echo "Error Deleting audio: " . mysqli_error($conn);
    }
}

// Function to delete Video
if (isset($_GET['video_id'])) {
    $video_id = $_GET['video_id'];

    $delete_sql = "DELETE FROM videos WHERE video_id = '$video_id'";

    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Video Deleted Successfully!'); window.location.href='view_video.php';</script>";
    } else {
        echo "Error Deleting video: " . mysqli_error($conn);
    }
}

// Function to delete Category
if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];

    $delete_sql = "DELETE FROM categories WHERE category_id = '$category_id'";

    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Category Deleted Successfully!'); window.location.href='view_category.php';</script>";
    } else {
        echo "Error Deleting Category: " . mysqli_error($conn);
    }
}


// Function to delete message
if (isset($_GET['message_id'])) {
    $message_id = $_GET['message_id'];

    // SQL query to delete the message
    $delete_sql = "DELETE FROM messages WHERE message_id = '$message_id'";

    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Message Deleted Successfully!'); window.location.href='message.php';</script>";
    } else {
        echo "Error deleting message: " . mysqli_error($conn);
    }
}

?>
